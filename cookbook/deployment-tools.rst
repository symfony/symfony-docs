.. index::
   single: Deployment

How to deploy a Symfony2 application
====================================

.. note::

    Deploying can be a complex and varied task depending on your setup and needs.
    This entry doesn't try to explain everything, but rather offers the most
    common requirements and ideas for deployment.

Symfony2 Deployment Basics
--------------------------

The typical steps taken while deploying a Symfony2 application include:

#. Upload your modified code to the live server;
#. Update your vendor dependencies (typically done via Composer, and may
   be done before uploading);
#. Running database migrations or similar tasks to update any changed data structures;
#. Clearing (and perhaps more importantly, warming up) your cache.

A deployment may also include other things, such as:

* Tagging a particular version of your code as a release in your source control repository;
* Creating a temporary staging area to build your updated setup "offline";
* Running any tests available to ensure code and/or server stability;
* Removal of any unnecessary files from ``web`` to keep your production environment clean;
* Clearing of external cache systems (like `Memcached`_ or `Redis`_).

How to deploy a Symfony2 application
------------------------------------

There are several ways you can deploy a Symfony2 application.

Start with a few basic deployment strategies and build up from there.

Basic File Transfer
~~~~~~~~~~~~~~~~~~~

The most basic way of deploying an application is copying the files manually
via ftp/scp (or similar method). This has its disadvantages as you lack control
over the system as the upgrade progresses. This method also requires you
to take some manual steps after transferring the files (see `Common Post-Deployment Tasks`_)

Using Source Control
~~~~~~~~~~~~~~~~~~~~

If you're using source control (e.g. Git or SVN), you can simplify by having
your live installation also be a copy of your repository. When you're ready
to upgrade it is as simple as fetching the latest updates from your source
control system.

This makes updating your files *easier*, but you still need to worry about
manually taking other steps (see `Common Post-Deployment Tasks`_).

Using Build scripts and other Tools
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are also high-quality tools to help ease the pain of deployment. There
are even a few tools which have been specifically tailored to the requirements of
Symfony2, and which take special care to ensure that everything before, during,
and after a deployment has gone correctly.

See `The Tools`_ for a list of tools that can help with deployment.

Common Post-Deployment Tasks
----------------------------

After deploying your actual source code, there are a number of common things
you'll need to do:

A) Configure your ``app/config/parameters.yml`` file
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This file should be customized on each system. The method you use to
deploy your source code should *not* deploy this file. Instead, you should
set it up manually (or via some build process) on your server(s).

B) Update your vendors
~~~~~~~~~~~~~~~~~~~~~~

Your vendors can be updated before transferring your source code (i.e.
update the ``vendor/`` directory, then transfer that with your source
code) or afterwards on the server. Either way, just update your vendors
as you normally do:

.. code-block:: bash

    $ php composer.phar install --no-dev --optimize-autoloader

.. tip::

    The ``--optimize-autoloader`` flag makes Composer's autoloader more
    performant by building a "class map". The ``--no-dev`` flag
    ensures that development packages are not installed in the production
    environment.

C) Clear your Symfony cache
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Make sure you clear (and warm-up) your Symfony cache:

.. code-block:: bash

    $ php app/console cache:clear --env=prod --no-debug

D) Dump your Assetic assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using Assetic, you'll also want to dump your assets:

.. code-block:: bash

    $ php app/console assetic:dump --env=prod --no-debug

E) Other things!
~~~~~~~~~~~~~~~~

There may be lots of other things that you need to do, depending on your
setup:

* Running any database migrations
* Clearing your APC cache
* Running ``assets:install`` (taken care of already in ``composer.phar install``)
* Add/edit CRON jobs
* Pushing assets to a CDN
* ...

Application Lifecycle: Continuous Integration, QA, etc
------------------------------------------------------

While this entry covers the technical details of deploying, the full lifecycle
of taking code from development up to production may have a lot more steps
(think deploying to staging, QA, running tests, etc).

The use of staging, testing, QA, continuous integration, database migrations
and the capability to roll back in case of failure are all strongly advised. There
are simple and more complex tools and one can make the deployment as easy
(or sophisticated) as your environment requires.

Don't forget that deploying your application also involves updating any dependency
(typically via Composer), migrating your database, clearing your cache and
other potential things like pushing assets to a CDN (see `Common Post-Deployment Tasks`_).

The Tools
---------

`Capifony`_:

    This tool provides a specialized set of tools on top of Capistrano, tailored
    specifically to symfony and Symfony2 projects.

`sf2debpkg`_:

    This tool helps you build a native Debian package for your Symfony2 project.

`Magallanes`_:

    This Capistrano-like deployment tool is built in PHP, and may be easier
    for PHP developers to extend for their needs.

Bundles:

    There are many `bundles that add deployment features`_ directly into your
    Symfony2 console.

Basic scripting:

    You can of course use shell, `Ant`_, or any other build tool to script
    the deploying of your project.

Platform as a Service Providers:

    PaaS is a relatively new way to deploy your application. Typically a PaaS
    will use a single configuration file in your project's root directory to
    determine how to build an environment on the fly that supports your software.
    One provider with confirmed Symfony2 support is `PagodaBox`_.

.. tip::

    Looking for more? Talk to the community on the `Symfony IRC channel`_ #symfony
    (on freenode) for more information.

.. _`Capifony`: http://capifony.org/
.. _`sf2debpkg`: https://github.com/liip/sf2debpkg
.. _`Ant`: http://blog.sznapka.pl/deploying-symfony2-applications-with-ant
.. _`PagodaBox`: https://github.com/jmather/pagoda-symfony-sonata-distribution/blob/master/Boxfile
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`bundles that add deployment features`: http://knpbundles.com/search?q=deploy
.. _`Symfony IRC channel`: http://webchat.freenode.net/?channels=symfony
.. _`Memcached`: http://memcached.org/
.. _`Redis`: http://redis.io/
