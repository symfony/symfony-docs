How to Create and store a Symfony2 Project in git
=================================================

.. tip::

    Though this entry is specifically about git, the same generic principles
    will apply if you're storing your project in Subversion.

Once you've read through :doc:`/book/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. In this
cookbook article, you'll learn the best way to start a new Symfony2 project
that's stored using the `git`_ source control management system.

Initial Project Setup
---------------------

To get started, you'll need to download Symfony and initialize your local
git repository:

1. Download the `Symfony2 Standard Edition`_ without vendors.

2. Unzip/untar the distribution. It will create a folder called Symfony with
   your new project structure, config files, etc. Rename it to whatever you like.

3. Create a new file called ``.gitignore`` at the root of your new project
   (e.g. next to the ``deps`` file) and paste the following into it. Files
   matching these patterns will be ignored by git:

    .. code-block:: text

        /web/bundles/
        /app/bootstrap*
        /app/cache/*
        /app/logs/*
        /vendor/  
        /app/config/parameters.ini

4. Copy ``app/config/parameters.ini`` to ``app/config/parameters.ini.dist``.
   The ``parameters.ini`` file is ignored by git (see above) so that machine-specific
   settings like database passwords aren't committed. By creating the ``parameters.ini.dist``
   file, new developers can quickly clone the project, copy this file to
   ``parameters.ini``, customize it, and start developing.

5. Initialize your git repository:

    .. code-block:: bash
    
        $ git init

6. Add all of the initial files to git:

    .. code-block:: bash
    
        $ git add .

7. Create an initial commit with your started project:

    .. code-block:: bash
    
        $ git commit -m "Initial commit"

8. Finally, download all of the third-party vendor libraries:

    .. code-block:: bash
    
        $ php bin/vendors install

At this point, you have a fully-functional Symfony2 project that's correctly
committed to git. You can immediately begin development, committing the new
changes to your git repository.

You can continue to follow along with the :doc:`/book/page_creation` chapter
to learn more about how to configure and develop inside your application.

.. tip::

    The Symfony2 Standard Edition comes with some example functionality. To
    remove the sample code, follow the instructions on the `Standard Edition Readme`_.

Managing Vendor Libraries
-------------------------

Every Symfony project uses a large group of third-party "vendor" libraries.

By default, these libraries are downloaded by running the ``php bin/vendors install``
script. This script reads from the ``deps`` file, and downloads the given
libraries into the ``vendor/`` directory. It also reads ``deps.lock`` file,
pinning each library listed there to the exact git commit hash.

In this setup, the vendors libraries aren't part of your git repository,
not even as submodules. Instead, we rely on the ``deps`` and ``deps.lock``
files and the ``bin/vendors`` script to manage everything. Those files are
part of your repository, so the necessary versions of each third-party library
are version-controlled in git, and you can use the vendors script to bring
your project up to date.

Whenever a developer clones a project, he/she should run the ``php bin/vendors install``
script to ensure that all of the needed vendor libraries are downloaded.

.. sidebar:: Upgrading Symfony

    Since Symfony is just a group of third-party libraries and third-party
    libraries are entirely controlled through ``deps`` and ``deps.lock``,
    upgrading Symfony means simply upgrading each of these files to match
    their state in the latest Symfony Standard Edition.

    Of course, if you've added new entries to ``deps`` or ``deps.lock``, be sure
    to replace only the original parts (i.e. be sure not to also delete any of
    your custom entries).

.. caution::

    There is also a ``php bin/vendors upgrade`` command, but this has nothing
    to do with upgrading your project and you will likely never need to use
    it.

Vendors and Submodules
~~~~~~~~~~~~~~~~~~~~~~

Instead of using the ``deps``, ``bin/vendors`` system for managing your vendor
libraries, you may instead choose to use native `git submodules`_. There
is nothing wrong with this approach, though the ``deps`` system is the official
way to solve this problem and git submodules can be difficult to work with
at times.

Storing your Project on a Remote Server
---------------------------------------

You now have a fully-functional Symfony2 project stored in git. However,
in most cases, you'll also want to store your project on a remote server
both for backup purposes, and so that other developers can collaborate on
the project.

The easiest way to store your project on a remote server is via `GitHub`_.
Public repositories are free, however you will need to pay a monthly fee
to host private repositories.

Alternatively, you can store your git repository on any server by creating
a `barebones repository`_ and then pushing to it. One library that helps
manage this is `Gitosis`_.

.. _`git`: http://git-scm.com/
.. _`Symfony2 Standard Edition`: http://symfony.com/download
.. _`Standard Edition Readme`: https://github.com/symfony/symfony-standard/blob/master/README.md
.. _`git submodules`: http://book.git-scm.com/5_submodules.html
.. _`GitHub`: https://github.com/
.. _`barebones repository`: http://progit.org/book/ch4-4.html
.. _`Gitosis`: https://github.com/res0nat0r/gitosis