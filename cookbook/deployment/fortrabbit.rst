.. index::
   single: Deployment; Deploying to fortrabbit.com

Deploying to fortrabbit
=======================

This step-by-step cookbook describes how to deploy a Symfony web application to
`fortrabbit`_. You can read more about using Symfony with fortrabbit on the
official fortrabbit `Symfony install guide`_.

Setting up fortrabbit
---------------------

Before getting started, you should have done a few things on the fortrabbit side:

* `Sign up`_;
* Add an SSH key to your Account (to deploy via Git);
* Create an App.

Preparing your Application
--------------------------

You don't need to change any code to deploy a Symfony application to fortrabbit.
But it requires some minor tweaks to its configuration.

Configure Logging
~~~~~~~~~~~~~~~~~

Per default Symfony logs to a file. Modify the ``app/config/config_prod.yml`` file
to redirect it to :phpfunction:`error_log`:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_prod.yml
        monolog:
            # ...
            handlers:
                nested:
                    type: error_log

    .. code-block:: xml

        <!-- app/config/config_prod.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <!-- ... -->
                <monolog:handler name="nested" type="error_log" />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config_prod.php
        $container->loadFromExtension('monolog', array(
            // ...
            'handlers' => array(
                'nested' => array(
                    'type' => 'error_log',
                ),
            ),
        ));

Configuring Database Access & Session Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the fortrabbit App Secrets to attain your database credentials.
Create the file ``app/config/config_prod_secrets.php`` with the following
contents::

    // get the path to the secrects.json file
    $secrets = getenv("APP_SECRETS")
    if (!$secrets) {
        return;
    }

    // read the file and decode json to an array
    $secrets = json_decode(file_get_contents($secrets), true);

    // set database parameters to the container
    if (isset($secrets['MYSQL'])) {
        $container->setParameter('database_driver', 'pdo_mysql');
        $container->setParameter('database_host', $secrets['MYSQL']['HOST']);
        $container->setParameter('database_name', $secrets['MYSQL']['DATABASE']);
        $container->setParameter('database_user', $secrets['MYSQL']['USER']);
        $container->setParameter('database_password', $secrets['MYSQL']['PASSWORD']);
    }

    // check if the Memcache component is present
    if (isset($secrets['MEMCACHE'])) {
        $memcache = $secrets['MEMCACHE'];
        $handlers = array();

        foreach (range(1, $memcache['COUNT']) as $num) {
            $handlers[] = $memcache['HOST'.$num].':'.$memcache['PORT'.$num];
        }

        // apply ini settings
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', implode(',', $handlers));

        if ("2" === $memcache['COUNT']) {
            ini_set('memcached.sess_number_of_replicas', 1);
            ini_set('memcached.sess_consistent_hash', 1);
            ini_set('memcached.sess_binary', 1);
        }
    }

Make sure this file is imported into the main config file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_prod.yml
            imports:
                - { resource: config.yml }
                - { resource: config_prod_secrets.php }

            # ..
            framework:
                session:
                    # set handler_id to null to use default session handler from php.ini (memcached)
                    handler_id:  ~
            # ..

    .. code-block:: xml

        <!-- app/config/config_prod.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="config.xml" />
                <import resource="config_prod_secrets.php" />
            </imports>

            <!-- .. -->
            <framework:config>
                <!-- .. -->
                <framework:session save_path="null" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config_prod.php
        $loader->import('config/config.php');
        $loader->import('config_prod_secrets.php');

        $container->loadFromExtension('framework', array(
            'session' => array(
                'handler_id' => null,
            ),
        ));

        // ...

Configuring the Environment in the Dashboard
--------------------------------------------

PHP Settings
~~~~~~~~~~~~

The PHP version and enabled extensions are configuable under the PHP settings
of your App within the fortrabbit Dashboard.

Environment Variables
~~~~~~~~~~~~~~~~~~~~~

Set the ``SYMFONY_ENV`` environment variable to ``prod`` to make sure the right
config files get loaded. ENV vars are configuable in fortrabbit Dashboard as well.

Document Root
~~~~~~~~~~~~~

The document root is configuable for every custom domain you setup for your App.
The default is ``/htdocs``, but for Symfony you probably want to change it to
``/htdocs/web``. You also do so in the fortrabbit Dashboard under ``Domain`` settings.

Deploying to fortrabbit
-----------------------

It is assumed that your codebase is under version-control with Git and dependencies
are managed with Composer (locally).

Every time you push to fortrabbit composer install runs before your code gets
deployed. To finetune the deployment behavior put a `fortrabbit.yml`_. deployment
file (optional) in the project root.

Add fortrabbit as a (additional) Git remote and add your configuration changes:

.. code-block:: bash

   $ git remote add fortrabbit git@deploy.eu2.frbit.com:<your-app>.git
   $ git add composer.json composer.lock
   $ git add app/config/config_prod_secrets.php

Commit and push

.. code-block:: bash

   $ git commit -m 'fortrabbit config'
   $ git push fortrabbit master -u

.. note::

    Replace ``<your-app>`` with the name of your fortrabbit App.

.. code-block:: bash

   Commit received, starting build of branch master

   –––––––––––––––––––––––  ∙ƒ  –––––––––––––––––––––––

   B U I L D

   Checksum:
      def1bb29911a62de26b1ddac6ef97fc76a5c647b

   Deployment file:
      fortrabbit.yml

   Pre-script:
      not found
      0ms

   Composer:
   - - -
   Loading composer repositories with package information
   Installing dependencies (including require-dev) from lock file
   Nothing to install or update
   Generating autoload files

   - - -
   172ms

   Post-script:
      not found
      0ms

   R E L E A S E

   Packaging:
      930ms

   Revision:
      1455788127289043421.def1bb29911a62de26b1ddac6ef97fc76a5c647b

   Size:
      9.7MB

   Uploading:
      500ms

   Build & release done in 1625ms, now queued for final distribution.


.. note::

   The first ``git push`` takes much longer as all composer dependencies get
   downloaded. All subsequent deploys are done within seconds.

That's it! Your application is being deployed on fortrabbit. More information
about `database migrations and tunneling`_ can be found in the fortrabbit
documentation.

.. _`fortrabbit`: https://www.fortrabbit.com
.. _`Symfony install guide`: https://help.fortrabbit.com/install-symfony
.. _`fortrabbit.yml`: https://help.fortrabbit.com/deployment-file-v2
.. _`database migrations and tunneling`: https://help.fortrabbit.com/install-symfony-2#toc-migrate-amp-other-database-commands
.. _`Sign up`: https://dashboard.fortrabbit.com
