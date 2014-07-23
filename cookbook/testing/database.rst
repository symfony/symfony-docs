.. index::
   single: Tests; Database

How to test code that interacts with the Database
=================================================

If your code interacts with the database, e.g. reads data from or stores data
into it, you need to adjust your tests to take this into account. There are
many ways how to deal with this. In a unit test, you can create a mock for
a ``Repository`` and use it to return expected objects. In a functional test,
you may need to prepare a test database with predefined values to ensure that
your test always has the same data to work with.

.. note::

    If you want to test your queries directly, see :doc:`/cookbook/testing/doctrine`.

Mocking the ``Repository`` in a Unit Test
-----------------------------------------

If you want to test code which depends on a Doctrine repository in isolation,
you need to mock the ``Repository``. Normally you inject the ``EntityManager``
into your class and use it to get the repository. This makes things a little
more difficult as you need to mock both the ``EntityManager`` and your repository
class.

.. tip::

    It is possible (and a good idea) to inject your repository directly by
    registering your repository as a :doc:`factory service </components/dependency_injection/factories>`.
    This is a little bit more work to setup, but makes testing easier as you
    only need to mock the repository.

Suppose the class you want to test looks like this::

    namespace Acme\DemoBundle\Salary;

    use Doctrine\Common\Persistence\ObjectManager;

    class SalaryCalculator
    {
        private $entityManager;

        public function __construct(ObjectManager $entityManager)
        {
            $this->entityManager = $entityManager;
        }

        public function calculateTotalSalary($id)
        {
            $employeeRepository = $this->entityManager->getRepository('AcmeDemoBundle::Employee');
            $employee = $employeeRepository->find($id);

            return $employee->getSalary() + $employee->getBonus();
        }
    }

Since the ``ObjectManager`` gets injected into the class through the constructor,
it's easy to pass a mock object within a test::

    use Acme\DemoBundle\Salary\SalaryCalculator;

    class SalaryCalculatorTest extends \PHPUnit_Framework_TestCase
    {
        public function testCalculateTotalSalary()
        {
            // First, mock the object to be used in the test
            $employee = $this->getMock('\Acme\DemoBundle\Entity\Employee');
            $employee->expects($this->once())
                ->method('getSalary')
                ->will($this->returnValue(1000));
            $employee->expects($this->once())
                ->method('getBonus')
                ->will($this->returnValue(1100));

            // Now, mock the repository so it returns the mock of the employee
            $employeeRepository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
                ->disableOriginalConstructor()
                ->getMock();
            $employeeRepository->expects($this->once())
                ->method('find')
                ->will($this->returnValue($employee));

            // Last, mock the EntityManager to return the mock of the repository
            $entityManager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()
                ->getMock();
            $entityManager->expects($this->once())
                ->method('getRepository')
                ->will($this->returnValue($employeeRepository));

            $salaryCalculator = new SalaryCalculator($entityManager);
            $this->assertEquals(2100, $salaryCalculator->calculateTotalSalary(1));
        }
    }

In this example, you are building the mocks from the inside out, first creating
the employee which gets returned by the ``Repository``, which itself gets
returned by the ``EntityManager``. This way, no real class is involved in
testing.

Changing database Settings for functional Tests
-----------------------------------------------

If you have functional tests, you want them to interact with a real database.
Most of the time you want to use a dedicated database connection to make sure
not to overwrite data you entered when developing the application and also
to be able to clear the database before every test.

To do this, you can specify a database configuration which overwrites the default
configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_test.yml
        doctrine:
            # ...
            dbal:
                host:     localhost
                dbname:   testdb
                user:     testdb
                password: testdb

    .. code-block:: xml

        <!-- app/config/config_test.xml -->
        <doctrine:config>
            <doctrine:dbal
                host="localhost"
                dbname="testdb"
                user="testdb"
                password="testdb"
            />
        </doctrine:config>

    .. code-block:: php

        // app/config/config_test.php
        $configuration->loadFromExtension('doctrine', array(
            'dbal' => array(
                'host'     => 'localhost',
                'dbname'   => 'testdb',
                'user'     => 'testdb',
                'password' => 'testdb',
            ),
        ));

Make sure that your database runs on localhost and has the defined database and
user credentials set up.

Set up Database Insulation for your Tests
-----------------------------------------

In your functional tests, you are maybe going to make changes to your database and then request some pages to see that the result is well displayed. You may also want to test form submit and see the new entity created. 
With the following code, you will be able to rollback all changes done to the database during the execution of one test, even in subrequests done by the test client.

To do so, you have to share the same database connection object in the test case and the test client. You first have to override the default test client class::
   
   namespace Acme\DemoBundle\Tests;

   use Symfony\Bundle\FrameworkBundle\Client as BaseClient;
   use Doctrine\DBAL\Connection;
   
   /**
    * Extends the default Client class to keep the same connection
    */
   class Client extends BaseClient
   {
       /**
        * @var Connection the database connection
        *
        * This connection must be the same as used in the test
        * to be able to rollback all changes to the database
        * done during the test and its requests
        */
       protected $connection;
   
       /**
        * @var boolean was there a request to shutdown ?
        */
       protected $requested;
   
       /**
        * Makes a request.
        *
        * @param object $request An origin request instance
        *
        * @throws \Exception if connection not set
        *
        * @return object An origin response instance
        */
       protected function doRequest($request)
       {
           if ($this->requested) {
               // If there was a previous request
               // Shutdown and then reboot the kernel
               $this->kernel->shutdown();
               $this->kernel->boot();
           }
   
           // Memorize that we need to shutdown and reboot
           $this->requested = true;
   
           // Set the defined connection
           if($this->connection == null) { 
               throw new \Exception('Please set the connection of the client object'); 
           }
           $this->getContainer()->set('doctrine.dbal.default_connection', $this->connection);
   
           // Handle request
           return $this->kernel->handle($request);
       }
   
       /**
        * Returns the database connection
        * @return Connection
        */
       public function getConnection()
       {
           return $this->connection;
       }
   
       /**
        * Set the database connection
        * @param Connection $connection
        */
       public function setConnection(Connection $connection)
       {
           $this->connection = $connection;
       }
   }


And then change your service.yml file::

  # src/Acme/DemoBundle/Resources/config/services.yml
  parameters:
      # Override the default test client class with our own
      # in order to ensure database isolation during tests
      test.client.class: Acme\DemoBundle\Tests\Client


There is still one thing to do, as we now share the same connection object for the whole test, but we don't begin a transaction at the beginning of each test, and rollback at the end.
You can create your custom parent test class, extending WebTestCase like this ::

   # src/Acme/DemoBundle/Tests/IsolatedTestCase.php
   
   namespace Acme\DemoBundle\Tests;
   
   use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
   
   /**
    * Parent class for tests
    * Automatically rollback any changes done to the database
    */
   abstract class IsolatedTestCase extends WebTestCase
   {
       protected $client;

       /**
        * Creates a Client
        *
        * @param array $options            An array of options to pass to the createKernel class
        * @param array $server             An array of server parameters
        *
        * @return Client                   A Client instance
        */
       protected static function createClient(array $options = array(), array $server = array())
       {
           // Create the client using parent function
           $client = parent::createClient($options, $server);
   
           // Set the database connection of the test client with the same used in the test
           $client->setConnection($client->getContainer()->get('doctrine')->getConnection());
   
           return $client;
       }
       
       /**
        * Called before every tests
        * - Initializes a new client and entity manager
        * - Starts a new transaction
        */
       public function setUp()
       {
           $this->client = static::createClient();
           $this->em = $this->client->getContainer()->get('doctrine')->getManager();
           $this->em->beginTransaction();
       }
   
       /**
        * Called after every tests
        * - Rollback the transaction
        * - Closes the entity manager
        */
       public function tearDown()
       {
           $this->em->rollback();
           $this->em->close();
       }
   }
   

Then, you just have to extend this class and your tests will automatically be isolated from a database point of view
Here is an example of a possible test ::

   # src/Acme/DemoBundle/Tests/Example.php
   
   namespace Acme\DemoBundle\Tests;
   
   use Acme\DemoBundle\Tests\IsolatedTestCase;
   
   class Example extends IsolatedTestCase
   {
       public function testExample()
       {
           // ... All changes done here will be automatically cancelled
       }
   }


   
