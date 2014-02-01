.. index::
   pair: Doctrine; DBAL

How to use Doctrine's DBAL Layer
================================

.. note::

    This article is about Doctrine DBAL's layer. Typically, you'll work with
    the higher level Doctrine ORM layer, which simply uses the DBAL behind
    the scenes to actually communicate with the database. To read more about
    the Doctrine ORM, see ":doc:`/book/doctrine`".

The `Doctrine`_ Database Abstraction Layer (DBAL) is an abstraction layer that
sits on top of `PDO`_ and offers an intuitive and flexible API for communicating
with the most popular relational databases. In other words, the DBAL library
makes it easy to execute queries and perform other database actions.

.. tip::

    Read the official Doctrine `DBAL Documentation`_ to learn all the details
    and capabilities of Doctrine's DBAL library.

To get started, configure the database connection parameters:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                driver:   pdo_mysql
                dbname:   Symfony2
                user:     root
                password: null
                charset:  UTF8

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <doctrine:config>
            <doctrine:dbal
                name="default"
                dbname="Symfony2"
                user="root"
                password="null"
                driver="pdo_mysql"
            />
        </doctrine:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'driver'    => 'pdo_mysql',
                'dbname'    => 'Symfony2',
                'user'      => 'root',
                'password'  => null,
            ),
        ));

For full DBAL configuration options, see :ref:`reference-dbal-configuration`.

You can then access the Doctrine DBAL connection by accessing the
``database_connection`` service::

    class UserController extends Controller
    {
        public function indexAction()
        {
            $conn = $this->get('database_connection');
            $users = $conn->fetchAll('SELECT * FROM users');

            // ...
        }
    }

Registering Custom Mapping Types
--------------------------------

You can register custom mapping types through Symfony's configuration. They
will be added to all configured connections. For more information on custom
mapping types, read Doctrine's `Custom Mapping Types`_ section of their documentation.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                types:
                    custom_first:  Acme\HelloBundle\Type\CustomFirst
                    custom_second: Acme\HelloBundle\Type\CustomSecond

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                    <doctrine:type name="custom_first" class="Acme\HelloBundle\Type\CustomFirst" />
                    <doctrine:type name="custom_second" class="Acme\HelloBundle\Type\CustomSecond" />
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'types' => array(
                    'custom_first'  => 'Acme\HelloBundle\Type\CustomFirst',
                    'custom_second' => 'Acme\HelloBundle\Type\CustomSecond',
                ),
            ),
        ));

Registering Custom Mapping Types in the SchemaTool
--------------------------------------------------

The SchemaTool is used to inspect the database to compare the schema. To
achieve this task, it needs to know which mapping type needs to be used
for each database types. Registering new ones can be done through the configuration.

Now, map the ENUM type (not supported by DBAL by default) to the ``string``
mapping type:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                connections:
                    default:
                        # other connections parameters
                        mapping_types:
                            enum: string

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/doctrine http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection>
                        <!-- other connections parameters -->
                        <doctrine:mapping-type name="enum">string</doctrine:mapping-type>
                    </doctrine:connection>
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'connections' => array(
                    'default' => array(
                        // other connection parameers
                        'mapping_types' => array(
                            'enum'  => 'string',
                        ),
                    ),
                ),
            ),
        ));

.. _`PDO`:           http://www.php.net/pdo
.. _`Doctrine`:      http://www.doctrine-project.org
.. _`DBAL Documentation`: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html
.. _`Custom Mapping Types`: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#custom-mapping-types
