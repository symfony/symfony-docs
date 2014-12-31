.. index::
   single: Installation

Installing and Configuring Symfony
==================================

The goal of this chapter is to get you up and running with a working application
built on top of Symfony. In order to simplify the process of creating new
applications, Symfony provides an installer that must be installed before
creating the first application.

Installing the Symfony Installer
--------------------------------

Using the Symfony Installer is the only recommended way to create new Symfony
applications. This installer is a PHP application that has to be installed
only once and then it can create any number of Symfony applications.

.. note::

    The installer requires PHP 5.4 or higher. If you still use the legacy
    PHP 5.3 version, you cannot use the Symfony Installer. Read the
    :ref:`book-creating-applications-without-the-installer` section to learn how
    to proceed.

Depending on your operating system, the installer must be installed in different
ways.

Linux and Mac OS X Systems
~~~~~~~~~~~~~~~~~~~~~~~~~~

Open your command console and execute the following three commands:

.. code-block:: bash

    $ curl -LsS http://symfony.com/installer > symfony.phar
    $ sudo mv symfony.phar /usr/local/bin/symfony
    $ chmod a+x /usr/local/bin/symfony

This will create a global ``symfony`` command in your system that will be used
to create new Symfony applications.

Windows Systems
~~~~~~~~~~~~~~~

Open your command console and execute the following command:

.. code-block:: bash

    c:\> php -r "readfile('http://symfony.com/installer');" > symfony.phar

Then, move the downloaded ``symfony.phar`` file to your projects directory and
execute it as follows:

.. code-block:: bash

    c:\> move symfony.phar c:\projects
    c:\projects\> php symfony.phar

Creating the Symfony Application
--------------------------------

Once the Symfony Installer is ready, create your first Symfony application with
the ``new`` command:

.. code-block:: bash

    # Linux, Mac OS X
    $ symfony new my_project_name

    # Windows
    c:\> cd projects/
    c:\projects\> php symfony.phar new my_project_name

This command creates a new directory called ``my_project_name`` that contains a
fresh new project based on the most recent stable Symfony version available. In
addition, the installer checks if your system meets the technical requirements
to execute Symfony applications. If not, you'll see the list of changes needed
to meet those requirements.

.. tip::

    For security reasons, all Symfony versions are digitally signed before
    distributing them. If you want to verify the integrity of any Symfony
    version, follow the steps `explained in this post`_.

Basing your Project on a Specific Symfony Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your project needs to be based on a specific Symfony version, pass the version
number as the second argument of the ``new`` command:

.. code-block:: bash

    # Linux, Mac OS X
    $ symfony new my_project_name 2.3.23

    # Windows
    c:\projects\> php symfony.phar new my_project_name 2.3.23

Read the `Symfony Release process`_ to better understand why there are several
Symfony versions and which one to use for your projects.

.. _book-creating-applications-without-the-installer:

Creating Symfony Applications without the Installer
---------------------------------------------------

If you still use PHP 5.3, or if you can't execute the installer for any reason,
you can create Symfony applications using the alternative installation method
based con `Composer`_.

Composer is the dependency manager used by modern PHP applications and it can
also be used to create new applications based on the Symfony framework. If you
don't have installed it globally, start by reading the next section.

Installing Composer Globally
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Start with :doc:`installing Composer globally </cookbook/composer>`.

Creating a Symfony Application with Composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once Composer is installed on your computer, execute the ``create-project``
command to create a new Symfony application based on its latest stable version:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project_name

If you need to base your application on a specific Symfony version, provide that
version as the second argument of the ``create-project`` command:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project_name '2.3.*'

.. tip::

    If your Internet connection is slow, you may think that Composer is not
    doing anything. If that's your case, add the ``-vvv`` flag to the previous
    command to display a detailed output of everything that Composer is doing.

Running the Symfony Application
-------------------------------

Symfony leverages the internal web server provided by PHP to run applications
while developing them. Therefore, running a Symfony application is a matter of
browsing the project directory and executing this command:

.. code-block:: bash

    $ cd my_project_name/
    $ php app/console server:run

Then, open your browser and access the ``http://localhost:8000`` URL to see the
Welcome page of Symfony:

.. image:: /images/quick_tour/welcome.png
   :align: center
   :alt:   Symfony Welcome Page

Instead of the Welcome Page, you may see a blank page or an error page.
This is caused by a directory permission misconfiguration. There are several
possible solutions depending on your operating system. All of them are
explained in the :ref:`Setting up Permissions <book-installation-permissions>`
section.

.. note::

    PHP's internal web server is available in PHP 5.4 or higher versions. If you
    still use the legacy PHP 5.3 version, you'll have to configure a *virtual host*
    in your web server.

The ``server:run`` command is only suitable while developing the application. In
order to run Symfony applications on production servers, you'll have to configure
your `Apache`_ or `Nginx`_ web server as explained in
:doc:`/cookbook/configuration/web_server_configuration`.

When you are finished working on your Symfony application, you can stop the
server with the ``server:stop`` command:

.. code-block:: bash

    $ php app/console server:stop

Checking Symfony Application Configuration and Setup
----------------------------------------------------

Symfony applications come with a visual server configuration tester to show if
your environment is ready to use Symfony. Access the following URL to check your
configuration:

.. code-block:: text

    http://localhost:8000/config.php

If there are any issues, correct them now before moving on.

.. _book-installation-permissions:

.. sidebar:: Setting up Permissions

    One common issue when installing Symfony is that the ``app/cache`` and
    ``app/logs`` directories must be writable both by the web server and the
    command line user. On a UNIX system, if your web server user is different
    from your command line user, you can try one of the following solutions.

    **1. Use the same user for the CLI and the web server**

    In development environments, it is a common practice to use the same UNIX
    user for the CLI and the web server because it avoids any of these permissions
    issues when setting up new projects. This can be done by editing your web server
    configuration (e.g. commonly httpd.conf or apache2.conf for Apache) and setting
    its user to be the same as your CLI user (e.g. for Apache, update the ``User``
    and ``Group`` values).

    **2. Using ACL on a system that supports chmod +a**

    Many systems allow you to use the ``chmod +a`` command. Try this first,
    and if you get an error - try the next method. This uses a command to
    try to determine your web server user and set it as ``HTTPDUSER``:

    .. code-block:: bash

        $ rm -rf app/cache/*
        $ rm -rf app/logs/*

        $ HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
        $ sudo chmod +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
        $ sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs


    **3. Using ACL on a system that does not support chmod +a**

    Some systems don't support ``chmod +a``, but do support another utility
    called ``setfacl``. You may need to `enable ACL support`_ on your partition
    and install setfacl before using it (as is the case with Ubuntu). This
    uses a command to try to determine your web server user and set it as
    ``HTTPDUSER``:

    .. code-block:: bash

        $ HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
        $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
        $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs

    If this doesn't work, try adding ``-n`` option.

    **4. Without using ACL**

    If none of the previous methods work for you, change the umask so that the
    cache and log directories will be group-writable or world-writable (depending
    if the web server user and the command line user are in the same group or not).
    To achieve this, put the following line at the beginning of the ``app/console``,
    ``web/app.php`` and ``web/app_dev.php`` files::

        umask(0002); // This will let the permissions be 0775

        // or

        umask(0000); // This will let the permissions be 0777

    Note that using the ACL is recommended when you have access to them
    on your server because changing the umask is not thread-safe.

.. _installation-updating-vendors:

Updating Symfony Applications
-----------------------------

At this point, you've created a fully-functional Symfony application in which
you'll start to develop your own project. A Symfony application depends on
a number of external libraries. These are downloaded into the ``vendor/`` directory
and they are managed exclusively by Composer.

Updating those third-party libraries frequently is a good practice to prevent bugs
and security vulnerabilities. Execute the ``update`` Composer command to update
them all at once:

.. code-block:: bash

    $ cd my_project_name/
    $ composer update

Depending on the complexity of your project, this update process can take up to
several minutes to complete.

.. _installing-a-symfony2-distribution:

Installing a Symfony Distribution
---------------------------------

Symfony project packages "distributions", which are fully-functional applications
that include the Symfony core libraries, a selection of useful bundles, a
sensible directory structure and some default configuration. In fact, when you
created a Symfony application in the previous sections, you actually downloaded the
default distribution provided by Symfony, which is called *Symfony Standard Edition*.

The *Symfony Standard Edition* is by far the most popular distribution and it's
also the best choice for developers starting with Symfony. However, the Symfony
Community has published other popular distributions that you may use in your
applications:

* The `Symfony CMF Standard Edition`_ is the best distribution to get started
  with the `Symfony CMF`_ project, which is a project that makes it easier for
  developers to add CMS functionality to applications built with the Symfony
  framework.
* The `Symfony REST Edition`_ shows how to build an application that provides a
  RESTful API using the FOSRestBundle and several other related bundles.

Using Source Control
--------------------

If you're using a version control system like `Git`_, you can safely commit all
your project's code. The reason is that Symfony applications already contain a
``.gitignore`` file specially prepared for Symfony.

For specific instructions on how best to set up your project to be stored
in Git, see :doc:`/cookbook/workflow/new_project_git`.

Checking out a versioned Symfony Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using Composer to manage application's dependencies, it's recommended to
ignore the entire ``vendor/`` directory before committing its code to the
repository. This means that when checking out a Symfony application from a Git
repository, there will be no ``vendor/`` directory and the application won't
work out-of-the-box.

In order to make it work, check out the Symfony application and then execute the
``install`` Composer command to download and install all the dependencies required
by the application:

.. code-block:: bash

    $ cd my_project_name/
    $ composer install

How does Composer know which specific dependencies to install? Because when a
Symfony application is committed to a repository, the ``composer.json`` and
``composer.lock`` files are also committed. These files tell Composer which
dependencies (and which specific versions) to install for the application.

Beginning Development
---------------------

Now that you have a fully-functional Symfony application, you can begin
development! Your distribution may contain some sample code - check the
``README.md`` file included with the distribution (open it as a text file)
to learn about what sample code was included with your distribution.

If you're new to Symfony, check out ":doc:`page_creation`", where you'll
learn how to create pages, change configuration, and do everything else you'll
need in your new application.

Be sure to also check out the :doc:`Cookbook </cookbook/index>`, which contains
a wide variety of articles about solving specific problems with Symfony.

.. note::

    If you want to remove the sample code from your distribution, take a look
    at this cookbook article: ":doc:`/cookbook/bundles/remove`"

.. _`Symfony Release process`: http://symfony.com/doc/current/contributing/community/releases.html
.. _`explained in this post`: http://fabien.potencier.org/article/73/signing-project-releases
.. _`Composer`: http://getcomposer.org/
.. _`Composer download page`: https://getcomposer.org/download/
.. _`Apache`: http://httpd.apache.org/docs/current/mod/core.html#documentroot
.. _`Nginx`: http://wiki.nginx.org/Symfony
.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
.. _`Symfony CMF Standard Edition`: https://github.com/symfony-cmf/symfony-cmf-standard
.. _`Symfony CMF`: http://cmf.symfony.com/
.. _`Symfony REST Edition`: https://github.com/gimler/symfony-rest-edition
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`Git`: http://git-scm.com/
