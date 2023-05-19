Configuring Symfony
===================

Configuration Files
-------------------

Symfony applications are configured with the files stored in the ``config/``
directory, which has this default structure:

.. code-block:: text

    your-project/
    ├─ config/
    │  ├─ packages/
    │  ├─ bundles.php
    │  ├─ routes.yaml
    │  └─ services.yaml

* The ``routes.yaml`` file defines the :doc:`routing configuration </routing>`;
* The ``services.yaml`` file configures the services of the :doc:`service container </service_container>`;
* The ``bundles.php`` file enables/disables packages in your application;
* The ``config/packages/`` directory stores the configuration of every package
  installed in your application.

Packages (also called "bundles" in Symfony and "plugins/modules" in other
projects) add ready-to-use features to your projects.

When using :ref:`Symfony Flex <symfony-flex>`, which is enabled by default in
Symfony applications, packages update the ``bundles.php`` file and create new
files in ``config/packages/`` automatically during their installation. For
example, this is the default file created by the "API Platform" bundle:

.. code-block:: yaml

    # config/packages/api_platform.yaml
    api_platform:
        mapping:
            paths: ['%kernel.project_dir%/src/Entity']

Splitting the configuration into lots of small files might appear intimidating for some
Symfony newcomers. However, you'll get used to them quickly and you rarely need
to change these files after package installation.

.. tip::

    To learn about all the available configuration options, check out the
    :doc:`Symfony Configuration Reference </reference/index>` or run the
    ``config:dump-reference`` command.

.. _configuration-formats:

Configuration Formats
~~~~~~~~~~~~~~~~~~~~~

Unlike other frameworks, Symfony doesn't impose a specific format on you to
configure your applications, but lets you choose between YAML, XML and PHP.
Throughout the Symfony documentation, all configuration examples will be
shown in these three formats.

There isn't any practical difference between formats. In fact, Symfony
transforms and caches all of them into PHP before running the application, so
there's not even any performance difference between them.

YAML is used by default when installing packages because it's concise and very
readable. These are the main advantages and disadvantages of each format:

* **YAML**: simple, clean and readable, but not all IDEs support autocompletion
  and validation for it. :doc:`Learn the YAML syntax </reference/formats/yaml>`;
* **XML**: autocompleted/validated by most IDEs and is parsed natively by PHP,
  but sometimes it generates configuration considered too verbose. `Learn the XML syntax`_;
* **PHP**: very powerful and it allows you to create dynamic configuration with
  arrays or a :ref:`ConfigBuilder <config-config-builder>`.

.. note::

    By default Symfony loads the configuration files defined in YAML and PHP
    formats. If you define configuration in XML format, update the
    ``src/Kernel.php`` file to add support for the ``.xml`` file extension.

    .. versionadded:: 6.1

        The automatic loading of PHP configuration files was introduced in Symfony 6.1.

Importing Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony loads configuration files using the :doc:`Config component
</components/config>`, which provides advanced features such as importing other
configuration files, even if they use a different format:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: 'legacy_config.php' }

            # glob expressions are also supported to load multiple files
            - { resource: '/etc/myapp/*.yaml' }

            # ignore_errors: not_found silently discards errors if the loaded file doesn't exist
            - { resource: 'my_config_file.xml', ignore_errors: not_found }
            # ignore_errors: true silently discards all errors (including invalid code and not found)
            - { resource: 'my_other_config_file.xml', ignore_errors: true }

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
                <!-- glob expressions are also supported to load multiple files -->
                <import resource="/etc/myapp/*.yaml"/>

                <!-- ignore-errors="not_found" silently discards errors if the loaded file doesn't exist -->
                <import resource="my_config_file.yaml" ignore-errors="not_found"/>
                <!-- ignore-errors="true" silently discards all errors (including invalid code and not found) -->
                <import resource="my_other_config_file.yaml" ignore-errors="true"/>
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container) {
            $container->import('legacy_config.php');

            // glob expressions are also supported to load multiple files
            $container->import('/etc/myapp/*.yaml');

            // the third optional argument of import() is 'ignore_errors'
            // 'ignore_errors' set to 'not_found' silently discards errors if the loaded file doesn't exist
            $container->import('my_config_file.yaml', null, 'not_found');
            // 'ignore_errors' set to true silently discards all errors (including invalid code and not found)
            $container->import('my_config_file.yaml', null, true);
        };

        // ...

.. _config-parameter-intro:
.. _config-parameters-yml:
.. _configuration-parameters:

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
            app.admin_email: 'something@example.com'

            # boolean parameters
            app.enable_v2_protocol: true

            # array/collection parameters
            app.supported_locales: ['en', 'es', 'fr']

            # binary content parameters (encode the contents with base64_encode())
            app.some_parameter: !!binary VGhpcyBpcyBhIEJlbGwgY2hhciAH

            # PHP constants as parameter values
            app.some_constant: !php/const GLOBAL_CONSTANT
            app.another_constant: !php/const App\Entity\BlogPost::MAX_ITEMS

            # Enum case as parameter values
            app.some_enum: !php/enum App\Enum\PostState::Published

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
                     to better differentiate your parameters from Symfony parameters). -->
                <parameter key="app.admin_email">something@example.com</parameter>

                <!-- boolean parameters -->
                <parameter key="app.enable_v2_protocol">true</parameter>
                <!-- if you prefer to store the boolean value as a string in the parameter -->
                <parameter key="app.enable_v2_protocol" type="string">true</parameter>

                <!-- array/collection parameters -->
                <parameter key="app.supported_locales" type="collection">
                    <parameter>en</parameter>
                    <parameter>es</parameter>
                    <parameter>fr</parameter>
                </parameter>

                <!-- binary content parameters (encode the contents with base64_encode()) -->
                <parameter key="app.some_parameter" type="binary">VGhpcyBpcyBhIEJlbGwgY2hhciAH</parameter>

                <!-- PHP constants as parameter values -->
                <parameter key="app.some_constant" type="constant">GLOBAL_CONSTANT</parameter>
                <parameter key="app.another_constant" type="constant">App\Entity\BlogPost::MAX_ITEMS</parameter>

                <!-- Enum case as parameter values -->
                <parameter key="app.some_enum" type="enum">App\Enum\PostState::Published</parameter>
            </parameters>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\BlogPost;
        use App\Enum\PostState;

        return static function (ContainerConfigurator $container) {
            $container->parameters()
                // the parameter name is an arbitrary string (the 'app.' prefix is recommended
                // to better differentiate your parameters from Symfony parameters).
                ->set('app.admin_email', 'something@example.com')

                // boolean parameters
                ->set('app.enable_v2_protocol', true)

                // array/collection parameters
                ->set('app.supported_locales', ['en', 'es', 'fr'])

                // binary content parameters (use the PHP escape sequences)
                ->set('app.some_parameter', 'This is a Bell char: \x07')

                // PHP constants as parameter values
                ->set('app.some_constant', GLOBAL_CONSTANT)
                ->set('app.another_constant', BlogPost::MAX_ITEMS);

                // Enum case as parameter values
                ->set('app.some_enum', PostState::Published);
        };

        // ...

.. caution::

    When using XML configuration, the values between ``<parameter>`` tags are
    not trimmed. This means that the value of the following parameter will be
    ``'\n    something@example.com\n'``:

    .. code-block:: xml

        <parameter key="app.admin_email">
            something@example.com
        </parameter>

.. versionadded:: 6.2

    Passing an enum case as a service parameter was introduced in Symfony 6.2.

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
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container) {
            $container->extension('some_package', [
                // any string surrounded by two % is replaced by that parameter value
                'email_address' => '%app.admin_email%',

                // ...
            ]);
        };


.. note::

    If some parameter value includes the ``%`` character, you need to escape it
    by adding another ``%``, so Symfony doesn't consider it a reference to a
    parameter name:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                # Parsed as 'https://symfony.com/?foo=%s&amp;bar=%d'
                url_pattern: 'https://symfony.com/?foo=%%s&amp;bar=%%d'

        .. code-block:: xml

            <!-- config/services.xml -->
            <parameters>
                <parameter key="url_pattern">http://symfony.com/?foo=%%s&amp;bar=%%d</parameter>
            </parameters>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container) {
                $container->parameters()
                    ->set('url_pattern', 'http://symfony.com/?foo=%%s&amp;bar=%%d');
            };

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

Configuration parameters are very common in Symfony applications. Some packages
even define their own parameters (e.g. when installing the translation package,
a new ``locale`` parameter is added to the ``config/services.yaml`` file).

.. seealso::

    Later in this article you can read how to
    :ref:`get configuration parameters in controllers and services <configuration-accessing-parameters>`.

.. _page-creation-environments:
.. _page-creation-prod-cache-clear:
.. _configuration-environments:

Configuration Environments
--------------------------

You have only one application, but whether you realize it or not, you need it
to behave differently at different times:

* While **developing**, you want to log everything and expose nice debugging tools;
* After deploying to **production**, you want that same application to be
  optimized for speed and only log errors.

The files stored in ``config/packages/`` are used by Symfony to configure the
:doc:`application services </service_container>`. In other words, you can change
the application behavior by changing which configuration files are loaded.
That's the idea of Symfony's **configuration environments**.

A typical Symfony application begins with three environments:

* ``dev`` for local development,
* ``prod`` for production servers,
* ``test`` for :doc:`automated tests </testing>`.

When running the application, Symfony loads the configuration files in this
order (the last files can override the values set in the previous ones):

#. The files in ``config/packages/*.<extension>``;
#. the files in ``config/packages/<environment-name>/*.<extension>``;
#. ``config/services.<extension>``;
#. ``config/services_<environment-name>.<extension>``.

Take the ``framework`` package, installed by default, as an example:

* First, ``config/packages/framework.yaml`` is loaded in all environments and
  it configures the framework with some options;
* In the **prod** environment, nothing extra will be set as there is no
  ``config/packages/prod/framework.yaml`` file;
* In the **dev** environment, there is no file either (
  ``config/packages/dev/framework.yaml`` does not exist).
* In the **test** environment, the ``config/packages/test/framework.yaml`` file
  is loaded to override some of the settings previously configured in
  ``config/packages/framework.yaml``.

In reality, each environment differs only somewhat from others. This means that
all environments share a large base of common configuration, which is put in
files directly in the ``config/packages/`` directory.

.. tip::

    You can also define options for different environments in a single
    configuration file using the special ``when`` keyword:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/webpack_encore.yaml
            webpack_encore:
                # ...
                output_path: '%kernel.project_dir%/public/build'
                strict_mode: true
                cache: false

            # cache is enabled only in the "prod" environment
            when@prod:
                webpack_encore:
                    cache: true

            # disable strict mode only in the "test" environment
            when@test:
                webpack_encore:
                    strict_mode: false

            # YAML syntax allows to reuse contents using "anchors" (&some_name) and "aliases" (*some_name).
            # In this example, 'test' configuration uses the exact same configuration as in 'prod'
            when@prod: &webpack_prod
                webpack_encore:
                    # ...
            when@test: *webpack_prod

        .. code-block:: xml

            <!-- config/packages/webpack_encore.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony
                    https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">
                <webpack-encore:config
                    output-path="%kernel.project_dir%/public/build"
                    strict-mode="true"
                    cache="false"
                />

                <!-- cache is enabled only in the "test" environment -->
                <when env="prod">
                    <webpack-encore:config cache="true"/>
                </when>

                <!-- disable strict mode only in the "test" environment -->
                <when env="test">
                    <webpack-encore:config strict-mode="false"/>
                </when>
            </container>

        .. code-block:: php

            // config/packages/framework.php
            use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
            use Symfony\Config\WebpackEncoreConfig;

            return static function (WebpackEncoreConfig $webpackEncore, ContainerConfigurator $container) {
                $webpackEncore
                    ->outputPath('%kernel.project_dir%/public/build')
                    ->strictMode(true)
                    ->cache(false)
                ;

                // cache is enabled only in the "prod" environment
                if ('prod' === $container->env()) {
                    $webpackEncore->cache(true);
                }

                // disable strict mode only in the "test" environment
                if ('test' === $container->env()) {
                    $webpackEncore->strictMode(false);
                }
            };

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

Open the ``.env`` file (or better, the ``.env.local`` file if you created one)
and edit the value of the ``APP_ENV`` variable to change the environment in
which the application runs. For example, to run the application in production:

.. code-block:: bash

    # .env (or .env.local)
    APP_ENV=prod

This value is used both for the web and for the console commands. However, you
can override it for commands by setting the ``APP_ENV`` value before running them:

.. code-block:: terminal

    # Use the environment defined in the .env file
    $ php bin/console command_name

    # Ignore the .env file and run this command in production
    $ APP_ENV=prod php bin/console command_name

Creating a New Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~

The default three environments provided by Symfony are enough for most projects,
but you can define your own environments too. For example, this is how you can
define a ``staging`` environment where the client can test the project before
going to production:

#. Create a configuration directory with the same name as the environment (in
   this case, ``config/packages/staging/``);
#. Add the needed configuration files in ``config/packages/staging/`` to
   define the behavior of the new environment. Symfony loads the
   ``config/packages/*.yaml`` files first, so you only need to configure the
   differences to those files;
#. Select the ``staging`` environment using the ``APP_ENV`` env var as explained
   in the previous section.

.. tip::

    It's common for environments to be similar to each other, so you can
    use `symbolic links`_ between ``config/packages/<environment-name>/``
    directories to reuse the same configuration.

Instead of creating new environments, you can use environment variables as
explained in the following section. This way you can use the same application
and environment (e.g. ``prod``) but change its behavior thanks to the
configuration based on environment variables (e.g. to run the application in
different scenarios: staging, quality assurance, client review, etc.)

.. _config-env-vars:

Configuration Based on Environment Variables
--------------------------------------------

Using `environment variables`_ (or "env vars" for short) is a common practice to
configure options that depend on where the application is run (e.g. the database
credentials are usually different in production versus your local machine). If
the values are sensitive, you can even :doc:`encrypt them as secrets </configuration/secrets>`.

Use the special syntax ``%env(ENV_VAR_NAME)%`` to reference environment variables.
The values of these options are resolved at runtime (only once per request, to
not impact performance) so you can change the application behavior without having
to clear the cache.

This example shows how you could configure the application secret using an env var:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # by convention the env var names are always uppercase
            secret: '%env(APP_SECRET)%'
            # ...

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/framework"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- by convention the env var names are always uppercase -->
            <framework:config secret="%env(APP_SECRET)%"/>

        </container>

    .. code-block:: php

        // config/packages/framework.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container) {
            $container->extension('framework', [
                // by convention the env var names are always uppercase
                'secret' => '%env(APP_SECRET)%',
            ]);
        };

.. note::

    Your env vars can also be accessed via the PHP super globals ``$_ENV`` and
    ``$_SERVER`` (both are equivalent)::

        $databaseUrl = $_ENV['DATABASE_URL']; // mysql://db_user:db_password@127.0.0.1:3306/db_name
        $env = $_SERVER['APP_ENV']; // prod

    However, in Symfony applications there's no need to use this, because the
    configuration system provides a better way of working with env vars.

.. seealso::

    The values of env vars can only be strings, but Symfony includes some
    :doc:`env var processors </configuration/env_var_processors>` to transform
    their contents (e.g. to turn a string value into an integer).

To define the value of an env var, you have several options:

* :ref:`Add the value to a .env file <config-dot-env>`;
* :ref:`Encrypt the value as a secret <configuration-secrets>`;
* Set the value as a real environment variable in your shell or your web server.

.. tip::

    Some hosts - like Platform.sh - offer easy `utilities to manage env vars`_
    in production.

.. note::

    Some configuration features are not compatible with env vars. For example,
    defining some container parameters conditionally based on the existence of
    another configuration option. When using an env var, the configuration option
    always exists, because its value will be ``null`` when the related env var
    is not defined.

.. caution::

    Beware that dumping the contents of the ``$_SERVER`` and ``$_ENV`` variables
    or outputting the ``phpinfo()`` contents will display the values of the
    environment variables, exposing sensitive information such as the database
    credentials.

    The values of the env vars are also exposed in the web interface of the
    :doc:`Symfony profiler </profiler>`. In practice this shouldn't be a
    problem because the web profiler must **never** be enabled in production.

.. _configuration-env-var-in-dev:
.. _config-dot-env:

Configuring Environment Variables in .env Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of defining env vars in your shell or your web server, Symfony provides
a convenient way to define them inside a ``.env`` (with a leading dot) file
located at the root of your project.

The ``.env`` file is read and parsed on every request and its env vars are added
to the ``$_ENV`` & ``$_SERVER`` PHP variables. Any existing env vars are *never*
overwritten by the values defined in ``.env``, so you can combine both.

For example, to define the ``DATABASE_URL`` env var shown earlier in this article,
you can add:

.. code-block:: bash

    # .env
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

This file should be committed to your repository and (due to that fact) should
only contain "default" values that are good for local development. This file
should not contain production values.

In addition to your own env vars, this ``.env`` file also contains the env vars
defined by the third-party packages installed in your application (they are
added automatically by :ref:`Symfony Flex <symfony-flex>` when installing packages).

.. tip::

    Since the ``.env`` file is read and parsed on every request, you don't need to
    clear the Symfony cache or restart the PHP container if you're using Docker.

.env File Syntax
................

Add comments by prefixing them with ``#``:

.. code-block:: bash

    # database credentials
    DB_USER=root
    DB_PASS=pass # this is the secret password

Use environment variables in values by prefixing variables with ``$``:

.. code-block:: bash

    DB_USER=root
    DB_PASS=${DB_USER}pass # include the user as a password prefix

.. caution::

    The order is important when some env var depends on the value of other env
    vars. In the above example, ``DB_PASS`` must be defined after ``DB_USER``.
    Moreover, if you define multiple ``.env`` files and put ``DB_PASS`` first,
    its value will depend on the ``DB_USER`` value defined in other files
    instead of the value defined in this file.

Define a default value in case the environment variable is not set:

.. code-block:: bash

    DB_USER=
    DB_PASS=${DB_USER:-root}pass # results in DB_PASS=rootpass

Embed commands via ``$()`` (not supported on Windows):

.. code-block:: bash

    START_TIME=$(date)

.. caution::

    Using ``$()`` might not work depending on your shell.

.. tip::

    As a ``.env`` file is a regular shell script, you can ``source`` it in
    your own shell scripts:

    .. code-block:: terminal

        $ source .env

.. _configuration-multiple-env-files:

Overriding Environment Values via .env.local
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to override an environment value (e.g. to a different value on your
local machine), you can do that in a ``.env.local`` file:

.. code-block:: bash

    # .env.local
    DATABASE_URL="mysql://root:@127.0.0.1:3306/my_database_name"

This file should be ignored by git and should *not* be committed to your repository.
Several other ``.env`` files are available to set environment variables in *just*
the right situation:

* ``.env``: defines the default values of the env vars needed by the application;
* ``.env.local``: overrides the default values for all environments but only on
  the machine which contains the file. This file should not be committed to the
  repository and it's ignored in the ``test`` environment (because tests should
  produce the same results for everyone);
* ``.env.<environment>`` (e.g. ``.env.test``): overrides env vars only for one
  environment but for all machines (these files *are* committed);
* ``.env.<environment>.local`` (e.g. ``.env.test.local``): defines machine-specific
  env var overrides only for one environment. It's similar to ``.env.local``,
  but the overrides only apply to one environment.

*Real* environment variables always win over env vars created by any of the
``.env`` files.

The ``.env`` and ``.env.<environment>`` files should be committed to the
repository because they are the same for all developers and machines. However,
the env files ending in ``.local`` (``.env.local`` and ``.env.<environment>.local``)
**should not be committed** because only you will use them. In fact, the
``.gitignore`` file that comes with Symfony prevents them from being committed.

.. _configuration-env-var-in-prod:

Configuring Environment Variables in Production
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In production, the ``.env`` files are also parsed and loaded on each request. So
the easiest way to define env vars is by creating a ``.env.local`` file on your
production server(s) with your production values.

To improve performance, you can optionally run the ``dump-env`` command (available
in :ref:`Symfony Flex <symfony-flex>` 1.2 or later):

.. code-block:: terminal

    # parses ALL .env files and dumps their final values to .env.local.php
    $ composer dump-env prod

After running this command, Symfony will load the ``.env.local.php`` file to
get the environment variables and will not spend time parsing the ``.env`` files.

.. tip::

    Update your deployment tools/workflow to run the ``dump-env`` command after
    each deploy to improve the application performance.

.. _configuration-secrets:

Encrypting Environment Variables (Secrets)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of defining a real environment variable or adding it to a ``.env`` file,
if the value of a variable is sensitive (e.g. an API key or a database password),
you can encrypt the value using the :doc:`secrets management system </configuration/secrets>`.

Listing Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the ``debug:dotenv`` command to understand how Symfony parses the different
``.env`` files to set the value of each environment variable:

.. code-block:: terminal

    $ php bin/console debug:dotenv

    Dotenv Variables & Files
    ========================

    Scanned Files (in descending priority)
    --------------------------------------

    * ⨯ .env.local.php
    * ⨯ .env.dev.local
    * ✓ .env.dev
    * ⨯ .env.local
    * ✓ .env

    Variables
    ---------

    ---------- ------- ---------- ------
     Variable   Value   .env.dev   .env
    ---------- ------- ---------- ------
     FOO        BAR     n/a        BAR
     ALICE      BOB     BOB        bob
    ---------- ------- ---------- ------

    # look for a specific variable passing its full or partial name as an argument
    $ php bin/console debug:dotenv foo

.. versionadded:: 6.2

    The option to pass variable names to ``debug:dotenv`` was introduced in Symfony 6.2.

Additionally, and regardless of how you set environment variables, you can see all
environment variables, with their values, referenced in Symfony's container configuration:

.. code-block:: terminal

    $ php bin/console debug:container --env-vars

    ---------------- ----------------- ---------------------------------------------
     Name             Default value     Real value
    ---------------- ----------------- ---------------------------------------------
     APP_SECRET       n/a               "471a62e2d601a8952deb186e44186cb3"
     FOO              "[1, "2.5", 3]"   n/a
     BAR              null              n/a
    ---------------- ----------------- ---------------------------------------------

    # you can also filter the list of env vars by name:
    $ php bin/console debug:container --env-vars foo

    # run this command to show all the details for a specific env var:
    $ php bin/console debug:container --env-var=FOO

.. _configuration-accessing-parameters:

Accessing Configuration Parameters
----------------------------------

Controllers and services can access all the configuration parameters. This
includes both the :ref:`parameters defined by yourself <configuration-parameters>`
and the parameters created by packages/bundles. Run the following command to see
all the parameters that exist in your application:

.. code-block:: terminal

    $ php bin/console debug:container --parameters

In controllers extending from the :ref:`AbstractController <the-base-controller-class-services>`,
use the ``getParameter()`` helper::

    // src/Controller/UserController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class UserController extends AbstractController
    {
        // ...

        public function index(): Response
        {
            $projectDir = $this->getParameter('kernel.project_dir');
            $adminEmail = $this->getParameter('app.admin_email');

            // ...
        }
    }

In services and controllers not extending from ``AbstractController``, inject
the parameters as arguments of their constructors. You must inject them
explicitly because :doc:`service autowiring </service_container/autowiring>`
doesn't work for parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            app.contents_dir: '...'

        services:
            App\Service\MessageGenerator:
                arguments:
                    $contentsDir: '%app.contents_dir%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="app.contents_dir">...</parameter>
            </parameters>

            <services>
                <service id="App\Service\MessageGenerator">
                    <argument key="$contentsDir">%app.contents_dir%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return static function (ContainerConfigurator $container) {
            $container->parameters()
                ->set('app.contents_dir', '...');

            $container->services()
                ->get(MessageGenerator::class)
                    ->arg('$contentsDir', '%app.contents_dir%');
        };

If you inject the same parameters over and over again, use the
``services._defaults.bind`` option instead. The arguments defined in that option are
injected automatically whenever a service constructor or controller action
defines an argument with that exact name. For example, to inject the value of the
:ref:`kernel.project_dir parameter <configuration-kernel-project-directory>`
whenever a service/controller defines a ``$projectDir`` argument, use this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                bind:
                    # pass this value to any $projectDir argument for any service
                    # that's created in this file (including controller arguments)
                    $projectDir: '%kernel.project_dir%'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false">
                    <!-- pass this value to any $projectDir argument for any service
                         that's created in this file (including controller arguments) -->
                    <bind key="$projectDir">%kernel.project_dir%</bind>
                </defaults>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Controller\LuckyController;

        return static function (ContainerConfigurator $container) {
            $container->services()
                ->defaults()
                    // pass this value to any $projectDir argument for any service
                    // that's created in this file (including controller arguments)
                    ->bind('$projectDir', '%kernel.project_dir%');

            // ...
        };

.. seealso::

    Read the article about :ref:`binding arguments by name and/or type <services-binding>`
    to learn more about this powerful feature.

Finally, if some service needs access to lots of parameters, instead of
injecting each of them individually, you can inject all the application
parameters at once by type-hinting any of its constructor arguments with the
:class:`Symfony\\Component\\DependencyInjection\\ParameterBag\\ContainerBagInterface`::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    // ...

    use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

    class MessageGenerator
    {
        public function __construct(
            private ContainerBagInterface $params,
        ) {
        }

        public function someMethod()
        {
            // get any container parameter from $this->params, which stores all of them
            $sender = $this->params->get('mailer_sender');
            // ...
        }
    }

.. _config-config-builder:

Using PHP ConfigBuilders
------------------------

Writing PHP config is sometimes difficult because you end up with large nested
arrays and you have no autocompletion help from your favorite IDE. A way to
address this is to use "ConfigBuilders". They are objects that will help you
build these arrays.

Symfony generates the ConfigBuilder classes automatically in the
:ref:`kernel build directory <configuration-kernel-build-directory>` for all the
bundles installed in your application. By convention they all live in the
namespace ``Symfony\Config``::

    // config/packages/security.php
    use Symfony\Config\SecurityConfig;

    return static function (SecurityConfig $security) {
        $security->firewall('main')
            ->pattern('^/*')
            ->lazy(true)
            ->anonymous();

        $security
            ->roleHierarchy('ROLE_ADMIN', ['ROLE_USER'])
            ->roleHierarchy('ROLE_SUPER_ADMIN', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'])
            ->accessControl()
                ->path('^/user')
                ->role('ROLE_USER');

        $security->accessControl(['path' => '^/admin', 'roles' => 'ROLE_ADMIN']);
    };

.. note::

    Only root classes in the namespace ``Symfony\Config`` are ConfigBuilders.
    Nested configs (e.g. ``\Symfony\Config\Framework\CacheConfig``) are regular
    PHP objects which aren't autowired when using them as an argument type.

Keep Going!
-----------

Congratulations! You've tackled the basics of Symfony. Next, learn about *each*
part of Symfony individually by following the guides. Check out:

* :doc:`/forms`
* :doc:`/doctrine`
* :doc:`/service_container`
* :doc:`/security`
* :doc:`/mailer`
* :doc:`/logging`

And all the other topics related to configuration:

.. toctree::
    :maxdepth: 1
    :glob:

    configuration/*

.. _`Learn the XML syntax`: https://en.wikipedia.org/wiki/XML
.. _`environment variables`: https://en.wikipedia.org/wiki/Environment_variable
.. _`symbolic links`: https://en.wikipedia.org/wiki/Symbolic_link
.. _`utilities to manage env vars`: https://symfony.com/doc/current/cloud/env.html
