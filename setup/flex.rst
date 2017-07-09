.. index:: Flex

Using Symfony Flex to Manage Symfony Applications
=================================================

`Symfony Flex`_ is the new way to install and manage Symfony applications. Flex
is not a new Symfony version, but a tool that replaces and improves the
`Symfony Installer`_.

Symfony Flex **automates the most common tasks of Symfony applications**, such
as installing and removing bundles and other dependencies. Symfony Flex works
for Symfony 3.3 and newer versions. Starting from Symfony 4.0, Flex will be used
by default, but it will still be optional to use.

How Does Flex Work
------------------

Internally, Symfony Flex is a Composer plugin that modifies the behavior of the
``require`` and ``update`` commands. When installing or updating dependencies
in a Flex-enabled application, Symfony can perform tasks before and after the
execution of Composer tasks.

Consider the following example:

.. code-block:: terminal

    $ cd my-project/
    $ composer require mailer

If you execute that command in a Symfony application which doesn't use Flex,
you'll see a Composer error explaining that ``mailer`` is not a valid package
name. However, if the application has Symfony Flex installed, that command ends
up installing and enabling the SwiftmailerBundle, which is the best way to
integrate Swiftmailer, the official mailer for Symfony applications.

When Symfony Flex is installed in the application and you execute ``composer require``,
the application makes a request to Symfony Flex servers before trying to install
the package with Composer:

* If there's no information about that package, Flex server returns nothing and
  the package installation follows the usual procedure based on Composer;
* If there's special information about that package, Flex returns it in a file
  called "recipe" and the application uses it to decide which package to install
  and which automated tasks to run after the installation.

In the above example, Symfony Flex asks about the ``mailer`` package and the
Symfony Flex server detects that ``mailer`` is in fact an alias for SwiftmailerBundle
and returns the "recipe" for it.

Symfony Flex Recipes
~~~~~~~~~~~~~~~~~~~~

Recipes are defined in a ``manifest.json`` file and can contain any number of
other files and directories. For example, this is the ``manifest.json`` for SwiftmailerBundle:

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

The ``alias`` option allows Flex to install packages using short and easy to
remember names (``composer require mailer`` vs ``composer require symfony/swiftmailer-bundle``).
The ``bundles`` option tells Flex in which environments should this bundle be
enabled automatically (``all`` in this case). The ``env`` option makes Flex to
add new environment variables to the application. Finally, the ``copy-from-recipe``
option allows the recipe to copy files and directories into your application.

The instructions defined in this ``manifest.json`` file are also used by Symfony
Flex when uninstalling dependencies (e.g. ``composer remove mailer``) to undo
all changes. This means that Flex can remove the SwiftmailerBundle from the
application, delete the ``MAILER_URL`` environment variable and any other file
and directory created by this recipe.

Symfony Flex recipes are contributed by the community and they are stored in
two public repositories:

* `Main recipe repository`_, is a curated list of recipes for high quality and
  maintained packages. Symfony Flex only looks for in this repository by default.
* `Contrib recipe repository`_, contains all the recipes created by the community.
  All of them are guaranteed to work, but their associated packages could be
  unmaintained. Symfony Flex ignores these recipes by default, but you can execute
  this command to start using them in your project:

  .. code-block:: terminal

        $ cd your-project/
        $ composer config extra.symfony.allow-contrib true

Read the `Symfony Recipes documentation`_ to learn everything about how to
created recipes for your own packages.

Using Symfony Flex in New Applications
--------------------------------------

Symfony has published a new "skeleton" project, which is a minimal Symfony
project recommended to create new applications. This "skeleton" already includes
Symfony Flex as a dependency, so you can create a new Flex-enabled Symfony
application executing the following command:

.. code-block:: terminal

    $ composer create-project symfony/skeleton my-project

.. note::

    The use of the Symfony Installer to create new applications is no longer
    recommended since Symfony 3.3. Use Composer ``create-project`` command instead.

Upgrading Existing Applications to Flex
---------------------------------------

Using Symfony Flex is optional, even in Symfony 4, where Flex will be used by
default. However, Flex is so convenient and improves your productivity so much
that it's strongly recommended to upgrade your existing applications to it.

The only caveat is that Symfony Flex requires that applications use the
following directory structure, which is the same used by default in Symfony 4:

.. code-block:: text

    your-project/
    ├── Makefile
    ├── config/
    │   ├── bundles.php
    │   ├── container.yaml
    │   ├── packages/
    │   └── routing.yaml
    ├── public/
    │   └── index.php
    ├── src/
    │   ├── ...
    │   └── Kernel.php
    ├── templates/
    └── vendor/

This means that installing the ``symfony/flex`` dependency in your application
is not enough. You must also upgrade the directory structure to the one showed
above. Sadly, there's no automatic tool to make this upgrade, so you must follow
these manual steps:

1. Create a new empty Symfony application (``composer create-project symfony/skeleton my-project-flex``)
2. Copy the ``require`` and ``require-dev`` dependencies defined in your original
   project's ``composer.json`` file to the ``composer.json`` file of the new project.
3. Install the dependencies in the new project executing ``composer install``. This
   will make Symfony Flex generate all the configuration files in ``config/packages/``
4. Review the generated ``config/packages/*.yaml`` files and make any needed
   changes according to the configuration defined in the ``app/config/config_*.yml``
   file of your original project. Beware that this is the most time-consuming
   and error-prone step of the upgrade process.
5. Move the original parameters defined in ``app/config/parameters.*.yml`` to the
   new ``config/container.yaml`` and ``.env`` files depending on your needs.
6. Move the original source code from ``src/{App,...}Bundle/`` to ``src/`` and
   update the namespaces of every PHP file (advanced IDEs can do this automatically).
7. Move the original templates from ``app/Resources/views/`` to ``templates/``
8. Make any other change needed by your application. For example, if your original
   ``web/app_*.php`` front controllers were customized, add those changes to the
   new ``public/index.php`` controller.

.. _`Symfony Flex`: https://github.com/symfony/flex
.. _`Symfony Installer`: https://github.com/symfony/symfony-installer
.. _`Main recipe repository`: https://github.com/symfony/recipes
.. _`Contrib recipe repository`: https://github.com/symfony/recipes-contrib
.. _`Symfony Recipes documentation`: https://github.com/symfony/recipes/blob/master/README.rst
