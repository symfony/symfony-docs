.. index::
   pair: Doctrine; DBAL

How to Use Doctrine DBAL
========================

.. note::

    This article is about the Doctrine DBAL. Typically, you'll work with
    the higher level Doctrine ORM layer, which simply uses the DBAL behind
    the scenes to actually communicate with the database. To read more about
    the Doctrine ORM, see ":doc:`/doctrine`".

The `Doctrine`_ Database Abstraction Layer (DBAL) is an abstraction layer that
sits on top of `PDO`_ and offers an intuitive and flexible API for communicating
with the most popular relational databases. In other words, the DBAL library
makes it easy to execute queries and perform other database actions.

.. tip::

    Read the official Doctrine `DBAL Documentation`_ to learn all the details
    and capabilities of Doctrine's DBAL library.

First, install the Doctrine bundle:

.. code-block:: terminal

    $ composer require doctrine/doctrine-bundle

Then configure the ``DATABASE_URL`` environment variable in ``.env``:

.. code-block:: text

    # .env

    # customize this line!
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

Further things can be configured in ``config/packages/doctrine.yaml``. For the full
DBAL configuration options, or to learn how to configure multiple connections,
see :ref:`reference-dbal-configuration`.

You can then access the Doctrine DBAL connection by autowiring the ``Connection``
object::

    use Doctrine\DBAL\Driver\Connection;

    class UserController extends Controller
    {
        public function index(Connection $connection)
        {
            $users = $connection->fetchAll('SELECT * FROM users');

            // ...
        }
    }

This will pass you the ``database_connection`` service.

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

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                    <doctrine:type name="custom_first" class="App\Type\CustomFirst" />
                    <doctrine:type name="custom_second" class="App\Type\CustomSecond" />
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        use App\Type\CustomFirst;
        use App\Type\CustomSecond;

        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'types' => array(
                    'custom_first'  => CustomFirst::class,
                    'custom_second' => CustomSecond::class,
                ),
            ),
        ));

Registering custom Mapping Types in the SchemaTool
--------------------------------------------------

The SchemaTool is used to inspect the database to compare the schema. To
achieve this task, it needs to know which mapping type needs to be used
for each database types. Registering new ones can be done through the configuration.

Now, map the ENUM type (not supported by DBAL by default) to the ``string``
mapping type:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
               mapping_types:
                  enum: string

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                     <doctrine:mapping-type name="enum">string</doctrine:mapping-type>
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
               'mapping_types' => array(
                  'enum'  => 'string',
               ),
            ),
        ));

.. _`PDO`:           https://php.net/pdo
.. _`Doctrine`:      http://www.doctrine-project.org
.. _`DBAL Documentation`: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html
.. _`Custom Mapping Types`: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#custom-mapping-types
