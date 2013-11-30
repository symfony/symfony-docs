.. index::
   single: Tests; Doctrine

How to test Doctrine Repositories
=================================

Unit testing Doctrine repositories in a Symfony project is not recommended.
When you're dealing with a repository, you're really dealing with something
that's meant to be tested against a real database connection.

Fortunately, you can easily test your queries against a real database, as
described below.

.. _cookbook-doctrine-repo-functional-test:

Functional Testing
------------------

If you need to actually execute a query, you will need to boot the kernel
to get a valid connection. In this case, you'll extend the ``KernelTestCase``,
which makes all of this quite easy::

    // src/Acme/StoreBundle/Tests/Entity/ProductRepositoryFunctionalTest.php
    namespace Acme\StoreBundle\Tests\Entity;

    use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

    class ProductRepositoryFunctionalTest extends KernelTestCase
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        private $em;

        /**
         * {@inheritDoc}
         */
        public function setUp()
        {
            self::bootKernel();
            $this->em = static::$kernel->getContainer()
                ->get('doctrine')
                ->getManager()
            ;
        }

        public function testSearchByCategoryName()
        {
            $products = $this->em
                ->getRepository('AcmeStoreBundle:Product')
                ->searchByCategoryName('foo')
            ;

            $this->assertCount(1, $products);
        }

        /**
         * {@inheritDoc}
         */
        protected function tearDown()
        {
            parent::tearDown();
            $this->em->close();
        }
    }
