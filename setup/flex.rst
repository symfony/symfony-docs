.. index:: Flex

Using Symfony Flex to Manage Symfony Applications
=================================================

`Symfony Flex`_ is the new way to install and manage Symfony applications. Flex
is not a new Symfony version, but a tool that replaces and improves the
`Symfony Installer`_ and the `Symfony Standard Edition`_.

Symfony Flex **automates the most common tasks of Symfony applications**, like
installing and removing bundles and other Composer dependencies. Symfony
Flex works for Symfony 3.3 and higher. Starting from Symfony 4.0, Flex
should be used by default, but it is still optional.

How Does Flex Work
------------------

Symfony Flex is a Composer plugin that modifies the behavior of the
``require``, ``update``, and ``remove`` commands. When installing or removing
dependencies in a Flex-enabled application, Symfony can perform tasks before
and after the execution of Composer tasks.

Consider the following example:

.. code-block:: terminal

    $ cd my-project/
    $ composer require mailer

If you execute that command in a Symfony application which doesn't use Flex,
you'll see a Composer error explaining that ``mailer`` is not a valid package
name. However, if the application has Symfony Flex installed, that command ends
up installing and enabling the SwiftmailerBundle, which is the best way to
integrate Swiftmailer, the official mailer for Symfony applications.

When Symfony Flex is installed in the application and you execute ``composer
require``, the application makes a request to the Symfony Flex server before
trying to install the package with Composer.

* If there's no information about that package, the Flex server returns nothing and
  the package installation follows the usual procedure based on Composer;

* If there's special information about that package, Flex returns it in a file
  called a "recipe" and the application uses it to decide which package to
  install and which automated tasks to run after the installation.

In the above example, Symfony Flex asks about the ``mailer`` package and the
Symfony Flex server detects that ``mailer`` is in fact an alias for
SwiftmailerBundle and returns the "recipe" for it.

Flex keeps tracks of the recipes it installed in a ``symfony.lock`` file, which
must be committed to your code repository.

Symfony Flex Recipes
~~~~~~~~~~~~~~~~~~~~

Recipes are defined in a ``manifest.json`` file and can contain any number of
other files and directories. For example, this is the ``manifest.json`` for
SwiftmailerBundle:

.. code-block:: javascript

    {
        "bundles": {
            "Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle": ["all"]
        },
        "copy-from-recipe": {
            "config/": "%CONFIG_DIR%/"
        },
        "env": {
            "MAILER_URL": "smtp://localhost:25?encryption=&auth_mode="
        },
        "aliases": ["mailer", "mail"]
    }

The ``aliases`` option allows Flex to install packages using short and easy to
remember names (``composer require mailer`` vs
``composer require symfony/swiftmailer-bundle``). The ``bundles`` option tells
Flex in which environments this bundle should be enabled automatically (``all``
in this case). The ``env`` option makes Flex add new environment variables to
the application. Finally, the ``copy-from-recipe`` option allows the recipe to
copy files and directories into your application.

The instructions defined in this ``manifest.json`` file are also used by
Symfony Flex when uninstalling dependencies (e.g. ``composer remove mailer``)
to undo all changes. This means that Flex can remove the SwiftmailerBundle from
the application, delete the ``MAILER_URL`` environment variable and any other
file and directory created by this recipe.

Symfony Flex recipes are contributed by the community and they are stored in
two public repositories:

* `Main recipe repository`_, is a curated list of recipes for high quality and
  maintained packages. Symfony Flex only looks in this repository by default.

* `Contrib recipe repository`_, contains all the recipes created by the
  community. All of them are guaranteed to work, but their associated packages
  could be unmaintained. Symfony Flex will ask your permission before installing
  any of these recipes.

Read the `Symfony Recipes documentation`_ to learn everything about how to
create recipes for your own packages.

Using Symfony Flex in New Applications
--------------------------------------

Symfony has published a new "skeleton" project, which is a minimal Symfony
project recommended to create new applications. This "skeleton" already
includes Symfony Flex as a dependency. This means you can create a new Flex-enabled
Symfony application by executing the following command:

.. code-block:: terminal

    $ composer create-project symfony/skeleton my-project

.. note::

    The use of the Symfony Installer to create new applications is no longer
    recommended since Symfony 3.3. Use the Composer ``create-project`` command
    instead.

.. _upgrade-to-flex:

Upgrading Existing Applications to Flex
---------------------------------------

Using Symfony Flex is optional, even in Symfony 4, where Flex is used by
default. However, Flex is so convenient and improves your productivity so much
that it's strongly recommended to upgrade your existing applications to it.

The only caveat is that Symfony Flex requires that applications use the
following directory structure, which is the same used by default in Symfony 4:

.. code-block:: text

    your-project/
    ├── assets/
    ├── bin/
    │   └── console
    ├── config/
    │   ├── bundles.php
    │   ├── packages/
    │   ├── routes.yaml
    │   └── services.yaml
    ├── public/
    │   └── index.php
    ├── src/
    │   ├── ...
    │   └── Kernel.php
    ├── templates/
    ├── tests/
    ├── translations/
    ├── var/
    └── vendor/

This means that installing the ``symfony/flex`` dependency in your application
is not enough. You must also upgrade the directory structure to the one shown
above. There's no automatic tool to make this upgrade, so you must follow these
manual steps:

#. Install Flex as a dependency of your project:

   .. code-block:: terminal

       $ composer require symfony/flex

#. If the project's ``composer.json`` file contains ``symfony/symfony`` dependency,
   it still depends on the Symfony Standard edition, which is no longer available
   in Symfony 4. First, remove this dependency:

   .. code-block:: terminal

       $ composer remove symfony/symfony

   Now add the ``symfony/symfony`` package to the ``conflict`` section of the project's
   ``composer.json`` file as `shown in this example of the skeleton-project`_ so that
   it will not be installed again:

   .. code-block:: diff

       {
           "require": {
               "symfony/flex": "^1.0",
       +     },
       +     "conflict": {
       +         "symfony/symfony": "*"
           }
       }

   Now you must add in ``composer.json`` all the Symfony dependencies required
   by your project. A quick way to do that is to add all the components that
   were included in the previous ``symfony/symfony`` dependency and later you
   can remove anything you don't really need:

   .. code-block:: terminal

       $ composer require annotations asset orm-pack twig \
         logger mailer form security translation validator
       $ composer require --dev dotenv maker-bundle orm-fixtures profiler

#. If the project's ``composer.json`` file doesn't contain the ``symfony/symfony``
   dependency, it already defines its dependencies explicitly, as required by
   Flex. Reinstall all dependencies to force Flex to generate the
   configuration files in ``config/``, which is the most tedious part of the upgrade
   process:

   .. code-block:: terminal

       $ rm -rf vendor/*
       $ composer install

#. No matter which of the previous steps you followed. At this point, you'll have
   lots of new config files in ``config/``. They contain the default config
   defined by Symfony, so you must check your original files in ``app/config/``
   and make the needed changes in the new files. Flex config doesn't use suffixes
   in config files, so the old ``app/config/config_dev.yml`` goes to
   ``config/packages/dev/*.yaml``, etc.

#. The most important config file is ``app/config/services.yml``, which now is
   located at ``config/services.yaml``. Copy the contents of the
   `default services.yaml file`_ and then add your own service configuration.
   Later you can revisit this file because thanks to Symfony's
   :doc:`autowiring feature </service_container/3.3-di-changes>` you can remove
   most of the service configuration.

   .. note::

       Make sure that your previous configuration files don't have ``imports``
       declarations pointing to resources already loaded by ``Kernel::configureContainer()``
       or ``Kernel::configureRoutes()`` methods.

#. Move the rest of the ``app/`` contents as follows (and after that, remove the
   ``app/`` directory):

   * ``app/Resources/views/`` -> ``templates/``
   * ``app/Resources/translations/`` -> ``translations/``
   * ``app/Resources/<BundleName>/views/`` -> ``templates/bundles/<BundleName>/``
   * rest of ``app/Resources/`` files -> ``src/Resources/``

#. Move the original PHP source code from ``src/AppBundle/*``, except bundle
   specific files (like ``AppBundle.php`` and ``DependencyInjection/``), to
   ``src/``.

   In addition to moving the files, update the ``autoload`` and ``autoload-dev``
   values of the ``composer.json`` file as `shown in this example`_ to use
   ``App\`` and ``App\Tests\`` as the application namespaces (advanced IDEs can
   do this automatically).

   If you used multiple bundles to organize your code, you must reorganize your
   code into ``src/``. For example, if you had ``src/UserBundle/Controller/DefaultController.php``
   and ``src/ProductBundle/Controller/DefaultController.php``, you could move
   them to ``src/Controller/UserController.php`` and ``src/Controller/ProductController.php``.

#. Move the public assets, such as images or compiled CSS/JS files, from
   ``src/AppBundle/Resources/public/`` to ``public/`` (e.g. ``public/images/``).

#. Move the source of the assets (e.g. the SCSS files) to ``assets/`` and use
   :doc:`Webpack Encore </frontend>` to manage and compile them.

#. Create the new ``public/index.php`` front controller
   `copying Symfony's index.php source`_ and, if you made any customization in
   your ``web/app.php`` and ``web/app_dev.php`` files, copy those changes into
   the new file. You can now remove the old ``web/`` dir.

#. Update the ``bin/console`` script `copying Symfony's bin/console source`_
   and changing anything according to your original console script.

#. Remove ``src/AppBundle/``.

#. Remove the ``bin/symfony_requirements`` script and if you need a replacement
   for it, use the new `Symfony Requirements Checker`_.

#. Update the ``.gitignore`` file to replace the existing ``var/logs/`` entry
   by ``var/log/``, which is the new name for the log directory.

Customizing Flex Paths
----------------------

The Flex recipes make a few assumptions about your project's directory structure.
Some of these assumptions can be customized by adding a key under the ``extra``
section of your ``composer.json`` file. For example, to tell Flex to copy any
PHP classes into ``src/App`` instead of ``src``:

.. code-block:: json

    {
        "...": "...",

        "extra": {
            "src-dir": "src/App"
        }
    }

The configurable paths are:

* ``bin-dir``: defaults to ``bin/``
* ``config-dir``: defaults to ``config/``
* ``src-dir`` defaults to ``src/``
* ``var-dir`` defaults to ``var/``
* ``public-dir`` defaults to ``public/``

If you customize these paths, some files copied from a recipe still may contain
references to the original path. In other words: you may need to update some things
manually after a recipe is installed.

.. _`Symfony Flex`: https://github.com/symfony/flex
.. _`Symfony Installer`: https://github.com/symfony/symfony-installer
.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard
.. _`Main recipe repository`: https://github.com/symfony/recipes
.. _`Contrib recipe repository`: https://github.com/symfony/recipes-contrib
.. _`Symfony Recipes documentation`: https://github.com/symfony/recipes/blob/master/README.rst
.. _`default services.yaml file`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/3.3/config/services.yaml
.. _`shown in this example`: https://github.com/symfony/skeleton/blob/8e33fe617629f283a12bbe0a6578bd6e6af417af/composer.json#L24-L33
.. _`shown in this example of the skeleton-project`: https://github.com/symfony/skeleton/blob/8e33fe617629f283a12bbe0a6578bd6e6af417af/composer.json#L44-L46
.. _`copying Symfony's index.php source`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/3.3/public/index.php
.. _`copying Symfony's bin/console source`: https://github.com/symfony/recipes/blob/master/symfony/console/3.3/bin/console
.. _`Symfony Requirements Checker`: https://github.com/symfony/requirements-checker
