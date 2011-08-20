.. index::
   single: Tests; Doctrine

How to unit test Doctrine repositories
======================================

Testing Doctrine repositories in a Symfony project is not a straightforward
task. Indeed, to load a repository you need to load your entities, an entity 
manager, and some other stuff like a connection.

As Symfony and Doctrine share the same testing framework, it's quite easy to 
implement unit tests in your Symfony project. The ORM comes with its own set
of tools to ease the unit testing and mock on the fly everything you need,
like a connection, an entity manager, etc. So, we will simply use the testing
components brought by Doctrine. All you need is to setup the autoloading and
the annotation driver, and extends your testing class by the one provided by 
Doctrine.

Anyway, you have to keep in mind than if you want to test your models against
a database, it's no more a unit test, but a functional test as you need a 
database.

First at all, you need to add the Doctrine\Tests namespace to the autoloader:

    // app/autoload.php
    // Add Doctrine\\Tests
    'Doctrine\\DBAL'                 => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine\\Tests'                => __DIR__.'/../vendor/doctrine/tests',


Then, you will have to setup an entity manager in each test, so that Doctrine
will be able to load your entities and repositories for you.

As Doctrine is not able by default to load the annotation the way it's 
recommanded in a Symfony project, we have to configure the annotation reader
to be able to parse and load the entities.

    
    namespace Shop\Bundle\ProductBundle\Tests;

    use Doctrine\Tests\OrmTestCase;
    use Doctrine\Common\Annotations\AnnotationReader;
    use Doctrine\ORM\Mapping\Driver\DriverChain;
    use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

    class ProductTest extends OrmTestCase
    {
        private $_em;

        protected function setUp()
        {
            $reader = new AnnotationReader();
            $reader->setIgnoreNotImportedAnnotations(true);
            $reader->setEnableParsePhpImports(true);

            $metadataDriver = new AnnotationDriver(
                $reader, 
                // provide the namespace of the entities you want to tests
                'Shop\\Bundle\\ProductBundle\\Entities'  
            );

            $this->_em = $this->_getTestEntityManager();

            $this->_em->getConfiguration()
            	->setMetadataDriverImpl($metadataDriver);

            $this->_em->getConfiguration()->setEntityNamespaces(array(
                'Shop' => 'Shop\\Bundle\\ShopBundle\\Entities'
            ));
        }


If you look at the code, you can notice:

- We extend from `\Doctrine\Tests\OrmTestCase`, which provide useful methods
  for unit testing
- We need to setup the AnnotationReader to be able to parse and load our 
  entities
- We create the entity manager by calling `_getTestEntityManager`: provided
  by Doctrine, this method returns a mocked entity manager which embed a
  mocked connection.

That's it, your are almost ready to write your units tests for your Doctrine
classes.


Unit testing
------------

Now that the autoloader and the annotation reader are successfully loaded, we 
can test our entities.
In this example, we are asserting that the SQL of a custom repository method 
is correct.

    class ProductTest extends \Doctrine\Tests\OrmTestCase
    {
        /* ... */

        public function testProductByCategoryName()
        {
            $query = $this->_em->getRepository('Shop:Product')
                ->searchProductsByNameQuery('foo');

            $this->assertEquals(
                $query->getSql(), 
                'SELECT p0_.id AS id0, p0_.name AS name2 FROM product p0_'.
                ' WHERE s0_.name LIKE ?');
        }
     }


Functional Testing
------------------

If you need to test against a database, i.e. that an executed query returns the
expected result, you will need to boot the kernel to get a valid connection.

    namespace Shop\Bundle\ProductBundle\Tests;

    class ProductFunctionalTest extends WebTestCase
    {
        public function setUp()
        {
        	$kernel = static::createKernel();
        	$kernel->boot();
            $this->_em = $kernel->getContainer()
                ->get('doctrine.orm.entity_manager');
        }

        public function testProductByCategoryName()
        {
            $results = $this->_em->getRepository('Shop:Product')
                ->searchProductsByNameQuery('foo')
                ->getResult();

            $this->assertEquals(count($results), 1);
        }
    }
