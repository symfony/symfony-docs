.. index::
   single: Workflow; Distribution

How to Prepare and Customize Symfony2 Distribution using git
============================================================

Once you've become familiar with basics, you'll probably start
to use it for your new projects. Quickly you will realize, that
with every new project you will have to repeat the same procedures
to configure the framework. Here you will find explanation how you can
prepare your own distribution, tailored to your specific needs and ready
to be used for your next project.

.. caution::

   The procedure below assumes that you are familiar with
   `git`_, `Github`_ and branching.

New branch
----------

To get started, you'll need your fork of `Symfony2 Standard Edition`_.
Clone the fork on your local drive and create a new branch that
corresponds to an arbitrary Symfony 2 version.

1. Fork the `Symfony2 Standard Edition`_ repository.

2. Clone the fork on your local drive.

3. Assuming that you want to use Symfony 2.1.4 as the basis for your projects
   execute following command:

   .. code-block:: bash

        $ git checkout -b 2.1-startup v2.1.4

   The new branch will be called ``2.1-startup``.

.. caution::

   The procedure begins with Symfony 2.1.4, which is not the latest
   official 2.1.x version, intentionally. Later on, you will learn
   how to upgrade your distribution, to the latest Symfony 2.1 version.
   Similar procedure can be used for other versions, e.g. Symfony 2.0
   or Symfony 2.2.

4. Install vendor libraries:

   .. code-block:: bash

       $ php composer.phar install

Customization
-------------

Switch to the new branch and customize it. Prepare following commits:

1. Commit #1: remove Acme example.

2. Commit #2: add `DoctrineFixturesBundle`_:

   Add the following to your ``composer.json`` file:

   .. code-block:: json

       "require": {
           ...
           "doctrine/data-fixtures": "dev-master",
           "doctrine/doctrine-fixtures-bundle": "2.1.*@dev"
       }

   Then update the vendor libraries:

   .. code-block:: bash

       $ php composer.phar update doctrine/doctrine-fixtures-bundle

   Your commit should contain changed versions of ``composer.json``
   and ``composer.lock``.

.. tip::

   Surely, you can use the above procedure to add any bundles to
   you distribution. `DoctrineFixturesBundle`_ is just an example.

Publish your branch
-------------------

Publish your branch on Github. You can use following commands:

   .. code-block:: bash

       $ git remote add startup git@github.com:joedoe/symfony-standard.git
       $ git push startup 2.1-startup

Update your distribution
------------------------

After some time, you will notice, that new versions of Symfony were published.
To update your distribution follow the procedure:

1. Switch to your local ``master`` branch and update it:

   .. code-block:: bash

       $ git checkout master
       $ git pull

2. Switch to your ``2.1-startup`` branch:

   .. code-block:: bash

       $ git checkout 2.1-startup

3. Merged new changes from the ``master`` branch into the
   ``2.1-startup`` branch. Use tags, e.g. ``v2.1.5``:

   .. code-block:: bash

       $ git merge v2.1.5

   You will get some conflicts. Resolve them and commit the changes.

.. tip::

   If you want to practice the above procedure, start with
   version v2.1.0 and update it to v2.1.1. Next, repeat
   the same procedure to update the distribution to version v2.1.2.
   Then, once again, update to version v2.1.3, etc.

How to use a newly created distribution?
----------------------------------------

When your distribution is finished it's time to try it:

1. Clone your ``2.1-startup`` branch:

   .. code-block:: bash

       $ git clone -b 2.1-startup git@github.com:joedoe/symfony-standard.git

   The clone will be stored in a folder named ``symfony-standard``.
   To avoid confusion rename the folder to ``project-alpha``.

2. Create local ``master`` branch in your ``project-alpha``:

   .. code-block:: bash

       $ git checkout -b master

3. Install vendor libraries:

   .. code-block:: bash

       $ php composer.phar install

4. Now, you have a starting point for your next project. And you
   don't have to repeat the steps that were included in ``2.1-startup branch``.
   Depending on your choice it can be a simple Acme removal or
   the installation of ``DoctrineFixturesBundle``,
   ``DoctrineMigrationsBundle``, ``FOSUserBundle``,``KnpMenuBundle``,
   ``SonataAdmin`` bundles, ``Behat``, and many others.
   The choice is up to you!

Updating the project
--------------------

Right now we have four important repositories:

*original `Symfony2 Standard Edition`_,
*your local repository with ``2.1-startup`` branch,
*your fork stored on Github,
*and ``project-alpha``.

Original `Symfony2 Standard Edition`_ can be updated by core Symfony2 team.
When it happens, you can update your local ``2.1-startup`` branch
using procedure described in *Update your distribution*.

In order to update your ``project-alpha`` with changes made by core
Symfony2 team follow the steps:

1. Update your local ``2.1-startup`` branch (procedure
   described in *Update your distribution*).

2. Push the updated branch to your fork:

   .. code-block:: bash

       $ git push startup 2.1-startup

3. Pull the updated ``2.1-startup`` branch into the ``project-alpha``.
   The commands to be executed in the ``project-alpha`` folder:

   .. code-block:: bash

       $ git checkout 2.1-startup
       $ git pull origin 2.1-startup
       $ git checkout master
       $ git merge 2.1-startup

   Remove the conflicts and commit the changes.

.. caution::

   If you want to avoid potential problems firstly test the above procedure
   in a temporary branch.

How to create a zipped distribution?
------------------------------------

Symfony2 homepage offers distributions stored in a compressed archive.
To create such a distribution basing on your ``2.1-startup`` branch
follow the steps:

1. Update your local ``2.1-startup`` branch.

2. Update vendor libraries:

   .. code-block:: bash

       $ php composer.phar install

3. Clear the cache.

4. Remove the history of vendor libraries:

   .. code-block:: bash

       $ find vendor -name .git -type d | xargs rm -rf

5. Copy the complete repository to a new location and delete its history.
   Simply: delete ``.git`` folder.

6. Compress the folder.

.. _`Symfony2 Standard Edition`: https://github.com/symfony/symfony-standard
.. _`git`: http://git-scm.com/
.. _`GitHub`: https://github.com/
.. _`DoctrineFixturesBundle`: https://github.com/doctrine/DoctrineFixturesBundle
