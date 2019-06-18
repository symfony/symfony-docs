.. index::
   single: Configuration

Configuring Symfony
===================

Symfony applications are configured with the files stored in the ``config/``
directory, which has this default structure:

.. code-block:: text

    your-project/
    ├─ config/
    │  ├─ packages/
    │  ├─ bundles.php
    │  ├─ routes.yaml
    │  └─ services.yaml
    ├─ ...

The ``routes.yaml`` file defines the :doc:`routing configuration </routing>`
the ``services.yaml`` file configures the services of the
:doc:`service container </service_container>` the ``bundles.php`` file enables/
disables packages in your application.

You'll be working most in the ``config/packages/`` directory. This directory
stores the configuration of every package installed in your application.
Packages (also called "bundles" in Symfony and "plugins/modules" in other
projects) add ready-to-use features to your projects.

When using :doc:`Symfony Flex </setup/flex>`, which is enabled by default in
Symfony applications, packages update the ``bundles.php`` file and create new
files in ``config/packages/`` automatically during their installation. For
example, this is the default file created by the "API Platform" package:

.. code-block:: yaml

    # config/packages/api_platform.yaml
    api_platform:
        mapping:
            paths: ['%kernel.project_dir%/src/Entity']

Splitting the configuration into lots of small files is intimidating for some
Symfony newcomers. However, you'll get used to them quickly and you rarely need
to change these files after package installation

.. tip::

    To learn about all the available configuration options, check out the
    :doc:`Symfony Configuration Reference </reference/index>` or run the
    ``config:dump-reference`` command.

Configuration Formats
---------------------

Unlike other frameworks, Symfony doesn't impose you a specific format to
configure your applications. Symfony lets you choose between YAML, XML and PHP
and throughout the Symfony documentation, all configuration examples will be
shown in these three formats.

There isn't any practical difference between formats. In fact, Symfony
transforms and caches all of them into PHP before running the application, so
there's not even any performance difference between them.

YAML is used by default when installing packages because it's concise and very
readable. These are the main advantages and disadvantages of each format:

* :doc:`YAML </components/yaml/yaml_format>`: simple, clean and readable, but
  requires using a dedicated parser.
* *XML*: supports IDE autocompletion/validation and is parsed natively by PHP,
  but sometimes it generates too verbose configuration.
* *PHP*: very powerful and it allows to create dynamic configuration, but the
  resulting configuration is less readable than the other formats.

Importing Configuration Files
-----------------------------

Symfony loads configuration files using the :doc:`Config component
</components/config>`, which provides advanced features such as importing
configuration files, even if they use a different format:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: 'legacy_config.php' }
            # ignore_errors silently discards errors if the loaded file doesn't exist
            - { resource: 'my_config_file.xml', ignore_errors: true }
            # glob expressions are also supported to load multiple files
            - { resource: '/etc/myapp/*.yaml' }

        # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="legacy_config.php"/>
                <!-- ignore_errors silently discards errors if the loaded file doesn't exist -->
                <import resource="my_config_file.yaml" ignore-errors="true"/>
                <!-- glob expressions are also supported to load multiple files -->
                <import resource="/etc/myapp/*.yaml"/>
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        $loader->import('legacy_config.xml');
        // the third optional argument of import() is 'ignore_errors', which
        // silently discards errors if the loaded file doesn't exist
        $loader->import('my_config_file.yaml', null, true);
        // glob expressions are also supported to load multiple files
        $loader->import('/etc/myapp/*.yaml');

        // ...

.. tip::

    If you use any other configuration format, you have to define your own loader
    class extending it from :class:`Symfony\\Component\\DependencyInjection\\Loader\\FileLoader`.
    In addition, you can define your own services to load configurations from
    databases or web services.

.. _config-parameter-intro:
.. _config-parameters-yml:

Configuration Parameters
------------------------

Sometimes the same configuration value is used in several configuration files.
Instead of repeating it, you can define it as a "parameter", which is like a
reusable configuration value. By convention, parameters are defined under the
``parameters`` key in the ``config/services.yaml`` file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            # the parameter name is an arbitrary string (the 'app.' prefix is recommended
            # to better differentiate your parameters from Symfony parameters).
            # the parameter value can be any valid scalar (numbers, strings, booleans, arrays)
            app.admin_email: 'something@example.com'

        # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <parameters>
                <!-- the parameter name is an arbitrary string (the 'app.' prefix is recommended
                     to better differentiate your parameters from Symfony parameters).
                     the parameter value can be any valid scalar (numbers, strings, booleans, arrays) -->
                <parameter key="app.admin_email">something@example.com</parameter>
            </parameters>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        // the parameter name is an arbitrary string (the 'app.' prefix is recommended
        // to better differentiate your parameters from Symfony parameters).
        // the parameter value can be any valid scalar (numbers, strings, booleans, arrays)
        $container->setParameter('app.admin_email', 'something@example.com');
        // ...

Once defined, you can reference this parameter value from any other
configuration file using a special syntax: wrap the parameter name in two ``%``
(e.g. ``%app.admin_email%``):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/some_package.yaml
        some_package:
            # any string surrounded by two % is replaced by that parameter value
            email_address: '%app.admin_email%'

            # ...

    .. code-block:: xml

        <!-- config/packages/some_package.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- any string surrounded by two % is replaced by that parameter value -->
            <some-package:config email-address="%app.admin_email%">
                <!-- ... -->
            </some-package:config>
        </container>

    .. code-block:: php

        // config/packages/some_package.php
        $container->loadFromExtension('some_package', [
            // any string surrounded by two % is replaced by that parameter value
            'email_address' => '%app.admin_email%',

            // ...
        ]);

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

Configuration parameters are very common in Symfony applications. Some packages
even define their own parameters (e.g. when installing the translation package,
a new ``locale`` parameter is added to the ``config/services.yaml`` file).

.. seealso::

    Read the :ref:`service parameters <service-container-parameters>` article to
    learn about how to use these configuration parameters in services and controllers.

.. index::
   single: Environments; Introduction

.. _page-creation-environments:
.. _page-creation-prod-cache-clear:
.. _configuration-environments:

Configuration Environments
--------------------------

You have just one application, but whether you realize it or not, you need it
to behave differently at different times:

* While **developing**, you want to log everything and expose nice debugging tools.
* After deploying to **production**, you want that same application to be
  optimized for speed and only log errors.

The files stored in ``config/packages/`` are used by Symfony to configure the
:doc:`application services </service_container>`. In other words, you can change
the application behavior by changing which configuration files are loaded.
That's the idea of Symfony's **configuration environments**.

A typical Symfony application begins with three environments: ``dev`` (for local
development), ``prod`` (for production servers) and ``test`` (for
:doc:`automated tests </testing>`). When running the application, Symfony loads
the configuration files in this order (the last files can override the values
set in the previous ones):

#. ``config/packages/*.yaml`` (and ``.xml`` and ``*.php`` files too)
#. ``config/packages/<environment-name>/*.yaml`` (and ``.xml`` and ``*.php`` files too)
#. ``config/packages/services.yaml`` (and ``services.xml`` and ``services.php`` files too)

Take the ``framework`` package, installed by default, as an example:

* First, ``config/packages/framework.yaml`` is loaded in all environments and
  it configures the framework with some options.
* In the **prod** environment, nothing extra will be set as there is no
  ``config/packages/prod/framework.yaml`` file.
* In the **dev** environment, there is no file either (
  ``config/packages/dev/framework.yaml`` does not exist).
* In the **test** environment, the ``config/packages/test/framework.yaml`` file
  is loaded to override some of the settings previously configured in
  ``config/packages/framework.yaml``.

In reality, each environment differs only somewhat from others. This means that
all environments share a large base of common configurations, which is put in
files directly in the ``config/packages/`` directory.

.. seealso::

    See the ``configureContainer()`` method of
    :doc:`the Kernel class </configuration/front_controllers_and_kernel>` to
    learn everything about the loading order of configuration files.

.. _selecting-the-active-environment:

Selecting the Active Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony applications come with a file called ``.env`` located at the project
root directory. This file is used to define the value of environment variables
and it's explained in detail :ref:`later in this article <config-dot-env>`.

Open the ``.env`` file and edit the value of the ``APP_ENV`` variable to change
the environment in which the application runs. For example, to run the
application in production:

.. code-block:: bash

    # .env
    APP_ENV=prod

This value is used both for the web and for the console commands. However, you
can override it for commands by setting the ``APP_ENV`` value before running them:

.. code-block:: terminal

    # Use the environment defined in the .env file
    $ php bin/console command_name

    # Ignore the .env file and run this command in production
    $ APP_ENV=prod php bin/console command_name

.. deprecated:: 4.2

    In previous Symfony versions you could configure the environment with the
    ``--env`` command option, which was deprecated in Symfony 4.2.

Creating a New Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~

The default three environments provided by Symfony are enough for most projects,
but you can define your own environments too. For example, this is how you can
define a ``staging`` environment where the client can test the project before
going to production:

#. Create a configuration directory with the same name as the environment (in
   this case, ``config/packages/staging/``).
#. Add the needed configuration files in ``config/packages/staging/`` to
   define the behavior of the new environment. Symfony loads first the files in
   ``config/packages/*.yaml``, so you must only configure the differences with
   those files.
#. Select the ``staging`` environment using the ``APP_ENV`` env var as explained
   in the previous section.

.. tip::

    It's common for environments to be similar between each other, so you can
    use `symbolic links`_ between ``config/packages/<environment-name>/``
    directories to reuse the same configuration.

.. _config-env-vars:

Configuration Based on Environment Variables
--------------------------------------------

Using `environment variables`_ (or "env vars" for short) is a common practice to
configure options that depend on where the application is run (e.g. the database
credentials are usually different in production and in your local machine).

Instead of defining those as regular options, you can define them as environment
variables and reference them in the configuration files using the special syntax
``%env(ENV_VAR_NAME)%``. The values of these options are resolved at runtime
(only once per request, to not impact performance).

This example shows how to configure the database connection using an env var:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                # by convention the env var names are always uppercase
                url: '%env(DATABASE_URL)%'
            # ...

    .. code-block:: xml

        <!-- config/packages/doctrine.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/doctrine
                https://symfony.com/schema/dic/doctrine/doctrine-1.0.xsd">

            <doctrine:config>
                <!-- by convention the env var names are always uppercase -->
                <doctrine:dbal url="%env(DATABASE_URL)%"/>
            </doctrine:config>

        </container>

    .. code-block:: php

        // config/packages/doctrine.php
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                // by convention the env var names are always uppercase
                'url' => '%env(DATABASE_URL)%',
            ]
        ]);

The next step is to define the value of those env vars in your shell, your web
server, etc. This is explained in the following sections, but to protect your
application from undefined env vars, you can give them a default value using the
``parameters`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            env(DATABASE_URL): 'sqlite:///%kernel.project_dir%/var/data.db'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="env(DATABASE_URL)">sqlite:///%kernel.project_dir%/var/data.db</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('env(DATABASE_URL)', 'sqlite:///%kernel.project_dir%/var/data.db');

.. seealso::

    The values of env vars can only be strings, but Symfony includes some
    :doc:`env var processors </configuration/env_var_processors>` to transform
    their contents (e.g. to turn a string value into an integer).

In order to define the actual values of env vars, Symfony proposes different
solutions depending if the application is running in production or in your local
development machine.

.. _configuration-env-var-in-dev:
.. _config-dot-env:

Configuring Environment Variables in Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of defining env vars in your shell or your web server, Symfony proposes
a convenient way of defining them in your local machine based on a file called
``.env`` (with a leading dot) located at the root of your project.

The ``.env`` file is read and parsed on every request and its env vars are added
to the ``$_ENV`` PHP variable. The existing env vars are never overwritten by
the values defined in ``.env``, so you can combine both.

This is for example the content of the ``.env`` file to define the value of the
``DATABASE_URL`` env var shown earlier in this article:

.. code-block:: bash

    # .env
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

In addition to your own env vars, this ``.env`` file also contains the env vars
defined by the third-party packages installed in your application (they are
added automatically by :doc:`Symfony Flex </setup/flex>` when installing packages).

Managing Multiple .env Files
............................

The ``.env`` file defines the default values for all env vars. However, it's
common to override some of those values depending on the environment (e.g. to
use a different database for tests) or depending on the machine (e.g. to use a
different OAuth token in your local machine while developing).

That's why you can define multiple ``.env`` files which override the default env
vars. These are the files in priority order (an env var found in one file
overrides the same env var for all the files below):

* ``.env.<environment>.local`` (e.g. ``.env.test.local``): defines/overrides
  env vars only for some environment and only in your local machine.
* ``.env.<environment>`` (e.g. ``.env.test``): defines/overrides env vars for
  some environment and for all machines.
* ``.env.local``: defines/overrides env vars for all environments but only in
  your local machine.
* ``.env``: defines the default value of env vars.

The ``.env`` and ``.env.<environment>`` files should be committed to the shared
repository because they are the same for all developers. However, the
``.env.local`` and ``.env.<environment>.local`` and files **should not be
committed** because only you will use them. In fact, the ``.gitignore`` file
that comes with Symfony prevents them from being committed.

.. caution::

    Applications created before November 2018 had a slightly different system,
    involving a ``.env.dist`` file. For information about upgrading, see:
    :doc:`configuration/dot-env-changes`.

.. _configuration-env-var-in-prod:

Configuring Environment Variables in Production
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In production, the ``.env`` files are also parsed and loaded on each request so
you can override the env vars already defined in the server. In order to improve
performance, you can run the ``dump-env`` command (available when using
:doc:`Symfony Flex </setup/flex>` 1.2 or later).

This command parses all the ``.env`` files once and compiles their contents into
a new PHP-optimized file called  ``.env.local.php``. From that moment, Symfony
will load the parsed file instead of parsing the ``.env`` files again:

.. code-block:: terminal

    $ composer dump-env prod

.. tip::

    Update your deployment tools/workflow to run the ``dump-env`` command after
    each deploy to improve the application performance.

.. caution::

    Beware that dumping the contents of the ``$_SERVER`` and ``$_ENV`` variables
    or outputting the ``phpinfo()`` contents will display the values of the
    environment variables, exposing sensitive information such as the database
    credentials.

    The values of the env vars are also exposed in the web interface of the
    :doc:`Symfony profiler </profiler>`. In practice this shouldn't be a
    problem because the web profiler must **never** be enabled in production.

Defining Environment Variables in the Web Server
................................................

Using the ``.env`` files explained earlier is the recommended way of using env
vars in Symfony applications. However, you can also define env vars in the web
server configuration if you prefer that:

.. configuration-block::

    .. code-block:: apache

        <VirtualHost *:80>
            # ...

            SetEnv DATABASE_URL "mysql://db_user:db_password@127.0.0.1:3306/db_name"
        </VirtualHost>

    .. code-block:: nginx

        fastcgi_param DATABASE_URL "mysql://db_user:db_password@127.0.0.1:3306/db_name";

.. tip::

    SymfonyCloud, the cloud service optimized for Symfony applications, defines
    some `utilities to manage env vars`_ in production.

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

.. _`environment variables`: https://en.wikipedia.org/wiki/Environment_variable
.. _`symbolic links`: https://en.wikipedia.org/wiki/Symbolic_link
.. _`utilities to manage env vars`: https://symfony.com/doc/master/cloud/cookbooks/env.html
