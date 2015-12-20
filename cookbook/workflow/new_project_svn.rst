.. index::
   single: Workflow; Subversion

.. _how-to-create-and-store-a-symfony2-project-in-subversion:

How to Create and Store a Symfony Project in Subversion
=======================================================

.. tip::

    This entry is specifically about Subversion, and based on principles found
    in :doc:`/cookbook/workflow/new_project_git`.

Once you've read through :doc:`/book/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. The
preferred method to manage Symfony projects is using `Git`_ but some prefer
to use `Subversion`_ which is totally fine!. In this cookbook article, you'll
learn how to manage your project using `SVN`_ in a similar manner you
would do with `Git`_.

.. tip::

    This is **a** method to tracking your Symfony project in a Subversion
    repository. There are several ways to do and this one is simply one that
    works.

The Subversion Repository
-------------------------

For this article it's assumed that your repository layout follows the
widespread standard structure:

.. code-block:: text

    myproject/
        branches/
        tags/
        trunk/

.. tip::

    Most Subversion hosting should follow this standard practice. This
    is the recommended layout in `Version Control with Subversion`_ and the
    layout used by most free hosting (see :ref:`svn-hosting`).

Initial Project Setup
---------------------

To get started, you'll need to download Symfony and get the basic Subversion setup.
First, download and get your Symfony project running by following the
:doc:`Installation </book/installation>` chapter.

Once you have your new project directory and things are working, follow along
with these steps:

#. Checkout the Subversion repository that will host this project. Suppose
   it is hosted on `Google code`_ and called ``myproject``:

   .. code-block:: bash

        $ svn checkout http://myproject.googlecode.com/svn/trunk myproject

#. Copy the Symfony project files in the Subversion folder:

   .. code-block:: bash

        $ mv Symfony/* myproject/

#. Now, set the ignore rules. Not everything *should* be stored in your Subversion
   repository. Some files (like the cache) are generated and others (like
   the database configuration) are meant to be customized on each machine.
   This makes use of the ``svn:ignore`` property, so that specific files can
   be ignored.

   .. code-block:: bash

        $ cd myproject/
        $ svn add --depth=empty app var/cache var/logs app/config web

        $ svn propset svn:ignore "vendor" .
        $ svn propset svn:ignore "bootstrap*" var/
        $ svn propset svn:ignore "parameters.yml" app/config/
        $ svn propset svn:ignore "*" var/cache/
        $ svn propset svn:ignore "*" var/logs/

        $ svn propset svn:ignore "bundles" web

        $ svn ci -m "commit basic Symfony ignore list (vendor, var/bootstrap*, app/config/parameters.yml, var/cache/*, var/logs/*, web/bundles)"

#. The rest of the files can now be added and committed to the project:

   .. code-block:: bash

        $ svn add --force .
        $ svn ci -m "add basic Symfony Standard 2.X.Y"

That's it! Since the ``app/config/parameters.yml`` file is ignored, you can
store machine-specific settings like database passwords here without committing
them. The ``parameters.yml.dist`` file *is* committed, but is not read by
Symfony. And by adding any new keys you need to both files, new developers
can quickly clone the project, copy this file to ``parameters.yml``, customize
it, and start developing.

At this point, you have a fully-functional Symfony project stored in your
Subversion repository. The development can start with commits in the Subversion
repository.

You can continue to follow along with the :doc:`/book/page_creation` chapter
to learn more about how to configure and develop inside your application.

.. tip::

    The Symfony Standard Edition comes with some example functionality. To
    remove the sample code, follow the instructions in the
    ":doc:`/cookbook/bundles/remove`" article.

.. include:: _vendor_deps.rst.inc

.. _svn-hosting:

Subversion Hosting Solutions
----------------------------

The biggest difference between `Git`_ and `SVN`_ is that Subversion *needs* a
central repository to work. You then have several solutions:

- Self hosting: create your own repository and access it either through the
  filesystem or the network. To help in this task you can read `Version Control
  with Subversion`_.

- Third party hosting: there are a lot of serious free hosting solutions
  available like `GitHub`_, `Google code`_, `SourceForge`_ or `Gna`_. Some of them offer
  Git hosting as well.

.. _`Git`: http://git-scm.com/
.. _`SVN`: http://subversion.apache.org/
.. _`Subversion`: http://subversion.apache.org/
.. _`Symfony Standard Edition`: https://symfony.com/download
.. _`Version Control with Subversion`: http://svnbook.red-bean.com/
.. _`GitHub`: https://github.com/
.. _`Google code`: http://code.google.com/hosting/
.. _`SourceForge`: http://sourceforge.net/
.. _`Gna`: http://gna.org/
