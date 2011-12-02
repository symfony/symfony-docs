.. index::
   single: Tests; Doctrine

How to test Doctrine Repositories
=================================

Unit testing Doctrine repositories in a Symfony project is not a straightforward
task. Indeed, to load a repository you need to load your entities, an entity 
manager, and some other stuff like a connection.

To test your repository, you have two different options:

1) **Functional test**: This includes using a real database connection with
   real database objects. It's easy to setup and can test anything, but is
   slower to execute. See :ref:`cookbook-doctrine-repo-functional-test`.

2) **Unit test**: Unit testing is faster to run and more precise in how you
   test. It does require a little bit more setup, which is covered in this
   document. It can also only test methods that, for example, build queries,
   not methods that actually execute them.

Unit Testing
------------

As Symfony and Doctrine share the same testing framework, it's quite easy to 
implement unit tests in your Symfony project. The ORM comes with its own set
of tools to ease the unit testing and mocking of everything you need, such as
a connection, an entity manager, etc. By using the testing components provided
by Doctrine - along with some basic setup - you can leverage Doctrine's tools
to unit test your repositories.

Keep in mind that if you want to test the actual execution of your queries,
you'll need a functional test (see :ref:`cookbook-doctrine-repo-functional-test`).
Unit testing is only possible when testing a method that builds a query.

Setup
~~~~~

First, you need to add the Doctrine\Tests namespace to your autoloader::

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

            // allows you to use the AcmeProductBundle:Product syntax
            $this->_em->getConfiguration()->setEntityNamespaces(array(
                'AcmeProductBundle' => 'Acme\\ProductBundle\\Entity'
            ));
        }
    }

If you look at the code, you can notice:

* You extend from ``\Doctrine\Tests\OrmTestCase``, which provide useful methods
  for unit testing;

* You need to setup the ``AnnotationReader`` to be able to parse and load the
  entities;

* You create the entity manager by calling ``_getTestEntityManager``, which
  returns a mocked entity manager with a mocked connection.

That's it! You're ready to write units tests for your Doctrine repositories.

Writing your Unit Test
~~~~~~~~~~~~~~~~~~~~~~

Remember that Doctrine repository methods can only be tested if they are
building and returning a query (but not actually executing a query). Take
the following example::

    // src/Acme/StoreBundle/Entity/ProductRepository
    namespace Acme\StoreBundle\Entity;

    use Doctrine\ORM\EntityRepository;

    class ProductRepository extends EntityRepository
    {
        public function createSearchByNameQueryBuilder($name)
        {
            return $this->createQueryBuilder('p')
                ->where('p.name LIKE :name')
                ->setParameter('name', $name);
        }
    }

In this example, the method is returning a ``QueryBuilder`` instance. You
can test the result of this method in a variety of ways::

    class ProductRepositoryTest extends \Doctrine\Tests\OrmTestCase
    {
        /* ... */

        public function testCreateSearchByNameQueryBuilder()
        {
            $queryBuilder = $this->_em->getRepository('AcmeProductBundle:Product')
                ->createSearchByNameQueryBuilder('foo');

            $this->assertEquals('p.name LIKE :name', (string) $queryBuilder->getDqlPart('where'));
            $this->assertEquals(array('name' => 'foo'), $queryBuilder->getParameters());
        }
     }

In this test, you dissect the ``QueryBuilder`` object, looking that each
part is as you'd expect. If you were adding other things to the query builder,
you might check the dql parts: ``select``, ``from``, ``join``, ``set``, ``groupBy``,
``having``, or ``orderBy``.

If you only have a raw ``Query`` object or prefer to test the actual query,
you can test the DQL query string directly::

    public function testCreateSearchByNameQueryBuilder()
    {
        $queryBuilder = $this->_em->getRepository('AcmeProductBundle:Product')
            ->createSearchByNameQueryBuilder('foo');

        $query = $queryBuilder->getQuery();

        // test DQL
        $this->assertEquals(
            'SELECT p FROM Acme\ProductBundle\Entity\Product p WHERE p.name LIKE :name',
            $query->getDql()
        );
    }

.. _cookbook-doctrine-repo-functional-test:

Functional Testing
------------------

If you need to actually execute a query, you will need to boot the kernel
to get a valid connection. In this case, you'll extend the ``WebTestCase``,
which makes all of this quite easy::

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
