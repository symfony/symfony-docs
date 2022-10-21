.. index::
   single: Tests; Database

How to Test A Doctrine Repository
=================================

.. seealso::

    The :ref:`main Testing guide <testing-databases>` describes how to use
    and set-up a database for your automated tests. The contents of this
    article show ways to test your Doctrine repositories.

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
