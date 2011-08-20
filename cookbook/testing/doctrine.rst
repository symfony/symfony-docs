.. index::
   single: Tests; Doctrine

How to test Doctrine Repositories
=================================

Unit testing Doctrine repositories in a Symfony project is not a straightforward
task. Indeed, to load a repository you need to load your entities, an entity 
manager, and some other stuff like a connection.

To test your repository, you have two different options:

1) **Functional test**: This includes using a real database connection with
   real database objects. It's easy to setup, but slower to execute. See
   :ref:`cookbook-doctrine-repo-functional-test`.

2) **Unit test**: Unit testing is faster to run and more precise in how you
   test. It does require a little bit more setup, which is covered in this
   document.

Unit Testing
------------

As Symfony and Doctrine share the same testing framework, it's quite easy to 
implement unit tests in your Symfony project. The ORM comes with its own set
of tools to ease the unit testing and mocking of everything you need, such as
a connection, an entity manager, etc. By using the testing components provided
by Doctrine - along with some basic setup - you can leverage Doctrine's tools
to unit test your repositories.

Keep in mind than if you want to test the queries created in your repository
against a real database, it's no longer a unit test, but rather a functional
test (see :ref:`cookbook-doctrine-repo-functional-test`).

Setup
~~~~~

First at all, you need to add the Doctrine\Tests namespace to your autoloader::

    // app/autoload.php
    $loader->registerNamespaces(array(
        //...
        'Doctrine\\Tests'                => __DIR__.'/../vendor/doctrine/tests',
    ));

Next, you will need to setup an entity manager in each test so that Doctrine
will be able to load your entities and repositories for you.

As Doctrine is not able by default to load annotation metadata from your
entities, you'll need to configure the annotation reader to be able to parse
and load the entities::

    // src/Acme/ProductBundle/Tests/Entity/ProductRepositoryTest.php
    namespace Acme\ProductBundle\Tests\Entity;

    use Doctrine\Tests\OrmTestCase;
    use Doctrine\Common\Annotations\AnnotationReader;
    use Doctrine\ORM\Mapping\Driver\DriverChain;
    use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

    class ProductRepositoryTest extends OrmTestCase
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
                'Acme\\ProductBundle\\Entity'
            );

            $this->_em = $this->_getTestEntityManager();

            $this->_em->getConfiguration()
            	->setMetadataDriverImpl($metadataDriver);

            $this->_em->getConfiguration()->setEntityNamespaces(array(
                'AcmeProductBundle' => 'Acme\\ProductBundle\\Entity'
            ));
        }
    }

If you look at the code, you can notice:

* We extend from ``\Doctrine\Tests\OrmTestCase``, which provide useful methods
  for unit testing;
* We need to setup the ``AnnotationReader`` to be able to parse and load the
  entities;
- We create the entity manager by calling ``_getTestEntityManager``, which
  returns a mocked entity manager with a mocked connection.

That's it! You're ready to write units tests for your Doctrine repositories.

Writing your Unit Test
~~~~~~~~~~~~~~~~~~~~~~

Now that the autoloader and the annotation reader are successfully loaded, we 
can test the methods of your repository.

In this example, we are asserting that the SQL of a custom repository method
is correct.

    class ProductRepositoryTest extends \Doctrine\Tests\OrmTestCase
    {
        /* ... */

        public function testProductByCategoryName()
        {
            $query = $this->_em->getRepository('AcmeProductBundle:Product')
                ->searchProductsByNameQuery('foo');

            $this->assertEquals(
                $query->getSql(), 
                'SELECT p0_.id AS id0, p0_.name AS name2 FROM product p0_'.
                ' WHERE s0_.name LIKE ?');
        }
     }

If asserting that the query string is exactly correct doesn't suit you, that's
ok! Using functional tests for your repositories (covered next) is also a
great way to test your repositories.

.. _cookbook-doctrine-repo-functional-test:

Functional Testing
------------------

If you need to test against a database (i.e. that an executed query returns the
expected result) you will need to boot the kernel to get a valid connection.

    // src/Acme/ProductBundle/Tests/Entity/ProductRepositoryFunctionalTest.php
    namespace Acme\ProductBundle\Tests\Entity;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    class ProductRepositoryFunctionalTest extends WebTestCase
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        private $_em;
    
        public function setUp()
        {
        	$kernel = static::createKernel();
        	$kernel->boot();
            $this->_em = $kernel->getContainer()
                ->get('doctrine.orm.entity_manager');
        }

        public function testProductByCategoryName()
        {
            $results = $this->_em->getRepository('AcmeProductBundle:Product')
                ->searchProductsByNameQuery('foo')
                ->getResult();

            $this->assertEquals(count($results), 1);
        }
    }
