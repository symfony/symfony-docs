.. index::
   single: Tests; Database

How to Test Code that Interacts with the Database
=================================================

Configuring a Database for Tests
--------------------------------

Tests that interact with the database should use their own separate database to
not mess with the databases used in the other :ref:`configuration environments <configuration-environments>`.
To do that, edit or create the ``.env.test.local`` file at the root directory of
your project and define the new value for the ``DATABASE_URL`` env var:

.. code-block:: bash

    # .env.test.local
    DATABASE_URL=mysql://USERNAME:PASSWORD@127.0.0.1/DB_NAME

.. tip::

    A common practice is to append the ``_test`` suffix to the original database
    names in tests. If the database name in production is called ``project_acme``
    the name of the testing database could be ``project_acme_test``.

The above assumes that each developer/machine uses a different database for the
tests. If the entire team uses the same settings for tests, edit or create the
``.env.test`` file instead and commit it to the shared repository. Learn more
about :ref:`using multiple .env files in Symfony applications <configuration-multiple-env-files>`.

Resetting the Database Automatically Before each Test
-----------------------------------------------------

Tests should be independent from each other to avoid side effects. For example,
if some test modifies the database (by adding or removing an entity) it could
change the results of other tests. Run the following command to install a bundle
that ensures that each test is run with the same unmodified database:

.. code-block:: terminal

    $ composer require --dev dama/doctrine-test-bundle

Now, enable it as a PHPUnit extension or listener:

.. code-block:: xml

    <!-- phpunit.xml.dist -->
    <phpunit>
        <!-- ... -->

        <!-- Add this for PHPUnit 7.5 or higher -->
        <extensions>
            <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
        </extensions>

        <!-- Add this for PHPUnit 7.0 until 7.4 -->
        <listeners>
            <listener class="\DAMA\DoctrineTestBundle\PHPUnit\PHPUnitListener"/>
        </listeners>
    </phpunit>

This bundle uses a clever trick to avoid side effects without sacrificing
performance: it begins a database transaction before every test and rolls it
back automatically after the test finishes to undo all changes. Read more in the
documentation of the `DAMADoctrineTestBundle`_.

.. _doctrine-fixtures:

Dummy Data Fixtures
-------------------

Instead of using the real data from the production database, it's common to use
fake or dummy data in the test database. This is usually called *"fixtures data"*
and Doctrine provides a library to create and load them. Install it with:

.. code-block:: terminal

    $ composer require --dev doctrine/doctrine-fixtures-bundle

Then, use the ``make:fixtures`` command to generate an empty fixture class:

.. code-block:: terminal

    $ php bin/console make:fixtures

    The class name of the fixtures to create (e.g. AppFixtures):
    > ProductFixture

Customize the new class to load ``Product`` objects into Doctrine::

    // src/DataFixtures/ProductFixture.php
    namespace App\DataFixtures;

    use App\Entity\Product;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Persistence\ObjectManager;

    class ProductFixture extends Fixture
    {
        public function load(ObjectManager $manager)
        {
            $product = new Product();
            $product->setName('Priceless widget');
            $product->setPrice(14.50);
            $product->setDescription('Ok, I guess it *does* have a price');
            $manager->persist($product);

            // add more products

            $manager->flush();
        }
    }

Empty the database and reload *all* the fixture classes with:

.. code-block:: terminal

    $ php bin/console doctrine:fixtures:load

For more information, read the `DoctrineFixturesBundle documentation`_.

Mocking a Doctrine Repository in Unit Tests
-------------------------------------------

**Unit testing Doctrine repositories is not recommended**. Repositories are
meant to be tested against a real database connection. However, in case you
still need to do this, look at the following example.

Suppose the class you want to test looks like this::

    // src/Salary/SalaryCalculator.php
    namespace App\Salary;

    use App\Entity\Employee;
    use Doctrine\Persistence\ObjectManager;

    class SalaryCalculator
    {
        private $objectManager;

        public function __construct(ObjectManager $objectManager)
        {
            $this->objectManager = $objectManager;
        }

        public function calculateTotalSalary($id)
        {
            $employeeRepository = $this->objectManager
                ->getRepository(Employee::class);
            $employee = $employeeRepository->find($id);

            return $employee->getSalary() + $employee->getBonus();
        }
    }

Since the ``EntityManagerInterface`` gets injected into the class through the
constructor, you can pass a mock object within a test::

    // tests/Salary/SalaryCalculatorTest.php
    namespace App\Tests\Salary;

    use App\Entity\Employee;
    use App\Salary\SalaryCalculator;
    use Doctrine\Persistence\ObjectManager;
    use Doctrine\Persistence\ObjectRepository;
    use PHPUnit\Framework\TestCase;

    class SalaryCalculatorTest extends TestCase
    {
        public function testCalculateTotalSalary()
        {
            $employee = new Employee();
            $employee->setSalary(1000);
            $employee->setBonus(1100);

            // Now, mock the repository so it returns the mock of the employee
            $employeeRepository = $this->createMock(ObjectRepository::class);
            // use getMock() on PHPUnit 5.3 or below
            // $employeeRepository = $this->getMock(ObjectRepository::class);
            $employeeRepository->expects($this->any())
                ->method('find')
                ->willReturn($employee);

            // Last, mock the EntityManager to return the mock of the repository
            // (this is not needed if the class being tested injects the
            // repository it uses instead of the entire object manager)
            $objectManager = $this->createMock(ObjectManager::class);
            // use getMock() on PHPUnit 5.3 or below
            // $objectManager = $this->getMock(ObjectManager::class);
            $objectManager->expects($this->any())
                ->method('getRepository')
                ->willReturn($employeeRepository);

            $salaryCalculator = new SalaryCalculator($objectManager);
            $this->assertEquals(2100, $salaryCalculator->calculateTotalSalary(1));
        }
    }

In this example, you are building the mocks from the inside out, first creating
the employee which gets returned by the ``Repository``, which itself gets
returned by the ``EntityManager``. This way, no real class is involved in
testing.

Functional Testing of A Doctrine Repository
-------------------------------------------

In :ref:`functional tests <functional-tests>` you'll make queries to the
database using the actual Doctrine repositories, instead of mocking them. To do
so, get the entity manager via the service container as follows::

    // tests/Repository/ProductRepositoryTest.php
    namespace App\Tests\Repository;

    use App\Entity\Product;
    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class ProductRepositoryTest extends KernelTestCase
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        private $entityManager;

        protected function setUp(): void
        {
            $kernel = self::bootKernel();

            $this->entityManager = $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        }

        public function testSearchByName()
        {
            $product = $this->entityManager
                ->getRepository(Product::class)
                ->findOneBy(['name' => 'Priceless widget'])
            ;

            $this->assertSame(14.50, $product->getPrice());
        }

        protected function tearDown(): void
        {
            parent::tearDown();

            // doing this is recommended to avoid memory leaks
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

.. _`DAMADoctrineTestBundle`: https://github.com/dmaicher/doctrine-test-bundle
.. _`DoctrineFixturesBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
