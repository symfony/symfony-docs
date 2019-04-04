.. index::
   single: Deployment; Deployment tools

.. _how-to-deploy-a-symfony2-application:

How to Deploy a Symfony Application
===================================

Deploying a Symfony application can be a complex and varied task depending on
the setup and the requirements of your application. This article is not a step-
by-step guide, but is a general list of the most common requirements and ideas
for deployment.

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
to take some manual steps after transferring the files (see `Common Post-Deployment Tasks`_)

Using Source Control
~~~~~~~~~~~~~~~~~~~~

If you're using source control (e.g. Git or SVN), you can simplify by having
your live installation also be a copy of your repository. When you're ready to
upgrade, fetch the latest updates from your source control
system. When using Git, a common approach is to create a tag for each release
and check out the appropriate tag on deployment (see `Git Tagging`_).

This makes updating your files *easier*, but you still need to worry about
manually taking other steps (see `Common Post-Deployment Tasks`_).

Using Platforms as a Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Using a Platform as a Service (PaaS) can be a great way to deploy your Symfony app
quickly and easily. There are many PaaS - below are a few that work well with Symfony:

* `Symfony Cloud`_
* `Heroku`_
* `Platform.sh`_
* `Azure`_
* `fortrabbit`_
* `Clever Cloud`_

Using Build Scripts and other Tools
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are also tools to help ease the pain of deployment. Some of them have been
specifically tailored to the requirements of Symfony.

`EasyDeployBundle`_
    A Symfony bundle that adds deploy tools to your application.

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

`sf2debpkg`_
    Helps you build a native Debian package for your Symfony project.

Basic scripting
    You can use a shell script, `Ant`_ or any other build tool to script
    the deploying of your project.

Common Post-Deployment Tasks
----------------------------

After deploying your actual source code, there are a number of common things
you'll need to do:

A) Check Requirements
~~~~~~~~~~~~~~~~~~~~~

Use the :doc:`Symfony Requirements Checker </reference/requirements>` to check
if your server meets the technical requirements to run Symfony applications.

.. _b-configure-your-app-config-parameters-yml-file:

B) Configure your Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most Symfony applications read their configuration from environment variables.
While developing locally, you'll usually store these in ``.env`` and ``.env.local``
(for local overrides). On production, you have two options:

1. Create "real" environment variables. How you set environment variables, depends
   on your setup: they can be set at the command line, in your Nginx configuration,
   or via other methods provided by your hosting service.

2. Or, create a ``.env.local`` file just like your local development (see note below)

There is no significant advantage to either of the two options: use whatever is
most natural in your hosting environment.

.. note::

    If you use the ``.env.*`` files on production, you may need to move your
    ``symfony/dotenv`` dependency from ``require-dev`` to ``require`` in ``composer.json``:

    .. code-block:: terminal

        $ composer require symfony/dotenv

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

.. caution::

    If you get a "class not found" error during this step, you may need to
    run ``export APP_ENV=prod`` (or ``export SYMFONY_ENV=prod`` if you're not
    using :doc:`Symfony Flex </setup/flex>`) before running this command so
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
* Clearing your APC cache
* Add/edit CRON jobs
* :ref:`Building and minifying your assets <how-do-i-deploy-my-encore-assets>` with Webpack Encore
* Pushing assets to a CDN
* ...

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
other potential things like pushing assets to a CDN (see `Common Post-Deployment Tasks`_).

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
.. _`Capistrano`: http://capistranorb.com/
.. _`sf2debpkg`: https://github.com/liip/sf2debpkg
.. _`Fabric`: http://www.fabfile.org/
.. _`Ansistrano`: https://ansistrano.com/
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`Ant`: http://blog.sznapka.pl/deploying-symfony2-applications-with-ant
.. _`Memcached`: http://memcached.org/
.. _`Redis`: http://redis.io/
.. _`Symfony plugin`: https://github.com/capistrano/symfony/
.. _`Deployer`: http://deployer.org/
.. _`Git Tagging`: https://git-scm.com/book/en/v2/Git-Basics-Tagging
.. _`Heroku`: https://devcenter.heroku.com/articles/getting-started-with-symfony
.. _`platform.sh`: https://docs.platform.sh/frameworks/symfony.html
.. _`Azure`: https://azure.microsoft.com/en-us/develop/php/
.. _`fortrabbit`: https://help.fortrabbit.com/install-symfony
.. _`EasyDeployBundle`: https://github.com/EasyCorp/easy-deploy-bundle
.. _`Clever Cloud`: https://www.clever-cloud.com/doc/php/tutorial-symfony/
.. _`Symfony Cloud`: https://symfony.com/doc/master/cloud/intro.html
