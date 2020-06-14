.. index::
   single: Set Up; Git

.. _how-to-create-and-store-a-symfony2-project-in-git:

How to Create and Store a Symfony Project in Git
================================================

.. tip::

    Though this article is specifically about Git, the same generic principles
    will apply if you're storing your project in Subversion.

Once you've read through :doc:`/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. In this
article, you'll learn the best way to start a new Symfony project that's stored
using the `Git`_ source control management system.

Initial Project Setup
---------------------

To get started, you'll need to download Symfony and get things running. See
the :doc:`/setup` article for details.

Symfony has already initialized a git repository and did a first commit.

You can continue to follow along with the :doc:`/page_creation` article
to learn more about how to configure and develop inside your application.

.. include:: _vendor_deps.rst.inc

Storing your Project on a remote Server
---------------------------------------

You now have a fully-functional Symfony project stored in Git. However,
in most cases, you'll also want to store your project on a remote server
both for backup purposes, and so that other developers can collaborate on
the project.

The easiest way to store your project on a remote server is via a web-based
hosting service like `GitHub`_ or `Bitbucket`_. There are more services out
there, you can start your research with a `comparison of hosting services`_.

Alternatively, you can store your Git repository on any server by creating
a `barebones repository`_ and then pushing to it. One library that helps
manage this is `Gitolite`_.

.. _`Git`: https://git-scm.com/
.. _`GitHub`: https://github.com/
.. _`barebones repository`: https://git-scm.com/book/en/Git-Basics-Getting-a-Git-Repository
.. _`Gitolite`: https://github.com/sitaramc/gitolite
.. _`GitHub .gitignore`: https://help.github.com/articles/ignoring-files
.. _`Bitbucket`: https://bitbucket.org/
.. _`comparison of hosting services`: https://en.wikipedia.org/wiki/Comparison_of_open-source_software_hosting_facilities
