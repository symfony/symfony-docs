.. _how-to-deploy-a-symfony2-application:

How to Deploy a Symfony Application
===================================

Deploying a Symfony application can be a complex and varied task depending on
the setup and the requirements of your application. This article is not a
step-by-step guide, but rather a general list of the most common requirements and
ideas for deployment.

.. _symfony2-deployment-basics:

Symfony Deployment Basics
-------------------------

The typical steps taken while deploying a Symfony application include:

#. Upload your code to the production server;
#. Install your vendor dependencies (typically done via Composer and may be done
   before uploading);
#. Running database migrations or similar tasks to update any changed data structures;
#. Clearing (and optionally, warming up) your cache.

A deployment may also include other tasks, such as:

* Tagging a particular version of your code as a release in your source control
  repository;
* Creating a temporary staging area to build your updated setup "offline";
* Running any tests available to ensure code and/or server stability;
* Removal of any unnecessary files from the ``public/`` directory to keep your
  production environment clean;
* Clearing of external cache systems (like `Memcached`_ or `Redis`_).

How to Deploy a Symfony Application
-----------------------------------

There are several ways you can deploy a Symfony application. Start with a few
basic deployment strategies and build up from there.

Basic File Transfer
~~~~~~~~~~~~~~~~~~~

The most basic way of deploying an application is copying the files manually
via FTP/SCP (or similar method). This has its disadvantages as you lack control
over the system as the upgrade progresses. This method also requires you
to take some manual steps after transferring the files (see `Common Deployment Tasks`_).

Using Source Control
~~~~~~~~~~~~~~~~~~~~

If you're using source control (e.g. Git or SVN), you can simplify by having
your live installation also be a copy of your repository. When you're ready to
upgrade, fetch the latest updates from your source control
system. When using Git, a common approach is to create a tag for each release
and check out the appropriate tag on deployment (see `Git Tagging`_).

This makes updating your files *easier*, but you still need to worry about
manually taking other steps (see `Common Deployment Tasks`_).

Using Platforms as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using a Platform as a Service (PaaS) can be a great way to deploy your Symfony
app quickly. There are many PaaS, but we recommend `Upsun`_ as it
provides a dedicated Symfony integration and helps fund the Symfony development.

Using Build Scripts and other Tools
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are also tools to help ease the pain of deployment. Some of them have been
specifically tailored to the requirements of Symfony.

`Deployer`_
    This is another native PHP rewrite of Capistrano, with some ready recipes for
    Symfony.

`Ansistrano`_
    An Ansible role that allows you to configure a powerful deploy via YAML files.

`Magallanes`_
    This Capistrano-like deployment tool is built in PHP, and may be easier
    for PHP developers to extend for their needs.

`Fabric`_
    This Python-based library provides a basic suite of operations for executing
    local or remote shell commands and uploading/downloading files.

`Capistrano`_ with `Symfony plugin`_
    `Capistrano`_ is a remote server automation and deployment tool written in Ruby.
    `Symfony plugin`_ is a plugin to ease Symfony related tasks, inspired by `Capifony`_
    (which works only with Capistrano 2).

.. _common-post-deployment-tasks:

Common Deployment Tasks
-----------------------

Before and after deploying your actual source code, there are a number of common
things you'll need to do:

A) Check Requirements
~~~~~~~~~~~~~~~~~~~~~

There are some :ref:`technical requirements for running Symfony applications <symfony-tech-requirements>`.
In your development machine, the recommended way to check these requirements is
to use `Symfony CLI`_. However, in your production server you might prefer to
not install the Symfony CLI tool. In those cases, install this other package in
your application:

.. code-block:: terminal

    $ composer require symfony/requirements-checker

Then, make sure that the checker is included in your Composer scripts:

.. code-block:: json

    {
        "...": "...",

        "scripts": {
            "auto-scripts": {
                "vendor/bin/requirements-checker": "php-script",
                "...": "..."
            },

            "...": "..."
        }
    }

.. _b-configure-your-app-config-parameters-yml-file:

B) Configure your Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most Symfony applications read their configuration from environment variables.
While developing locally, you'll usually store these in :ref:`.env files <configuration-env-var-in-dev>`.
On production, you have two options:

1. Create "real" environment variables. How you set environment variables, depends
   on your setup: they can be set at the command line, in your Nginx configuration,
   or via other methods provided by your hosting service;

2. Or, create a ``.env.prod.local`` file that contains values specific to your
   production environment.

There is no significant advantage to either option: use whichever is most natural
for your hosting environment.

.. tip::

    You might not want your application to process the ``.env.*`` files on
    every request. You can generate an optimized ``.env.local.php`` which
    overrides all other configuration files:

    .. code-block:: terminal

        $ composer dump-env prod

    The generated file will contain all the configuration stored in ``.env``. If you
    want to rely only on environment variables, generate one without any values using:

    .. code-block:: terminal

        $ composer dump-env prod --empty

    If you don't have Composer installed on the production server, use instead
    :ref:`the dotenv:dump Symfony command <configuration-env-var-in-prod>`.

C) Install/Update your Vendors
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Your vendors can be updated before transferring your source code (i.e.
update the ``vendor/`` directory, then transfer that with your source
code) or afterwards on the server. Either way, update your vendors
as you normally do:

.. code-block:: terminal

    $ composer install --no-dev --optimize-autoloader

.. tip::

    The ``--optimize-autoloader`` flag improves Composer's autoloader performance
    significantly by building a "class map". The ``--no-dev`` flag ensures that
    development packages are not installed in the production environment.

.. warning::

    If you get a "class not found" error during this step, you may need to
    run ``export APP_ENV=prod`` (or ``export SYMFONY_ENV=prod`` if you're not
    using :ref:`Symfony Flex <symfony-flex>`) before running this command so
    that the ``post-install-cmd`` scripts run in the ``prod`` environment.

D) Clear your Symfony Cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Make sure you clear and warm-up your Symfony cache:

.. code-block:: terminal

    $ APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

E) Other Things!
~~~~~~~~~~~~~~~~

There may be lots of other things that you need to do, depending on your
setup:

* Running any database migrations
* Clearing your APCu cache
* Add/edit CRON jobs
* Restarting your workers
* :ref:`Building and minifying your assets <how-do-i-deploy-my-encore-assets>` with Webpack Encore
* :ref:`Compile your assets <asset-mapper-deployment>` if you're using the AssetMapper component
* Pushing assets to a CDN
* On a shared hosting platform using the Apache web server, you may need to
  install the `symfony/apache-pack`_ package
* etc.

Application Lifecycle: Continuous Integration, QA, etc.
-------------------------------------------------------

While this article covers the technical details of deploying, the full lifecycle
of taking code from development up to production may have more steps:
deploying to staging, QA (Quality Assurance), running tests, etc.

The use of staging, testing, QA, continuous integration, database migrations
and the capability to roll back in case of failure are all strongly advised. There
are simple and more complex tools and one can make the deployment as easy
(or sophisticated) as your environment requires.

Don't forget that deploying your application also involves updating any dependency
(typically via Composer), migrating your database, clearing your cache and
other potential things like pushing assets to a CDN (see `Common Deployment Tasks`_).

Troubleshooting
---------------

Deployments not Using the ``composer.json`` File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :ref:`project root directory <configuration-kernel-project-directory>`
(whose value is used via the ``kernel.project_dir`` parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir` method) is
calculated automatically by Symfony as the directory where the main
``composer.json`` file is stored.

In deployments not using the ``composer.json`` file, you'll need to override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir` method
:ref:`as explained in this section <configuration-kernel-project-directory>`.

Learn More
----------

.. toctree::
    :maxdepth: 1

    deployment/proxies

.. _`Capifony`: https://github.com/everzet/capifony
.. _`Capistrano`: https://capistranorb.com/
.. _`Fabric`: https://www.fabfile.org/
.. _`Ansistrano`: https://ansistrano.com/
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`Memcached`: https://memcached.org/
.. _`Redis`: https://redis.io/
.. _`Symfony plugin`: https://github.com/capistrano/symfony/
.. _`Deployer`: https://deployer.org/
.. _`Git Tagging`: https://git-scm.com/book/en/v2/Git-Basics-Tagging
.. _`Upsun`: https://symfony.com/cloud
.. _`Symfony CLI`: https://symfony.com/download
.. _`symfony/apache-pack`: https://packagist.org/packages/symfony/apache-pack
