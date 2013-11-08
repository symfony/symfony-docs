.. index::
   single: Workflow; Subversion

How to Create and store a Symfony2 Project in Subversion
========================================================

.. tip::

    This entry is specifically about Subversion, and based on principles found
    in :doc:`/cookbook/workflow/new_project_git`.

Once you've read through :doc:`/book/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. The
preferred method to manage Symfony2 projects is using `git`_ but some prefer
to use `Subversion`_ which is totally fine!. In this cookbook article, you'll
learn how to manage your project using `svn`_ in a similar manner you
would do with `git`_.

.. tip::

    This is **a** method to tracking your Symfony2 project in a Subversion
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

    Most subversion hosting should follow this standard practice. This
    is the recommended layout in `Version Control with Subversion`_ and the
    layout used by most free hosting (see :ref:`svn-hosting`).

Initial Project Setup
---------------------

To get started, you'll need to download Symfony2 and get the basic Subversion setup:

1. Download the `Symfony2 Standard Edition`_ with or without vendors.

2. Unzip/untar the distribution. It will create a folder called Symfony with
   your new project structure, config files, etc. Rename it to whatever you
   like.

3. Checkout the Subversion repository that will host this project. Let's say it
   is hosted on `Google code`_ and called ``myproject``:

   .. code-block:: bash

        $ svn checkout http://myproject.googlecode.com/svn/trunk myproject

4. Copy the Symfony2 project files in the subversion folder:

   .. code-block:: bash

        $ mv Symfony/* myproject/

5. Let's now set the ignore rules. Not everything *should* be stored in your
   subversion repository. Some files (like the cache) are generated and
   others (like the database configuration) are meant to be customized
   on each machine. This makes use of the ``svn:ignore`` property, so that
   specific files can be ignored.

   .. code-block:: bash

        $ cd myproject/
        $ svn add --depth=empty app app/cache app/logs app/config web

        $ svn propset svn:ignore "vendor" .
        $ svn propset svn:ignore "bootstrap*" app/
        $ svn propset svn:ignore "parameters.yml" app/config/
        $ svn propset svn:ignore "*" app/cache/
        $ svn propset svn:ignore "*" app/logs/

        $ svn propset svn:ignore "bundles" web

        $ svn ci -m "commit basic Symfony ignore list (vendor, app/bootstrap*, app/config/parameters.yml, app/cache/*, app/logs/*, web/bundles)"

6. The rest of the files can now be added and committed to the project:

   .. code-block:: bash

        $ svn add --force .
        $ svn ci -m "add basic Symfony Standard 2.X.Y"

7. Copy ``app/config/parameters.yml`` to ``app/config/parameters.yml.dist``.
   The ``parameters.yml`` file is ignored by svn (see above) so that
   machine-specific settings like database passwords aren't committed. By
   creating the ``parameters.yml.dist`` file, new developers can quickly clone
   the project, copy this file to ``parameters.yml``, customize it, and start
   developing.

8. Finally, download all of the third-party vendor libraries by
   executing composer. For details, see :ref:`installation-updating-vendors`.

.. tip::

	If you rely on any "dev" versions, then git may be used to install
	those libraries, since there is no archive available for download.

At this point, you have a fully-functional Symfony2 project stored in your
Subversion repository. The development can start with commits in the Subversion
repository.

You can continue to follow along with the :doc:`/book/page_creation` chapter
to learn more about how to configure and develop inside your application.

.. tip::

    The Symfony2 Standard Edition comes with some example functionality. To
    remove the sample code, follow the instructions in the
    ":doc:`/cookbook/bundles/remove`" article.

.. include:: _vendor_deps.rst.inc

.. _svn-hosting:

Subversion hosting solutions
----------------------------

The biggest difference between `git`_ and `svn`_ is that Subversion *needs* a
central repository to work. You then have several solutions:

- Self hosting: create your own repository and access it either through the
  filesystem or the network. To help in this task you can read `Version Control
  with Subversion`_.

- Third party hosting: there are a lot of serious free hosting solutions
  available like `GitHub`_, `Google code`_, `SourceForge`_ or `Gna`_. Some of them offer
  git hosting as well.

.. _`git`: http://git-scm.com/
.. _`svn`: http://subversion.apache.org/
.. _`Subversion`: http://subversion.apache.org/
.. _`Symfony2 Standard Edition`: http://symfony.com/download
.. _`Version Control with Subversion`: http://svnbook.red-bean.com/
.. _`GitHub`: https://github.com/
.. _`Google code`: http://code.google.com/hosting/
.. _`SourceForge`: http://sourceforge.net/
.. _`Gna`: http://gna.org/
