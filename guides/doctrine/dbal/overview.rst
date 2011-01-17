.. index::
   pair: Doctrine; DBAL

DBAL
====

The `Doctrine`_ Database Abstraction Layer (DBAL) is an abstraction layer that
sits on top of `PDO`_ and offers an intuitive and flexible API for
communicating with the most popular relational databases that exist today!

.. tip::

    You can read more about the Doctrine DBAL on the official `documentation`_
    website.

To get started you just need to enable and configure the DBAL:

.. code-block:: yaml

    # app/config/config.yml

    doctrine.dbal:
        driver:   pdo_mysql
        dbname:   Symfony2
        user:     root
        password: null

You can then access the Doctrine DBAL connection by accessing the
``database_connection`` service::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->get('database_connection');

            $users = $conn->fetchAll('SELECT * FROM users');
        }
    }

.. _PDO:           http://www.php.net/pdo
.. _documentation: http://www.doctrine-project.org/projects/dbal/2.0/docs/en
.. _Doctrine:      http://www.doctrine-project.org
