How to Use Doctrine DBAL
========================

.. note::

    This article is about the Doctrine DBAL. Typically, you'll work with
    the higher level Doctrine ORM layer, which uses the DBAL behind
    the scenes to actually communicate with the database. To read more about
    the Doctrine ORM, see ":doc:`/doctrine`".

The `Doctrine`_ Database Abstraction Layer (DBAL) is an abstraction layer that
sits on top of `PDO`_ and offers an intuitive and flexible API for communicating
with the most popular relational databases. The DBAL library allows you to write
queries independently of your ORM models, e.g. for building reports or direct
data manipulations.

.. tip::

    Read the official Doctrine `DBAL Documentation`_ to learn all the details
    and capabilities of Doctrine's DBAL library.

First, install the Doctrine ``orm`` :ref:`Symfony pack <symfony-packs>`:

.. code-block:: terminal

    $ composer require symfony/orm-pack

Then configure the ``DATABASE_URL`` environment variable in ``.env``:

.. code-block:: text

    # .env (or override DATABASE_URL in .env.local to avoid committing your changes)

    # customize this line!
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0.37"

Further things can be configured in ``config/packages/doctrine.yaml`` - see
`Doctrine DBAL configuration reference`_. Remove the ``orm`` key in that file
if you *don't* want to use the Doctrine ORM.

You can then access the Doctrine DBAL connection by autowiring the ``Connection``
object::

    // src/Controller/UserController.php
    namespace App\Controller;

    use Doctrine\DBAL\Connection;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class UserController extends AbstractController
    {
        public function index(Connection $connection): Response
        {
            $users = $connection->fetchAllAssociative('SELECT * FROM users');

            // ...
        }
    }

This will pass you the ``database_connection`` service.

.. _doctrine-dbal-read-replicas:

Using Primary/Replica Connections (Read Replicas)
-------------------------------------------------

When your application uses a database cluster with read replicas, you can
configure Doctrine to automatically route read queries to a replica and
write queries to the primary database. This reduces the load on the primary
database and improves performance for read-heavy applications.

First, define a ``DATABASE_REPLICA_URL`` environment variable in ``.env``:

.. code-block:: text

    # .env
    DATABASE_REPLICA_URL="mysql://replica_user:replica_password@replica-host:3306/db_name?serverVersion=8.0.37"

Then configure the replicas in your Doctrine configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        when@prod:
            doctrine:
                dbal:
                    url: '%env(resolve:DATABASE_URL)%'
                    replicas:
                        replica1:
                            url: '%env(resolve:DATABASE_REPLICA_URL)%'

    .. code-block:: php

        // config/packages/doctrine.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'doctrine' => [
                'dbal' => [
                    'connections' => [
                        'default' => [
                            'url' => env('DATABASE_URL')->resolve(),
                            'replica' => [
                                'replica1' => [
                                    'url' => env('DATABASE_REPLICA_URL')->resolve(),
                                ],
                            ],
                        ],
                    ],
                    'default_connection' => 'default',
                ],
            ],
        ]);

You can add as many replicas as needed (e.g. ``replica2``, ``replica3``). When
multiple replicas are configured, Doctrine randomly selects one when connecting
to a replica and keeps using it for subsequent read operations on that connection.

With this configuration, Doctrine uses the ``PrimaryReadReplicaConnection`` wrapper
class from Doctrine DBAL, which decides where to route each database operation:

* **Read operations** (e.g. ``fetchAllAssociative()``, ``executeQuery()``)
  are sent to a replica;
* **Write operations** (e.g. ``executeStatement()``) and transactions are
  sent to the primary;
* Once the primary has been used, **all subsequent operations** on that
  connection use the primary too, ensuring read-your-writes consistency.

.. note::

    The routing is based on which DBAL method your code calls, not on
    SQL-level detection. If you execute a write query through a read method
    like ``executeQuery()``, it will be sent to a replica. Always use the
    appropriate DBAL methods (``executeStatement()`` for writes,
    ``executeQuery()`` for reads) to ensure correct routing.

.. note::

    In long-running processes (e.g. Messenger workers or FrankenPHP worker
    mode), the connection instance is reused across messages and requests,
    so the "switch to primary" behavior applies for the lifetime of that
    connection instance, not just a single HTTP request. Switching back to
    a replica must be done explicitly (see below).

.. tip::

    Set the ``keep_replica`` option to ``true`` to keep the replica connection
    open after the primary has been used. This doesn't change the automatic
    routing; once the primary has been picked, read methods like
    ``executeQuery()`` keep using it. What this option allows is switching back
    to the replica explicitly by calling ``$connection->ensureConnectedToReplica()``,
    which is useful in long-running processes. Without ``keep_replica``, that
    method has no effect, because the replica connection is replaced by the
    primary one after the first write:

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        when@prod:
            doctrine:
                dbal:
                    url: '%env(resolve:DATABASE_URL)%'
                    keep_replica: true
                    replicas:
                        replica1:
                            url: '%env(resolve:DATABASE_REPLICA_URL)%'

Forcing the Primary Connection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, you may need to force the primary connection for a read
query (e.g. right after a write to ensure data consistency). You can do
so by calling the ``ensureConnectedToPrimary()`` method::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use Doctrine\DBAL\Connection;
    use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class ProductController extends AbstractController
    {
        public function index(Connection $connection): Response
        {
            if ($connection instanceof PrimaryReadReplicaConnection) {
                $connection->ensureConnectedToPrimary();
            }

            // the following query will be executed on the primary
            $result = $connection->fetchAllAssociative('SELECT * FROM product');

            // ...
        }
    }

The ``instanceof`` check ensures the code works in all environments: in
production where a replica is configured, ``$connection`` is an instance
of ``PrimaryReadReplicaConnection``; in development without replicas, it
is a regular ``Connection`` instance.

Registering custom Mapping Types
--------------------------------

You can register custom mapping types through Symfony's configuration. They
will be added to all configured connections. For more information on custom
mapping types, read Doctrine's `Custom Mapping Types`_ section of their documentation.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                types:
                    custom_first:  App\Type\CustomFirst
                    custom_second: App\Type\CustomSecond

    .. code-block:: php

        // config/packages/doctrine.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Type\CustomFirst;
        use App\Type\CustomSecond;

        return App::config([
            'doctrine' => [
                'dbal' => [
                    'types' => [
                        'custom_first' => CustomFirst::class,
                        'custom_second' => CustomSecond::class,
                    ],
                ],
            ],
        ]);

Registering custom Mapping Types in the SchemaTool
--------------------------------------------------

The SchemaTool is used to inspect the database to compare the schema. To
achieve this task, it needs to know which mapping type needs to be used
for each database type. Registering new ones can be done through the configuration.

Now, map the ENUM type (not supported by Doctrine DBAL by default) to the
``text`` mapping type:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                mapping_types:
                    enum: text

    .. code-block:: php

        // config/packages/doctrine.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'doctrine' => [
                'dbal' => [
                    'mapping_types' => [
                        'enum' => 'text',
                    ],
                ],
            ],
        ]);

.. note::

    On Doctrine DBAL 3 you can also map ``enum`` to ``string``, but that no
    longer works on DBAL 4, where ``string`` columns require an explicit
    length. Mapping to ``text`` works on both versions.

.. tip::

    Doctrine DBAL 4.2 added native support for the ``ENUM`` type of MySQL
    and MariaDB. When using DBAL 4.2 or newer, ``ENUM`` columns are
    introspected natively, so you don't need to register any custom mapping
    type for them.

.. _`PDO`: https://www.php.net/pdo
.. _`Doctrine`: https://www.doctrine-project.org/
.. _`DBAL Documentation`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html
.. _`Custom Mapping Types`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#custom-mapping-types
.. _`Doctrine DBAL configuration reference`: https://symfony.com/bundles/DoctrineBundle/current/configuration.html
