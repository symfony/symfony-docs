How to Create and store a Symfony2 Project in Subversion
========================================================

.. tip::

    This entry is specifically about Subversion, and based on principles found
    in :doc:`/cookbook/workflow/new_project_git`.

Once you've read through :doc:`/book/page_creation` and become familiar with
using Symfony, you'll no-doubt be ready to start your own project. The
preferred method to manage Symfony2 projects is using `git`_ but some people
are stuck with `Subversion`_ and don't have a choice. In this cookbook article,
you'll learn how to manage your project using `svn`_ in a similar manner you
would do with `git`_.

.. caution::

    This is **a** method to import your Symfony2 project in a Subversion
    repository. There are several ways to do and this one is simply one that
    works. Furthermore, it is quite complex to do so you should probably
    consider using `git`_ anyway.

Subversion repository
---------------------

For this article we will suppose that your repository layout follows the
widespread standard structure :

.. code-block:: text

    myproject/
        branches/
        tags/
        trunk/

.. tip::

    Most of the subversion hosting should follow this standard practice. This
    is the recommended layout in `Version Control with Subversion`_ and the
    layout used by most free hosting (see :ref:`svn-hosting`).

Initial Project Setup
---------------------

To get started, you'll need to download Symfony2 and get the basic Subversion setup :

1. Download the `Symfony2 Standard Edition`_ without vendors.

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

5. Let's now set the ignore rules, this is the complex part compared to `git`_.
   You need to add some specific files/folders to the subversion repository and
   edit ``svn:ignore`` properties:

    .. code-block:: bash

        $ cd myproject/
        $ svn add --depth=empty app app/cache app/logs app/config web
        $ svn propedit svn:ignore .
        vendor
        $ svn propedit svn:ignore app/
        bootstrap*
        $ svn propedit svn:ignore app/config/
        parameters.ini
        $ svn propedit svn:ignore app/cache/
        *
        $ svn propedit svn:ignore app/logs/
        *
        $ svn propedit svn:ignore web
        bundles
        $ svn ci -m "commit basic symfony ignore list (vendor, app/bootstrap*, app/config/parameters.ini, app/cache/*, app/logs/*, web/bundles)"

.. tip::

    This part is a bit painful but this is the only way to make sure that those
    files and folders will **never** appear in your project repository.

6. The rest of the files can now be added and commited to the project:

    .. code-block:: bash

        $ svn add --force .
        $ svn ci -m "add basic Symfony Standard 2.X.Y"

7. Copy ``app/config/parameters.ini`` to ``app/config/parameters.ini.dist``.
   The ``parameters.ini`` file is ignored by svn (see above) so that
   machine-specific settings like database passwords aren't committed. By
   creating the ``parameters.ini.dist`` file, new developers can quickly clone
   the project, copy this file to ``parameters.ini``, customize it, and start
   developing.

8. Finally, download all of the third-party vendor libraries:

    .. code-block:: bash
    
        $ php bin/vendors install

At this point, you have a fully-functional Symfony2 project, followed in your
Subversion repository. The development can start with commits in the Subversion
repository.

You can continue to follow along with the :doc:`/book/page_creation` chapter
to learn more about how to configure and develop inside your application.

.. tip::

    The Symfony2 Standard Edition comes with some example functionality. To
    remove the sample code, follow the instructions on the `Standard Edition Readme`_.

.. _cookbook-managing-vendor-libraries:

Managing Vendor Libraries with bin/vendors and deps
---------------------------------------------------

Every Symfony project uses a large group of third-party "vendor" libraries.

By default, these libraries are downloaded by running the ``php bin/vendors install``
script. This script reads from the ``deps`` file, and downloads the given
libraries into the ``vendor/`` directory. It also reads ``deps.lock`` file,
pinning each library listed there to the exact git commit hash.

In this setup, the vendors libraries aren't part of your repository,
not even as submodules. Instead, we rely on the ``deps`` and ``deps.lock``
files and the ``bin/vendors`` script to manage everything. Those files are
part of your repository, so the necessary versions of each third-party library
are version-controlled, and you can use the vendors script to bring
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

    There is also a ``php bin/vendors update`` command, but this has nothing
    to do with upgrading your project and you will normally not need to use
    it. This command is used to freeze the versions of all of your vendor libraries
    by updating them to the version specified in ``deps`` and recording it
    into the ``deps.lock`` file.

.. _svn-hosting:

Subversion hosting solutions
----------------------------

The biggest difference between `git`_ and `svn`_ is that Subversion *needs* a
central repository to work. You then have several solutions :

- Self hosting: create your own repository and access it either through the
  filesystem or the network. To help in this task you can read `Version Control
  with Subversion`_.

- Third party hosting: there are a lot of serious free hosting solutions
  available like `Google code`_, `SourceForge`_ or `Gna`_. Some of them offer
  git hosting as well.

.. _`git`: http://git-scm.com/
.. _`svn`: http://subversion.apache.org/
.. _`Subversion`: http://subversion.apache.org/
.. _`Symfony2 Standard Edition`: http://symfony.com/download
.. _`Standard Edition Readme`: https://github.com/symfony/symfony-standard/blob/master/README.md
.. _`Version Control with Subversion`: http://svnbook.red-bean.com/
.. _`Google code`: http://code.google.com/hosting/
.. _`SourceForge`: http://sourceforge.net/
.. _`Gna`: http://gna.org/
