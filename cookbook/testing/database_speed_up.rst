How to speed up your tests using database
===========================================================

When testing code that must fetch information from the database, it's a good practise to reset modifications after each tests.
But this can be a very heavy and long process, and waiting so long for tests to execute is always a pain.
This chapter will explain some methods to speed up your tests when using the database.

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



