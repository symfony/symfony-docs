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

    This tool is a deployment recipe on top of Capistrano for Symfony2 project

`Magallanes`_:

    This tool is probably the one top php deployment tool Capistrano-like for deploying any kind of php project

`sf2debpkg`_:

    This tool helps you build a native Debian package for your symfony project

Bundles:

    There are `listings`_ of bundles for deployment you can search and use

Basic scripting:

    You can of course use shell, `Ant`_, or other build tool to script the deploying of your project

Deployment services:

    Some services require a single file in project's git repository like `PagodaBox`_ to handle all deployment


.. tip::

    Consult your symfony community at IRC channel #symfony for more fresh ideas or common problems.

.. _`Capifony`: https://capifony.org/
.. _`sf2debpkg`: https://github.com/liip/sf2debpkg
.. _`Ant`: http://blog.sznapka.pl/deploying-symfony2-applications-with-ant
.. _`PagodaBox`: https://github.com/jmather/pagoda-symfony-sonata-distribution/blob/master/Boxfile
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`listings`: http://knpbundles.com/search?q=deploy