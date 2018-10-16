.. index::
   single: Tests; Doctrine

How to Test Doctrine Repositories
=================================

Unit testing Doctrine repositories in a Symfony project is not recommended.
When you're dealing with a repository, you're really dealing with something
that's meant to be tested against a real database connection.

Fortunately, you can easily test your queries against a real database, as
described below.

Functional Testing
------------------

If you need to actually execute a query, you will need to boot the kernel
to get a valid connection. In this case, you'll extend the ``KernelTestCase``,
which makes all of this quite easy::

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

        /**
         * {@inheritDoc}
         */
        protected function setUp()
        {
            $kernel = self::bootKernel();

            $this->entityManager = $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        }

        public function testSearchByCategoryName()
        {
            $products = $this->entityManager
                ->getRepository(Product::class)
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

            $this->entityManager->close();
            $this->entityManager = null; // avoid memory leaks
        }
    }
