.. index::
   single: Installation

Installing and Configuring Symfony
==================================

The goal of this chapter is to get you up and running with a working application
built on top of Symfony. Fortunately, Symfony offers "distributions", which
are functional Symfony "starter" projects that you can download and begin
developing in immediately.

.. tip::

    If you're looking for instructions on how best to create a new project
    and store it via source control, see `Using Source Control`_.

Installing a Symfony2 Distribution
----------------------------------

.. tip::

    First, check that you have installed and configured a Web server (such as
    Apache) with the most recent PHP version possible (PHP 5.3.8 or newer is
    recommended). For more information on Symfony2 requirements, see the
    :doc:`requirements reference</reference/requirements>`. For information on
    configuring your specific web server document root, see the following
    documentation: `Apache`_ | `Nginx`_ .

Symfony2 packages "distributions", which are fully-functional applications
that include the Symfony2 core libraries, a selection of useful bundles, a
sensible directory structure and some default configuration. When you download
a Symfony2 distribution, you're downloading a functional application skeleton
that can be used immediately to begin developing your application.

Start by visiting the Symfony2 download page at `http://symfony.com/download`_.
On this page, you'll see the *Symfony Standard Edition*, which is the main
Symfony2 distribution. There are 2 ways to get your project started:

Option 1) Composer
~~~~~~~~~~~~~~~~~~

`Composer`_ is a dependency management library for PHP, which you can use
to download the Symfony2 Standard Edition.

Start by `downloading Composer`_ anywhere onto your local computer. If you
have curl installed, it's as easy as:

.. code-block:: bash

    curl -s https://getcomposer.org/installer | php

.. note::

    If your computer is not ready to use Composer, you'll see some recommendations
    when running this command. Follow those recommendations to get Composer
    working properly.

Composer is an executable PHAR file, which you can use to download the Standard
Distribution:

.. code-block:: bash

    php composer.phar create-project symfony/framework-standard-edition /path/to/webroot/Symfony

This command may take several minutes to run as Composer download the Standard
Distribution along with all of the vendor libraries that it needs. When it finishes,
you should have a directory that looks something like this:

.. code-block:: text

    path/to/webroot/ <- your web root directory
        Symfony/ <- the new directory
            app/
                cache/
                config/
                logs/
            src/
                ...
            vendor/
                ...
            web/
                app.php
                ...

Option 2) Download an Archive
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also download an archive of the Standard Edition. Here, you'll
need to make two choices:

* Download either a ``.tgz`` or ``.zip`` archive - both are equivalent, download
  whatever you're more comfortable using;

* Download the distribution with or without vendors. If you're planning on
  using more third-party libraries or bundles and managing them via Composer,
  you should probably download "without vendors".

Download one of the archives somewhere under your local web server's root
directory and unpack it. From a UNIX command line, this can be done with
one of the following commands (replacing ``###`` with your actual filename):

.. code-block:: bash

    # for .tgz file
    $ tar zxvf Symfony_Standard_Vendors_2.1.###.tgz

    # for a .zip file
    $ unzip Symfony_Standard_Vendors_2.1.###.zip

If you've downloaded "without vendors", you'll definitely need to read the
next section.

.. _installation-updating-vendors:

Updating Vendors
~~~~~~~~~~~~~~~~

At this point, you've downloaded a fully-functional Symfony project in which
you'll start to develop your own application. A Symfony project depends on
a number of external libraries. These are downloaded into the `vendor/` directory
of your project via a library called `Composer`_.

Depending on how you downloaded Symfony, you may or may not need to do update
your vendors right now. But, updating your vendors is always safe, and guarantees
that you have all the vendor libraries you need.

Step 1: Get `Composer`_ (The great new PHP packaging system)

.. code-block:: bash

    curl -s http://getcomposer.org/installer | php

Make sure you download ``composer.phar`` in the same folder where
the ``composer.json`` file is located (this is your Symfony project
root by default).

Step 2: Install vendors

.. code-block:: bash

    $ php composer.phar install

This command downloads all of the necessary vendor libraries - including
Symfony itself - into the ``vendor/`` directory.

.. note::

    If you don't have ``curl`` installed, you can also just download the ``installer``
    file manually at http://getcomposer.org/installer. Place this file into your
    project and then run:

    .. code-block:: bash

        php installer
        php composer.phar install

.. tip::

    When running ``php composer.phar install`` or ``php composer.phar update``,
    composer will execute post install/update commands to clear the cache
    and install assets. By default, the assets will be copied into your ``web``
    directory. To create symlinks instead of copying the assets, you can
    add an entry in the ``extra`` node of your composer.json file with the
    key ``symfony-assets-install`` and the value ``symlink``:

    .. code-block:: json

        "extra": {
            "symfony-app-dir": "app",
            "symfony-web-dir": "web",
            "symfony-assets-install": "symlink"
        }

    When passing ``relative`` instead of ``symlink`` to symfony-assets-install,
    the command will generate relative symlinks.

Configuration and Setup
~~~~~~~~~~~~~~~~~~~~~~~

At this point, all of the needed third-party libraries now live in the ``vendor/``
directory. You also have a default application setup in ``app/`` and some
sample code inside the ``src/`` directory.

Symfony2 comes with a visual server configuration tester to help make sure
your Web server and PHP are configured to use Symfony. Use the following URL
to check your configuration:

.. code-block:: text

    http://localhost/config.php

If there are any issues, correct them now before moving on.

.. sidebar:: Setting up Permissions

    One common issue is that the ``app/cache`` and ``app/logs`` directories
    must be writable both by the web server and the command line user. On
    a UNIX system, if your web server user is different from your command
    line user, you can run the following commands just once in your project
    to ensure that permissions will be setup properly. Change ``www-data``
    to your web server user:

    **1. Using ACL on a system that supports chmod +a**

    Many systems allow you to use the ``chmod +a`` command. Try this first,
    and if you get an error - try the next method:

    .. code-block:: bash

        $ rm -rf app/cache/*
        $ rm -rf app/logs/*

        $ sudo chmod +a "www-data allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
        $ sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs

    **2. Using Acl on a system that does not support chmod +a**

    Some systems don't support ``chmod +a``, but do support another utility
    called ``setfacl``. You may need to `enable ACL support`_ on your partition
    and install setfacl before using it (as is the case with Ubuntu), like
    so:

    .. code-block:: bash

        $ sudo setfacl -R -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs
        $ sudo setfacl -dR -m u:www-data:rwx -m u:`whoami`:rwx app/cache app/logs

    Note that not all web servers run as the user ``www-data``. You have to
    check which user the web server is being run as and put it in for ``www-data``.
    This can be done by checking your process list to see which user is running
    your web server processes.

    **3. Without using ACL**

    If you don't have access to changing the ACL of the directories, you will
    need to change the umask so that the cache and log directories will
    be group-writable or world-writable (depending if the web server user
    and the command line user are in the same group or not). To achieve
    this, put the following line at the beginning of the ``app/console``,
    ``web/app.php`` and ``web/app_dev.php`` files:

    .. code-block:: php

        umask(0002); // This will let the permissions be 0775

        // or

        umask(0000); // This will let the permissions be 0777

    Note that using the ACL is recommended when you have access to them
    on your server because changing the umask is not thread-safe.

When everything is fine, click on "Go to the Welcome page" to request your
first "real" Symfony2 webpage:

.. code-block:: text

    http://localhost/app_dev.php/

Symfony2 should welcome and congratulate you for your hard work so far!

.. image:: /images/quick_tour/welcome.jpg

Beginning Development
---------------------

Now that you have a fully-functional Symfony2 application, you can begin
development! Your distribution may contain some sample code - check the
``README.rst`` file included with the distribution (open it as a text file)
to learn about what sample code was included with your distribution and how
you can remove it later.

If you're new to Symfony, join us in the ":doc:`page_creation`", where you'll
learn how to create pages, change configuration, and do everything else you'll
need in your new application.

Be sure to also check out the :doc:`Cookbook</cookbook/index>`, which contains
a wide variety of articles about solving specific problems with Symfony.

Using Source Control
--------------------

If you're using a version control system like ``Git`` or ``Subversion``, you
can setup your version control system and begin committing your project to
it as normal. The Symfony Standard edition *is* the starting point for your
new project.

For specific instructions on how best to setup your project to be stored
in git, see :doc:`/cookbook/workflow/new_project_git`.

Ignoring the ``vendor/`` Directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you've downloaded the archive *without vendors*, you can safely ignore
the entire ``vendor/`` directory and not commit it to source control. With
``Git``, this is done by creating and adding the following to a ``.gitignore``
file:

.. code-block:: text

    /vendor/

Now, the vendor directory won't be committed to source control. This is fine
(actually, it's great!) because when someone else clones or checks out the
project, he/she can simply run the ``php composer.phar install`` script to
install all the necessary project dependencies.

.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
.. _`http://symfony.com/download`: http://symfony.com/download
.. _`Git`: http://git-scm.com/
.. _`GitHub Bootcamp`: http://help.github.com/set-up-git-redirect
.. _`Composer`: http://getcomposer.org/
.. _`downloading Composer`: http://getcomposer.org/download/
.. _`Apache`: http://httpd.apache.org/docs/current/mod/core.html#documentroot
.. _`Nginx`: http://wiki.nginx.org/Symfony
