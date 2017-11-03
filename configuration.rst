.. index::
   single: Configuration

Configuring Symfony (and Environments)
======================================

Every Symfony application consists of a collection of bundles that add useful tools
(:doc:`services </service_container>`) to your project. Each bundle can be customized
via configuration files that live - by default - in the ``config/`` directory.

Configuration: config/packages/
-------------------------------

The configuration for each package can be found in ``config/packages/``. For
instance, the framework package is configured in ``config/packages/framework.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            secret: '%env(APP_SECRET)%'
            #default_locale: en
            #csrf_protection: ~
            #http_method_override: true
            #trusted_hosts: ~
            # https://symfony.com/doc/current/reference/configuration/framework.html#handler-id
            #session:
            #    # The native PHP session handler will be used
            #    handler_id: ~
            #esi: ~
            #fragments: ~
            php_errors:
                log: true

    .. code-block:: xml

            <!-- config/packages/framework.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:framework="http://symfony.com/schema/dic/framework"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/framework http://symfony.com/schema/dic/framework/framework-1.0.xsd"
            >
                <framework:config secret="%env(APP_SECRET)%">
                    <framework:php-errors log="true" />
                </framework:config>
            </container>

    .. code-block:: php

        # config/packages/framework.php
        $container->loadFromExtension('framework', [
            'secret' => '%env(APP_SECRET)%',
            //'default_locale' => 'en',
            //'csrf_protection' => null,
            //'http_method_override' => true,
            //'trusted_hosts' => null,
            // https://symfony.com/doc/current/reference/configuration/framework.html#handler-id
            //'session' => [
            //    // The native PHP session handler will be used
            //    'handler_id' => null,
            //],
            //'esi' => null,
            //'fragments' => null,
            'php_errors' => [
                'log' => true,
            ],
        ]);

The top-level key (here ``framework``) references configuration for a specific
bundle (``FrameworkBundle`` in this case).

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

For example, if you want to configure something in Framework, you can see an example
dump of all available configuration options by running:

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
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

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
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- any string surrounded by two % is replaced by that parameter value -->
            <framework:config default-locale="%locale%">
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/translation.php
        $container->loadFromExtension('framework', array(
            // any string surrounded by two % is replaced by that parameter value
            'default_locale' => '%locale%',

            // ...
        ));

You can define whatever parameter names you want under the ``parameters`` key of
any configuration file. To reference a parameter, surround its name with two percent
signs - e.g. ``%locale%``.

.. seealso::

    You can also set parameters dynamically, like from environment variables.
    See :doc:`/configuration/external_parameters`.

For more information about parameters - including how to reference them from inside
a controller - see :ref:`service-container-parameters`.

.. _config-dot-env:
.. _config-parameters-yml:

The .env File
~~~~~~~~~~~~~

There is also a ``.env`` file which is loaded. Its contents become environment variables
in the dev environment, making it easier to reference environment variables in your
code.

The ``.env`` file is special, because it defines the values that usually change
on each server. For example, the database credentials on your local development
machine might be different from your workmates. That's why this file is **not
committed to the shared repository** and is only stored on your machine. In
fact, the ``.gitignore`` file that comes with Symfony prevents it from being
committed.

However, a ``.env.dist`` file *is* committed (with dummy values). This file
isn't read by Symfony: it's just a reference so that Symfony knows which
variables need to be defined in the ``.env`` file. If you add or remove keys to
``.env``, add or remove them from ``.env.dist`` too, so both files are always
in sync.

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
