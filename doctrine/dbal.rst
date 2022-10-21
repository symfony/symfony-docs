.. index::
   pair: Doctrine; DBAL

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
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"

Further things can be configured in ``config/packages/doctrine.yaml`` - see
:ref:`reference-dbal-configuration`. Remove the ``orm`` key in that file
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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                    <doctrine:type name="custom_first" class="App\Type\CustomFirst"/>
                    <doctrine:type name="custom_second" class="App\Type\CustomSecond"/>
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        use App\Type\CustomFirst;
        use App\Type\CustomSecond;
        use Symfony\Config\DoctrineConfig;

        return static function (DoctrineConfig $doctrine) {
            $dbal = $doctrine->dbal();
            $dbal->type('custom_first')->class(CustomFirst::class);
            $dbal->type('custom_second')->class(CustomSecond::class);
        };

Registering custom Mapping Types in the SchemaTool
--------------------------------------------------

The SchemaTool is used to inspect the database to compare the schema. To
achieve this task, it needs to know which mapping type needs to be used
for each database type. Registering new ones can be done through the configuration.

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal>
                    <doctrine:mapping-type name="enum">string</doctrine:mapping-type>
                </doctrine:dbal>
            </doctrine:config>
        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        use Symfony\Config\DoctrineConfig;

        return static function (DoctrineConfig $doctrine) {
            $dbalDefault = $doctrine->dbal()
                ->connection('default');
            $dbalDefault->mappingType('enum', 'string');
        };

.. _`PDO`: https://www.php.net/pdo
.. _`Doctrine`: https://www.doctrine-project.org/
.. _`DBAL Documentation`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/index.html
.. _`Custom Mapping Types`: https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#custom-mapping-types
