.. index::
   single: Configuration

Configuring Symfony (and Environments)
======================================

Every Symfony application consists of a collection of bundles that add useful tools
(:doc:`services </service_container>`) to your project. Each bundle can be customized
via configuration files that live - by default - in the ``app/config`` directory.

Configuration: config.yml
-------------------------

The main configuration file is called ``config.yml``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: parameters.yml }
            - { resource: security.yml }
            - { resource: services.yml }

        framework:
            secret:          '%secret%'
            router:          { resource: '%kernel.project_dir%/app/config/routing.yml' }
            # ...

        # Twig Configuration
        twig:
            debug:            '%kernel.debug%'
            strict_variables: '%kernel.debug%'

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="security.yml" />
                <import resource="services.yml" />
            </imports>

            <framework:config secret="%secret%">
                <framework:router resource="%kernel.project_dir%/app/config/routing.xml" />
                <!-- ... -->
            </framework:config>

            <!-- Twig Configuration -->
            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%" />

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $this->import('parameters.yml');
        $this->import('security.yml');
        $this->import('services.yml');

        $container->loadFromExtension('framework', array(
            'secret' => '%secret%',
            'router' => array(
                'resource' => '%kernel.project_dir%/app/config/routing.php',
            ),
            // ...
        ));

        // Twig Configuration
        $container->loadFromExtension('twig', array(
            'debug'            => '%kernel.debug%',
            'strict_variables' => '%kernel.debug%',
        ));

        // ...

Most top-level keys - like ``framework`` and ``twig`` - are configuration for a
specific bundle (i.e. ``FrameworkBundle`` and ``TwigBundle``).

.. sidebar:: Configuration Formats

    Throughout the documentation, all configuration examples will be shown in
    three formats (YAML, XML and PHP). YAML is used by default, but you can
    choose whatever you like best. There is no performance difference:

    * :doc:`/components/yaml/yaml_format`: Simple, clean and readable;

    * *XML*: More powerful than YAML at times & supports IDE autocompletion;

    * *PHP*: Very powerful but less readable than standard configuration formats.

Configuration Reference & Dumping
---------------------------------

There are *two* ways to know *what* keys you can configure:

#. Use the :doc:`Reference Section </reference/index>`;

#. Use the ``config:dump-reference`` command.

For example, if you want to configure something in Twig, you can see an example
dump of all available configuration options by running:

.. code-block:: terminal

    $ php bin/console config:dump-reference twig

.. index::
   single: Environments; Introduction

.. _page-creation-environments:
.. _page-creation-prod-cache-clear:

The imports Key: Loading other Configuration Files
--------------------------------------------------

Symfony's main configuration file is ``app/config/config.yml``. But, for organization,
it *also* loads other configuration files via its ``imports`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: parameters.yml }
            - { resource: security.yml }
            - { resource: services.yml }
        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="security.yml" />
                <import resource="services.yml" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $this->import('parameters.yml');
        $this->import('security.yml');
        $this->import('services.yml');

        // ...

The ``imports`` key works a lot like the PHP ``include()`` function: the contents of
``parameters.yml``, ``security.yml`` and ``services.yml`` are read and loaded. You
can also load XML files or PHP files.

.. tip::

    If your application uses unconventional file extensions (for example, your
    YAML files have a ``.res`` extension) you can set the file type explicitly
    with the ``type`` option:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            imports:
                - { resource: parameters.res, type: yml }
                # ...

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xmlns:twig="http://symfony.com/schema/dic/twig"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    http://symfony.com/schema/dic/symfony/symfony-1.0.xsd
                    http://symfony.com/schema/dic/twig
                    http://symfony.com/schema/dic/twig/twig-1.0.xsd">

                <imports>
                    <import resource="parameters.res" type="yml" />
                    <!-- ... -->
                </imports>
            </container>

        .. code-block:: php

            // app/config/config.php
            $this->import('parameters.res', 'yml');
            // ...

.. _config-parameter-intro:

The parameters Key: Parameters (Variables)
------------------------------------------

Another special key is called ``parameters``: it's used to define *variables* that
can be referenced in *any* other configuration file. For example, in ``config.yml``,
a ``locale`` parameter is defined and then referenced below under the ``framework``
key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        # ...

        parameters:
            locale: en

        framework:
            # ...

            # any string surrounded by two % is replaced by that parameter value
            default_locale:  "%locale%"

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <parameters>
                <parameter key="locale">en</parameter>
            </parameters>

            <framework:config default-locale="%locale%">
                <!-- ... -->
            </framework:config>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        // ...

        $container->setParameter('locale', 'en');

        $container->loadFromExtension('framework', array(
            'default_locale' => '%locale%',
            // ...
        ));

        // ...

You can define whatever parameter names you want under the ``parameters`` key of
any configuration file. To reference a parameter, surround its name with two percent
signs - e.g. ``%locale%``.

.. seealso::

    You can also set parameters dynamically, like from environment variables.
    See :doc:`/configuration/external_parameters`.

For more information about parameters - including how to reference them from inside
a controller - see :ref:`service-container-parameters`.

.. _config-parameters-yml:

The Special parameters.yml File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On the surface, ``parameters.yml`` is just like any other configuration file: it
is imported by ``config.yml`` and defines several parameters:

.. code-block:: yaml

    parameters:
        # ...
        database_user:      root
        database_password:  ~

Not surprisingly, these are referenced from inside of ``config.yml`` and help to
configure DoctrineBundle and other parts of Symfony:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            dbal:
                driver:   pdo_mysql
                # ...
                user:     '%database_user%'
                password: '%database_password%'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                http://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <doctrine:dbal
                    driver="pdo_mysql"

                    user="%database_user%"
                    password="%database_password%" />
            </doctrine:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'driver'   => 'pdo_mysql',
                // ...

                'user'     => '%database_user%',
                'password' => '%database_password%',
            ),
        ));

But the ``parameters.yml`` file *is* special: it defines the values that usually
change on each server. For example, the database credentials on your local
development machine might be different from your workmates. That's why this file
is not committed to the shared repository and is only stored on your machine.

Because of that, **parameters.yml is not committed to your version control**. In fact,
the ``.gitignore`` file that comes with Symfony prevents it from being committed.

However, a ``parameters.yml.dist`` file *is* committed (with dummy values). This file
isn't read by Symfony: it's just a reference so that Symfony knows which parameters
need to be defined in the ``parameters.yml`` file. If you add or remove keys to
``parameters.yml``, add or remove them from ``parameters.yml.dist`` too so both
files are always in sync.

.. sidebar:: The Interactive Parameter Handler

    When you :ref:`install an existing Symfony project <install-existing-app>`, you
    will need to create the ``parameters.yml`` file using the committed ``parameters.yml.dist``
    file as a reference. To help with this, after you run ``composer install``, a
    Symfony script will automatically create this file by interactively asking you
    to supply the value for each parameter defined in ``parameters.yml.dist``. For
    more details - or to remove or control this behavior - see the
    `Incenteev Parameter Handler`_ documentation.

Environments & the Other Config Files
-------------------------------------

You have just *one* app, but whether you realize it or not, you need it to behave
*differently* at different times:

* While **developing**, you want your app to log everything and expose nice debugging
  tools;

* After deploying to **production**, you want that *same* app to be optimized for
  speed and only log errors.

How can you make *one* application behave in two different ways? With *environments*.

You've probably already been using the ``dev`` environment without even knowing it.
After you deploy, you'll use the ``prod`` environment.

To learn more about *how* to execute and control each environment, see
:doc:`/configuration/environments`.

Keep Going!
-----------

Congratulations! You've tackled the basics in Symfony. Next, learn about *each*
part of Symfony individually by following the guides. Check out:

* :doc:`/forms`
* :doc:`/doctrine`
* :doc:`/service_container`
* :doc:`/security`
* :doc:`/email`
* :doc:`/logging`

And the many other topics.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    configuration/*

.. _`Incenteev Parameter Handler`: https://github.com/Incenteev/ParameterHandler
