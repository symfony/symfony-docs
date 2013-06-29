.. index::
   single: Workflow; Git

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
   (e.g. next to the ``composer.json`` file) and paste the following into it. Files
   matching these patterns will be ignored by git:

   .. code-block:: text

        /web/bundles/
        /app/bootstrap*
        /app/cache/*
        /app/logs/*
        /vendor/
        /app/config/parameters.yml

.. tip::

   You may also want to create a .gitignore file that can be used system-wide,
   in which case, you can find more information here: `Github .gitignore`_
   This way you can exclude files/folders often used by your IDE for all of your projects.

4. Copy ``app/config/parameters.yml`` to ``app/config/parameters.yml.dist``.
   The ``parameters.yml`` file is ignored by git (see above) so that machine-specific
   settings like database passwords aren't committed. By creating the ``parameters.yml.dist``
   file, new developers can quickly clone the project, copy this file to
   ``parameters.yml``, customize it, and start developing.

5. Initialize your git repository:

   .. code-block:: bash

        $ git init

6. Add all of the initial files to git:

   .. code-block:: bash

        $ git add .

7. Create an initial commit with your started project:

   .. code-block:: bash

        $ git commit -m "Initial commit"

8. Finally, download all of the third-party vendor libraries by
   executing composer. For details, see :ref:`installation-updating-vendors`.

At this point, you have a fully-functional Symfony2 project that's correctly
committed to git. You can immediately begin development, committing the new
changes to your git repository.

You can continue to follow along with the :doc:`/book/page_creation` chapter
to learn more about how to configure and develop inside your application.

.. tip::

    The Symfony2 Standard Edition comes with some example functionality. To
    remove the sample code, follow the instructions in the
    ":doc:`/cookbook/bundles/remove`" article.

.. _cookbook-managing-vendor-libraries:

.. include:: _vendor_deps.rst.inc

Vendors and Submodules
~~~~~~~~~~~~~~~~~~~~~~

Instead of using the ``composer.json`` system for managing your vendor
libraries, you may instead choose to use native `git submodules`_. There
is nothing wrong with this approach, though the ``composer.json`` system
is the official way to solve this problem and probably much easier to
deal with. Unlike git submodules, ``Composer`` is smart enough to calculate
which libraries depend on which other libraries.

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
manage this is `Gitolite`_.

.. _`git`: http://git-scm.com/
.. _`Symfony2 Standard Edition`: http://symfony.com/download
.. _`git submodules`: http://git-scm.com/book/en/Git-Tools-Submodules
.. _`GitHub`: https://github.com/
.. _`barebones repository`: http://git-scm.com/book/en/Git-Basics-Getting-a-Git-Repository
.. _`Gitolite`: https://github.com/sitaramc/gitolite
.. _`Github .gitignore`: https://help.github.com/articles/ignoring-files
