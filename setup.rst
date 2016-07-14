.. index::
   single: Installing and Setting up Symfony

Installing & Setting up the Symfony Framework
=============================================

This article explains how to install Symfony in different ways and how to solve
the most common issues that may appear during the installation process.

.. seealso::

    Do you prefer video tutorials? Check out the `Joyful Development with Symfony`_
    screencast series from KnpUniversity.

Creating Symfony Applications
-----------------------------

Symfony provides a dedicated application called **Symfony Installer** to ease
the creation of Symfony applications. This installer is a PHP 5.4 compatible
application that has to be installed in your system only once:

.. code-block:: bash

    # Linux and macOS systems
    $ sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
    $ sudo chmod a+x /usr/local/bin/symfony

    # Windows systems
    c:\> php -r "readfile('https://symfony.com/installer');" > symfony

.. note::

    In Linux and macOS, a global ``symfony`` command is created. In Windows,
    move the ``symfony`` file to some directory included in the ``PATH``
    environment variable to create the global command or move it to any other
    directory convenient for you:

    .. code-block:: bash

        # for example, if WAMP is used ...
        c:\> move symfony c:\wamp\bin\php
        # ... then, execute the command as:
        c:\> symfony

        # moving it to your projects folder ...
        c:\> move symfony c:\projects
        # ... then, execute the command as
        c:\> cd projects
        c:\projects\> php symfony

.. _installation-creating-the-app:

Once the Symfony Installer is installed, create your first Symfony application
with the ``new`` command:

.. code-block:: bash

    $ symfony new my_project_name

This command creates a new directory called ``my_project_name/`` that contains
an empty project based on the most recent stable Symfony version available. In
addition, the installer checks if your system meets the technical requirements
to execute Symfony applications. If not, you'll see the list of changes needed
to meet those requirements.

.. note::

    If the installer doesn't work for you or doesn't output anything, make sure
    that the PHP `Phar extension`_ is installed and enabled on your computer.

Basing your Project on a Specific Symfony Version
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In case your project needs to be based on a specific Symfony version, use the
optional second argument of the ``new`` command:

.. code-block:: bash

    # use the most recent version in any Symfony branch
    $ symfony new my_project_name 2.8
    $ symfony new my_project_name 3.1

    # use a specific Symfony version
    $ symfony new my_project_name 2.8.3
    $ symfony new my_project_name 3.1.5

    # use a beta or RC version (useful for testing new Symfony versions)
    $ symfony new my_project 2.7.0-BETA1
    $ symfony new my_project 2.7.0-RC1

    # use the most recent 'lts' version (Long Term Support version)
    $ symfony new my_project_name lts

.. note::

    Read the :doc:`Symfony Release process </contributing/community/releases>`
    to better understand why there are several Symfony versions and which one
    to use for your projects.

.. _book-creating-applications-without-the-installer:

Creating Symfony Applications with Composer
-------------------------------------------

If you still use PHP 5.3 or can't use the Symfony installer for any reason, you
can create Symfony applications with `Composer`_, the dependency manager used by
modern PHP applications.

If you don't have Composer installed in your computer, start by
:doc:`installing Composer globally </setup/composer>`. Then, execute the
``create-project`` command to create a new Symfony application based on its
latest stable version:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project_name

You can also install any other Symfony version by passing a second argument to
the ``create-project`` command:

.. code-block:: bash

    $ composer create-project symfony/framework-standard-edition my_project_name "2.8.*"

.. tip::

    If your Internet connection is slow, you may think that Composer is not
    doing anything. If that's your case, add the ``-vvv`` flag to the previous
    command to display a detailed output of everything that Composer is doing.

Running the Symfony Application
-------------------------------

Symfony leverages the internal PHP web server (available since PHP 5.4) to run
applications while developing them. Therefore, running a Symfony application is
a matter of browsing the project directory and executing this command:

.. code-block:: bash

    $ cd my_project_name/
    $ php app/console server:run

Then, open your browser and access the ``http://localhost:8000/`` URL to see the
Welcome Page of Symfony:

.. image:: /_images/quick_tour/welcome.png
   :align: center
   :alt:   Symfony Welcome Page

If you see a blank page or an error page instead of the Welcome Page, there is
a directory permission misconfiguration. The solution to this problem is
explained in the :ref:`Setting up Permissions <book-installation-permissions>`
section.

When you are finished working on your Symfony application, stop the server by
pressing ``Ctrl+C`` from the terminal or command console.

.. tip::

    PHP's internal web server is great for developing, but should **not** be
    used on production. Instead, use Apache or Nginx.
    See :doc:`/setup/web_server_configuration`.

Checking Symfony Application Configuration and Setup
----------------------------------------------------

The Symfony Installer checks if your system is ready to run Symfony applications.
However, the PHP configuration for the command console can be different from the
PHP web configuration. For that reason, Symfony provides a visual configuration
checker. Access the following URL to check your configuration and fix any issue
before moving on:

.. code-block:: text

    http://localhost:8000/config.php

.. _book-installation-permissions:

Setting up Permissions
----------------------

One important Symfony requirement is that the ``var`` directory must be
writable both by the web server and the command line user.

On Linux and macOS systems, if your web server user is different from your
command line user, you need to configure permissions properly to avoid issues.
There are several ways to achieve that:

1. Use the same user for the CLI and the web server
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Edit your web server configuration (commonly ``httpd.conf`` or ``apache2.conf``
for Apache) and set its user to be the same as your CLI user (e.g. for Apache,
update the ``User`` and ``Group`` directives).

.. caution::

    If this solution is used in a production server, be sure this user only has
    limited privileges (no access to private data or servers, execution of
    unsafe binaries, etc.) as a compromised server would give to the hacker
    those privileges.

2. Using ACL on a system that supports ``chmod +a`` (macOS)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

On macOS systems, the ``chmod`` command supports the ``+a`` flag to define an
ACL. Use the following script to determine your web server user and grant the
needed permissions:

.. code-block:: bash

    $ rm -rf var/cache/* var/logs/* var/sessions/*

    $ HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    $ sudo chmod -R +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" var
    $ sudo chmod -R +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" var

3. Using ACL on a system that supports ``setfacl`` (Linux/BSD)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Most Linux and BSD distributions don't support ``chmod +a``, but do support
another utility called ``setfacl``. You may need to install ``setfacl`` and
`enable ACL support`_ on your disk partition before using it. Then, use the
following script to determine your web server user and grant the needed permissions:

.. code-block:: bash

    $ HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    # if this doesn't work, try adding `-n` option
    $ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
    $ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var

.. note::

    setfacl isn't available on NFS mount points. However, storing cache and logs
    over NFS is strongly discouraged for performance reasons.

4. Without using ACL
~~~~~~~~~~~~~~~~~~~~

If none of the previous methods work for you, change the umask so that the
cache and log directories are group-writable or world-writable (depending
if the web server user and the command line user are in the same group or not).
To achieve this, put the following line at the beginning of the ``bin/console``,
``web/app.php`` and ``web/app_dev.php`` files::

    umask(0002); // This will let the permissions be 0775

    // or

    umask(0000); // This will let the permissions be 0777

.. note::

    Changing the umask is not thread-safe, so the ACL methods are recommended
    when they are available.

.. _installation-updating-vendors:

Updating Symfony Applications
-----------------------------

At this point, you've created a fully-functional Symfony application in which
you'll start to develop your own project. A Symfony application depends on
a number of third-party libraries stored in the ``vendor/`` directory and
managed by Composer.

Updating those libraries frequently is a good practice to prevent bugs and
security vulnerabilities. Execute the ``update`` Composer command to update them
all at once (this can take up to several minutes to complete depending on the
complexity of your project):

.. code-block:: bash

    $ cd my_project_name/
    $ composer update

.. tip::

    Symfony provides a command to check whether your project's dependencies
    contain any known security vulnerability:

    .. code-block:: bash

        $ php bin/console security:check

    A good security practice is to execute this command regularly to be able to
    update or replace compromised dependencies as soon as possible.

Installing the Symfony Demo Application
---------------------------------------

The `Symfony Demo application`_ is a fully-functional application that shows the
recommended way to develop Symfony applications. The application has been
conceived as a learning tool for Symfony newcomers and its source code contains
tons of comments and helpful notes.

If you have installed the Symfony Installer as explained in the above sections,
install the Symfony Demo application anywhere in your system executing the
``demo`` command:

.. code-block:: bash

    $ symfony demo

Once downloaded, enter into the ``symfony_demo/`` directory, run the PHP's
built-in web server (``php app/console server:run``) and access to the
``http://localhost:8000`` URL to start using the Symfony Demo application.

.. _installing-a-symfony2-distribution:

Installing a Symfony Distribution
---------------------------------

Symfony distributions are fully-functional applications that include the Symfony
core libraries, a selection of useful bundles, a sensible directory structure
and some default configuration. In fact, when you created a Symfony application
in the previous sections, you actually downloaded the default distribution
provided by Symfony, which is called `Symfony Standard Edition`_.

The Symfony Standard Edition is the best choice for developers starting with
Symfony. However, the Symfony Community has published other distributions that
you may use in your applications:

* The `Symfony CMF Standard Edition`_ to get started with the `Symfony CMF`_
  project, which is a project that makes it easier for developers to add CMS
  functionality to Symfony applications.
* The `Symfony REST Edition`_ shows how to build an application that provides a
  RESTful API using the `FOSRestBundle`_ and several other related bundles.

.. _install-existing-app:

Installing an Existing Symfony Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When working collaboratively in a Symfony application, it's uncommon to create
a new Symfony application as explained in the previous sections. Instead,
someone else has already created and submitted it to a shared repository.

It's recommended to not submit some files (:ref:`parameters.yml <config-parameters-yml>`)
and directories (``vendor/``, cache, logs) to the repository, so you'll have to do
the following when installing an existing Symfony application:

.. code-block:: bash

    # clone the project to download its contents
    $ cd projects/
    $ git clone ...

    # make Composer install the project's dependencies into vendor/
    $ cd my_project_name/
    $ composer install

    # now Composer will ask you for the values of any undefined parameter
    $ ...

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    install/*
    setup/*

.. _`Joyful Development with Symfony`: http://knpuniversity.com/screencast/symfony
.. _`Composer`: https://getcomposer.org/
.. _`enable ACL support`: https://help.ubuntu.com/community/FilePermissionsACLs
.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard
.. _`Symfony CMF Standard Edition`: https://github.com/symfony-cmf/symfony-cmf-standard
.. _`Symfony CMF`: http://cmf.symfony.com/
.. _`Symfony REST Edition`: https://github.com/gimler/symfony-rest-edition
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`Git`: http://git-scm.com/
.. _`Phar extension`: http://php.net/manual/en/intro.phar.php
.. _`Symfony Demo application`: https://github.com/symfony/symfony-demo
