.. index::
   single: Workflow; Git

How to Create and store a Symfony2 Project in Git
=================================================

.. tip::

    Though this entry is specifically about Git, the same generic principles
    will apply if you're storing your project in Subversion.

Once you've read through :doc:`/book/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. In this
cookbook article, you'll learn the best way to start a new Symfony2 project
that's stored using the `Git`_ source control management system.

Initial Project Setup
---------------------

To get started, you'll need to download Symfony and initialize your local
git repository:

#. Download the `Symfony2 Standard Edition`_ using Composer:

   .. code-block:: bash

        $ php composer.phar create-project symfony/framework-standard-edition path/ ~2.3

   Composer will now download the Standard Distribution along with all of the
   required vendor libraries. For more information about downloading Symfony using
   Composer, see `Installing Symfony using Composer`_.

#. Initialize your Git repository:

   .. code-block:: bash

        $ git init

#. Add all of the initial files to Git:

   .. code-block:: bash

        $ git add .

   .. tip::

      As you might have noticed, not all files that were downloaded by Composer in step 1,
      have been staged for commit by Git. Certain files and folders, such as the project's
      dependencies (which are managed by Composer), ``parameters.yml`` (which contains sensitive
      information such as database credentials), log and cache files and dumped assets (which are
      created automatically by your project), should not be committed in Git. To help you prevent
      committing those files and folders by accident, the Standard Distribution comes with a
      file called ``.gitignore``, which contains a list of files and folders that Git should
      ignore.

   .. tip::

      You may also want to create a ``.gitignore`` file that can be used system-wide.
      This allows you to exclude files/folders for all your projects that are created by
      your IDE or operating system. For details, see `Github .gitignore`_.

#. Create an initial commit with your started project:

   .. code-block:: bash

        $ git commit -m "Initial commit"

At this point, you have a fully-functional Symfony2 project that's correctly
committed to Git. You can immediately begin development, committing the new
changes to your Git repository.

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
deal with. Unlike Git submodules, Composer is smart enough to calculate
which libraries depend on which other libraries.

Storing your Project on a Remote Server
---------------------------------------

You now have a fully-functional Symfony2 project stored in Git. However,
in most cases, you'll also want to store your project on a remote server
both for backup purposes, and so that other developers can collaborate on
the project.

The easiest way to store your project on a remote server is via a web-based
hosting service like `GitHub`_ or `Bitbucket`_. Of course, there are more
services out there, you can start your research with a
`comparison of hosting services`_.

Alternatively, you can store your Git repository on any server by creating
a `barebones repository`_ and then pushing to it. One library that helps
manage this is `Gitolite`_.

.. _`Git`: http://git-scm.com/
.. _`Symfony2 Standard Edition`: http://symfony.com/download
.. _`Installing Symfony using Composer`: http://symfony.com/doc/current/book/installation.html#option-1-composer
.. _`git submodules`: http://git-scm.com/book/en/Git-Tools-Submodules
.. _`GitHub`: https://github.com/
.. _`barebones repository`: http://git-scm.com/book/en/Git-Basics-Getting-a-Git-Repository
.. _`Gitolite`: https://github.com/sitaramc/gitolite
.. _`Github .gitignore`: https://help.github.com/articles/ignoring-files
.. _`Bitbucket`: https://bitbucket.org/
.. _`comparison of hosting services`: http://en.wikipedia.org/wiki/Comparison_of_open-source_software_hosting_facilities
