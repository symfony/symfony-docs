.. index::
   pair: Doctrine; DBAL

DBAL
====

The Doctrine Database Abstraction Layer (DBAL) offers an intuitive and
flexible API for communicating with the most popular relational databases that
exist today. In order to start using the DBAL, configure it:

The Doctrine Database Abstraction Layer (DBAL) is an abstraction layer that sits on top of
`PDO`_ and offers an intuitive and flexible API for communicating with the most popular
relational databases that exist today!

To get started you just need to enable the DBAL:

.. code-block:: yaml

    # config/config.yml

    doctrine.dbal:
        driver:   PDOMySql
        dbname:   Symfony2
        user:     root
        password: null

You can then access the Doctrine DBAL connection by accessing the ``database_connection`` service::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->container->getService('database_connection');

            $users = $conn->fetchAll('SELECT * FROM users');
        }
    }

.. _PDO: http://www.php.net/pdo