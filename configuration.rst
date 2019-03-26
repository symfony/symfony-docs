.. index::
   single: Configuration

Configuring Symfony (and Environments)
======================================

Symfony applications can install third-party packages (bundles, libraries, etc.)
to bring in new features (:doc:`services </service_container>`) to your project.
Each package can be customized via configuration files that live - by default -
in the ``config/`` directory.

Configuration: config/packages/
-------------------------------

The configuration for each package can be found in ``config/packages/``. For
instance, the framework bundle is configured in ``config/packages/framework.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            secret: '%env(APP_SECRET)%'
            #default_locale: en
            #csrf_protection: true
            #http_method_override: true

            # Enables session support. Note that the session will ONLY be started if you read or write from it.
            # Remove or comment this section to explicitly disable session support.
            session:
                handler_id: ~

            #esi: true
            #fragments: true
            php_errors:
                log: true

    .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:framework="http://symfony.com/schema/dic/framework"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/framework https://symfony.com/schema/dic/framework/framework-1.0.xsd"
            >
                <framework:config secret="%env(APP_SECRET)%">
                    <!--<framework:csrf-protection enabled="true"/>-->
                    <!--<framework:esi enabled="true"/>-->
                    <!--<framework:fragments enabled="true"/>-->

                    <!-- Enables session support. Note that the session will ONLY be started if you read or write from it.
                         Remove or comment this section to explicitly disable session support. -->
                    <framework:session/>

                    <framework:php-errors log="true"/>
                </framework:config>
            </container>

    .. code-block:: php

        // config/packages/framework.php
        $container->loadFromExtension('framework', [
            'secret' => '%env(APP_SECRET)%',
            //'default_locale' => 'en',
            //'csrf_protection' => true,
            //'http_method_override' => true,

            // Enables session support. Note that the session will ONLY be started if you read or write from it.
            // Remove or comment this section to explicitly disable session support.
            'session' => [
                'handler_id' => null,
            ],
            //'esi' => true,
            //'fragments' => true,
            'php_errors' => [
                'log' => true,
            ],
        ]);

The top-level key (here ``framework``) references configuration for a specific
bundle (:doc:`FrameworkBundle </reference/configuration/framework>` in this case).

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

For example, if you want to configure something related to the framework bundle,
you can see an example dump of all available configuration options by running:

.. code-block:: terminal

    $ php bin/console config:dump-reference framework

.. index::
   single: Environments; Introduction

.. _page-creation-environments:
.. _page-creation-prod-cache-clear:

.. _config-parameter-intro:

The parameters Key: Parameters (Variables)
------------------------------------------

The configuration has some special top-level keys. One of them is called
``parameters``: it's used to define *variables* that can be referenced in *any*
other configuration file. For example, when you install the *translation*
package, a ``locale`` parameter is added  to ``config/services.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            locale: en

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
                <parameter key="locale">en</parameter>
            </parameters>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('locale', 'en');
        // ...

This parameter is then referenced in the framework config in
``config/packages/translation.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/translation.yaml
        framework:
            # any string surrounded by two % is replaced by that parameter value
            default_locale: '%locale%'

            # ...

    .. code-block:: xml

        <!-- config/packages/translation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- any string surrounded by two % is replaced by that parameter value -->
            <framework:config default-locale="%locale%">
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        $container->loadFromExtension('framework', [
            // any string surrounded by two % is replaced by that parameter value
            'default_locale' => '%locale%',

            // ...
        ]);

You can define whatever parameter names you want under the ``parameters`` key of
any configuration file. To reference a parameter, surround its name with two
percent signs - e.g. ``%locale%``.

.. seealso::

    You can also set parameters dynamically, like from environment variables.
    See :doc:`/configuration/environment_variables`.

For more information about parameters - including how to reference them from inside
a controller - see :ref:`service-container-parameters`.

.. _config-dot-env:
.. _config-parameters-yml:

The .env File & Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There is also a ``.env`` file which is loaded and its contents become environment
variables. This is useful during development, or if setting environment variables
is difficult for your deployment.

When you install packages, more environment variables are added to this file. But
you can also add your own.

Environment variables can be referenced in any other configuration files by using
a special syntax. For example, if you install the ``doctrine`` package, then you
will have an environment variable called ``DATABASE_URL`` in your ``.env`` file.
This is referenced inside ``config/packages/doctrine.yaml``:

.. code-block:: yaml

    # config/packages/doctrine.yaml
    doctrine:
        dbal:
            url: '%env(DATABASE_URL)%'

            # The `resolve:` prefix replaces container params by their values inside the env variable:
            # url: '%env(resolve:DATABASE_URL)%'

For more details about environment variables, see :ref:`config-env-vars`.

.. caution::

    Applications created before November 2018 had a slightly different system,
    involving a ``.env.dist`` file. For information about upgrading, see:
    :doc:`configuration/dot-env-changes`.

The ``.env`` file is special, because it defines the values that usually change
on each server. For example, the database credentials on your local development
machine might be different from your workmates. The ``.env`` file should contain
sensible, non-secret *default* values for all of your environment variables and
*should* be commited to your repository.

To override these variables with machine-specific or sensitive values, create a
``.env.local`` file. This file is **not committed to the shared repository** and
is only stored on your machine. In fact, the ``.gitignore`` file that comes with
Symfony prevents it from being committed.

You can also create a few other ``.env`` files that will be loaded:

* ``.env.{environment}``: e.g. ``.env.test`` will be loaded in the ``test`` environment
  and committed to your repository.

* ``.env.{environment}.local``: e.g. ``.env.prod.local`` will be loaded in the
  ``prod`` environment but will *not* be committed to your repository.

If you decide to set real environment variables on production, the ``.env`` files
*are* still loaded, but your real environment variables will override those values.

Environments & the Other Config Files
-------------------------------------

You have just *one* app, but whether you realize it or not, you need it to
behave *differently* at different times:

* While **developing**, you want your app to log everything and expose nice
  debugging tools;

* After deploying to **production**, you want that *same* app to be optimized
  for speed and only log errors.

How can you make *one* application behave in two different ways? With
*environments*.

You've probably already been using the ``dev`` environment without even knowing
it. After you deploy, you'll use the ``prod`` environment.

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
