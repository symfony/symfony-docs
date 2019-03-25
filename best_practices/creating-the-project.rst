Creating the Project
====================

Installing Symfony
------------------

.. best-practice::

    Use Composer and Symfony Flex to create and manage Symfony applications.

`Composer`_ is the package manager used by modern PHP applications to manage
their dependencies. `Symfony Flex`_ is a Composer plugin designed to automate
some of the most common tasks performed in Symfony applications. Using Flex is
optional but recommended because it improves your productivity significantly.

.. best-practice::

    Use the Symfony Skeleton to create new Symfony-based projects.

The `Symfony Skeleton`_ is a minimal and empty Symfony project which you can
base your new projects on. Unlike past Symfony versions, this skeleton installs
the absolute bare minimum amount of dependencies to make a fully working Symfony
project. Read the :doc:`/setup` article to learn more about installing Symfony.

.. _linux-and-mac-os-x-systems:
.. _windows-systems:

Creating the Blog Application
-----------------------------

In your command console, browse to a directory where you have permission to
create files and execute the following commands:

.. code-block:: terminal

    $ cd projects/
    $ composer create-project symfony/skeleton blog

This command creates a new directory called ``blog`` that contains a fresh new
project based on the most recent stable Symfony version available.

.. tip::

    The technical requirements to run Symfony are simple. If you want to check
    if your system meets those requirements, read :doc:`/reference/requirements`.

Structuring the Application
---------------------------

After creating the application, enter the ``blog/`` directory and you'll see a
number of files and directories generated automatically. These are the most
important ones:

.. code-block:: text

    blog/
    ├─ bin/
    │  └─ console
    ├─ config/
    └─ public/
    │  └─ index.php
    ├─ src/
    │  └─ Kernel.php
    ├─ var/
    │  ├─ cache/
    │  └─ log/
    └─ vendor/

This file and directory hierarchy is the convention proposed by Symfony to
structure your applications. It's recommended to keep this structure because it's
easy to navigate and most directory names are self-explanatory, but you can
:doc:`override the location of any Symfony directory </configuration/override_dir_structure>`:

Application Bundles
~~~~~~~~~~~~~~~~~~~

When Symfony 2.0 was released, most developers naturally adopted the symfony
1.x way of dividing applications into logical modules. That's why many Symfony
applications used bundles to divide their code into logical features: UserBundle,
ProductBundle, InvoiceBundle, etc.

But a bundle is *meant* to be something that can be reused as a stand-alone
piece of software. If UserBundle cannot be used *"as is"* in other Symfony
applications, then it shouldn't be its own bundle. Moreover, if InvoiceBundle
depends on ProductBundle, then there's no advantage to having two separate bundles.

.. best-practice::

    Don't create any bundle to organize your application logic.

Symfony applications can still use third-party bundles (installed in ``vendor/``)
to add features, but you should use PHP namespaces instead of bundles to organize
your own code.

----

Next: :doc:`/best_practices/configuration`

.. _`Composer`: https://getcomposer.org/
.. _`Symfony Flex`: https://github.com/symfony/flex
.. _`Symfony Skeleton`: https://github.com/symfony/skeleton
