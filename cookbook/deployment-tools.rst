.. index::
   single: Deployment Tools

How to deploy a Symfony2 application
====================================

There are several ways you can deploy a Symfony2 application.

Let's start with a few basic examples of where developers typically begin with their
deployment strategies, and build on things from there.

* The most basic way of deploying an application is copying the files manually via ftp
  (or similar method). This is one of the worst methods because of the complete lack of
  control you have over the system as the upgrade progresses.

* When using a source code versioning tool (such as git or subversion), you can
  simplify this by instead having your live installation also be a copy of your repository,
  so when you're ready to upgrade it is as simple as fetching the latest updates from
  your source control system. This way still causes problems when you have to incorporate
  database migrations, however. It does improve upon the prior method, however, as only
  the files that were changed would need to be updated, and that means your application
  will be in an unstable state for far less time.

* There are tools to help ease the pains of deployment, and their use is becoming more widespread.
  Now we even have a few tools which have been specifically tailored to the requirements of
  Symfony2, that take special care to ensure that everything before, during, and after a deployment
  has gone correctly, and is able to halt (and roll back, if necessary) the process should an error
  occur.

Deployment of an application require care. The use of staging, testing, QA,
continuous integration, database migrations and capability to roll back in case of failure
are all strongly advised. There are simple and more complex tools and one can make
the deployment as easy (or sophisticated) as your environment requires.

Don't forget that deploying your application also involves updating any dependency (via
composer), migrating your database, and clearing your cache. You may even need permission
management, and the ability to add, edit, or remove cron jobs.

Next, we will take a look at some of the more popular options.

The Tools
---------

`Capifony`_:

    This tool provides a specialized set of tools on top of Capistrano, tailored specifically to symfony and Symfony2 projects.

`Magallanes`_:

    This Capistrano-like deployment tool is built in PHP, and may be easier for PHP developers to extend for their needs.

`sf2debpkg`_:

    This tool helps you build a native Debian package for your Symfony2 project.

Bundles:

    There are many `bundles that add deployment features`_ directly into your Symfony2 console.

Basic scripting:

    You can of course use shell, `Ant`_, or any other build tool to script the deploying of your project.

Platform as a Service Providers:

    PaaS is a relatively new way to deploy your application. Typically a PaaS will use a single configuration file
    in your project's root directory to detrmine how to build an environment on the fly that supports your software.
    One provider with confirmed Symfony2 support is `PagodaBox`_.


.. tip::

    Looking for more? Talk to the community on the `Symfony IRC channel`_ #symfony (on freenode) for more information.

.. _`Capifony`: https://capifony.org/
.. _`sf2debpkg`: https://github.com/liip/sf2debpkg
.. _`Ant`: http://blog.sznapka.pl/deploying-symfony2-applications-with-ant
.. _`PagodaBox`: https://github.com/jmather/pagoda-symfony-sonata-distribution/blob/master/Boxfile
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`bundles that add deployment features`: http://knpbundles.com/search?q=deploy
.. _`Symfony IRC channel`: http://webchat.freenode.net/?channels=symfony