.. index::
   single: Deployment; Deploying to Platform.sh

Deploying to Platform.sh
========================

This step by step cookbook describes how to deploy a Symfony web application to 
`Platform.sh`_ . You can read more about using Symfony with Platform.sh on the 
official `Platform.sh documentation`_.

Deploy an existing site
-----------------------

In this guide, we assume your codebase is already versioned with Git.

Get a project on Platform.sh
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 
You need to subscribe to a `Platform.sh project`_. Choose the development plan
and go through the checkout process.

Once your project is ready, give it a name and choose: **Import an existing
site**.

Prepare your Application
~~~~~~~~~~~~~~~~~~~~~~~~

To deploy your Symfony application on Platform.sh, you simply need to add a 
``.platform.app.yaml`` at the root of your Git repository which will tell
Platform.sh how to deploy your application (read more about `Platform.sh 
configuration files`_).

.. code-block:: yaml

    # This file describes an application. You can have multiple applications
    # in the same project.

    # The name of this app. Must be unique within a project.
    name: php

    # The toolstack used to build the application.
    toolstack: "php:symfony"

    # The relationships of the application with services or other applications.
    # The left-hand side is the name of the relationship as it will be exposed
    # to the application in the PLATFORM_RELATIONSHIPS variable. The right-hand
    # side is in the form `<service name>:<endpoint name>`.
    relationships:
        database: "mysql:mysql"

    # The configuration of app when it is exposed to the web.
    web:
        # The public directory of the app, relative to its root.
        document_root: "/web"
        # The front-controller script to send non-static requests to.
        passthru: "/app.php"

    # The size of the persistent disk of the application (in MB).
    disk: 2048

    # The mounts that will be performed when the package is deployed.
    mounts:
        "/app/cache": "shared:files/cache"
        "/app/logs": "shared:files/logs"

    # The hooks that will be performed when the package is deployed.
    hooks:
        build: |
          rm web/app_dev.php
          app/console --env=prod assetic:dump --no-debug
        deploy: |
          app/console --env=prod cache:clear

    # The configuration of scheduled execution.
    # see http://symfony.com/doc/current/components/console/introduction.html
    #crons:
    #    symfony:
    #        spec: "*/20 * * * *"
    #        cmd: "php cron.php example:test"

For best practices, you should also add a ``.platform`` folder at the root of
your Git repository which contains the following files:

.. code-block:: yaml

    # .platform/routes.yaml
    "http://{default}/":
        type: upstream
        upstream: "php:php"

.. code-block:: yaml

    # .platform/services.yaml
    mysql:
        type: mysql
        disk: 2048

Configure database access
~~~~~~~~~~~~~~~~~~~~~~~~~

Platform.sh overrides your database specific configuration via importing the
following file:

.. code-block:: yaml
    
    # app/config/parameters_platform.php
    <?php
    $relationships = getenv("PLATFORM_RELATIONSHIPS");
    if (!$relationships) {
      return;
    }

    $relationships = json_decode(base64_decode($relationships), TRUE);

    foreach ($relationships['database'] as $endpoint) {
      if (empty($endpoint['query']['is_master'])) {
        continue;
      }

      $container->setParameter('database_driver', 'pdo_' . $endpoint['scheme']);
      $container->setParameter('database_host', $endpoint['host']);
      $container->setParameter('database_port', $endpoint['port']);
      $container->setParameter('database_name', $endpoint['path']);
      $container->setParameter('database_user', $endpoint['username']);
      $container->setParameter('database_password', $endpoint['password']);
      $container->setParameter('database_path', '');
    }

    # Hack.
    ini_set('session.save_path', '/tmp/sessions');

Make sure this file is listed in your *imports*:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: parameters_platform.php }

Deploy your Application
~~~~~~~~~~~~~~~~~~~~~~~

Now you need to add a remote to Platform.sh in your Git repository (copy the 
command that you see on the Platform.sh web UI):

.. code-block:: bash

    $ git remote add platform kjh43kbobssae@git.eu.platform.sh:kjh43kbobssae.git

Commit the Platform.sh specific files created in the previous section:

.. code-block:: bash

    $ git add .platform.app.yaml .platform/* 
    $ git add app/config/config.yml app/config/parameters_platform.php
    $ git commit -m "Adding Platform.sh configuration files."

Push your code base to the newly added remote:

.. code-block:: bash

    $ git push -u platform master

    Counting objects: 27, done.
    Delta compression using up to 4 threads.
    Compressing objects: 100% (11/11), done.
    Writing objects: 100% (16/16), 2.47 KiB | 0 bytes/s, done.
    Total 16 (delta 7), reused 12 (delta 5)

    Processing activity environment.push
      Found 213 new commits.

      Building application 'php' with toolstack 'php:symfony' (tree: 2248cf8)
        Found a `composer.json`, installing dependencies.
    ...

That's it! Your application is being deployed on Platform.sh and you'll soon be
able to access it in your browser.

Deploy a new site
-----------------
 
You can start a new `Platform.sh project`_. Choose the development plan and go 
through the checkout process.

Once your project is ready, give it a name and choose: **Create a new site**.
Choose the *Symfony* stack and a starting point such as *Standard*.

That's it! Your Symfony application will be bootstaped and deployed. You'll soon
be able to see it in your browser.

.. _`Platform.sh`: https://platform.sh
.. _`Platform.sh documentation`: https://docs.platform.sh/toolstacks/symfony/symfony-getting-started/
.. _`Platform.sh project`: https://marketplace.commerceguys.com/platform/buy-now
.. _`Platform.sh configuration files`: https://docs.platform.sh/reference/configuration-files/