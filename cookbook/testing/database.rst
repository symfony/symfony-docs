.. index::
   single: Tests; Database

How to test code which interacts with the database
==================================================

If your code interacts with the database, e.g. reads data from or stores data into
it, you need to adjust your tests to take this into account. There are many ways
how to deal with this. In a unit test, you can create a mock for a ``Repository``
and use it to return expected objects. In a functional test, you may need to
prepare a test database with predefined values, so a test always has the same data
to work with.

Mocking the ``Repository`` in a Unit Test
-----------------------------------------

If you want to test code which depends on a doctrine ``Repository`` in isolation, you
need to mock the ``Repository``. Normally you get the ``Repository`` from the ``EntityManager``,
so you would need to mock those as well. Suppose the class you want to test looks like this::

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
            $employee = $userRepository->find($id);
            
            return $employee->getSalary() + $employee->getBonus();
        }
    }

As the ``ObjectManager`` gets injected into the class through the constructor, it's 
easy to pass a mock object within a test::

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
            $this->assertEquals(1100, $salaryCalculator->calculateTotalSalary(1));
        }
    }
    
We are building our mocks from the inside out, first creating the employee which 
gets returned by the ``Repository`` which itself gets returned by the ``EntityManager``.
This way, no real class is involved in testing.
    
Changing database settings for functional tests
-----------------------------------------------

If you have functional tests, you want them to interact with a real database.
Most of the time you want to use a dedicated database connection to make sure
not to overwrite data you entered when developing the application and also
to be able to clear the database before every test.

To do this, you can specify a database configuration which overwrites the default
configuration:

.. code-block:: yaml

    # app/config/config_test.yml
    doctrine:
        # ...
        dbal:
            host: localhost
            dbname: testdb
            user: testdb
            password: testdb
            
.. code-block:: xml

    <!-- app/config/config_test.xml -->
    <doctrine:config>
        <doctrine:dbal
            host="localhost"
            dbname="testdb"
            user="testdb"
            password="testdb"
        >
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