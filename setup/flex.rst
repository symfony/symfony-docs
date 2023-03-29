Upgrading Existing Applications to Symfony Flex
===============================================

Using Symfony Flex is optional, even in Symfony 4, where Flex is used by
default. However, Flex is so convenient and improves your productivity so much
that it's strongly recommended to upgrade your existing applications to it.

Symfony Flex recommends that applications use the following directory structure,
which is the same used by default in Symfony 4, but you can
:ref:`customize some directories <flex-customize-paths>`:

.. code-block:: text

    your-project/
    ├── assets/
    ├── bin/
    │   └── console
    ├── config/
    │   ├── bundles.php
    │   ├── packages/
    │   ├── routes.yaml
    │   └── services.yaml
    ├── public/
    │   └── index.php
    ├── src/
    │   ├── ...
    │   └── Kernel.php
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
   it still depends on the Symfony Standard Edition, which is no longer available
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
       +   },
       +   "conflict": {
       +       "symfony/symfony": "*"
             }
         }

   Now you must add in ``composer.json`` all the Symfony dependencies required
   by your project. A quick way to do that is to add all the components that
   were included in the previous ``symfony/symfony`` dependency and later you
   can remove anything you don't really need:

   .. code-block:: terminal

       $ composer require annotations asset orm twig \
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

#. Regardless of which of the previous steps you followed, at this point you'll have
   lots of new config files in ``config/``. They contain the default config
   defined by Symfony, so you must check your original files in ``app/config/``
   and make the needed changes in the new files. Flex config doesn't use suffixes
   in config files, so the old ``app/config/config_dev.yml`` goes to
   ``config/packages/dev/*.yaml``, etc.

#. The most important config file is ``app/config/services.yml``, which now is
   located at ``config/services.yaml``. Copy the contents of the
   `default services.yaml file`_ and then add your own service configuration.
   Later you can revisit this file because thanks to Symfony's
   :doc:`autowiring feature </service_container/autowiring>` you can remove
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

#. Move the original PHP source code files from ``src/AppBundle/*``, except bundle
   specific files (like ``AppBundle.php`` and ``DependencyInjection/``), to
   ``src/`` and update the namespace of each moved file to be ``App\...`` (advanced
   IDEs can do this automatically).

   In addition to moving the files, update the ``autoload`` and ``autoload-dev``
   values of the ``composer.json`` file as `shown in this example`_ to use
   ``App\`` and ``App\Tests\`` as the application namespaces.

   If you used multiple bundles to organize your code, you must reorganize your
   code into ``src/``. For example, if you had ``src/UserBundle/Controller/DefaultController.php``
   and ``src/ProductBundle/Controller/DefaultController.php``, you could move
   them to ``src/Controller/UserController.php`` and ``src/Controller/ProductController.php``.

#. Move the public assets, such as images or compiled CSS/JS files, from
   ``src/AppBundle/Resources/public/`` to ``public/`` (e.g. ``public/images/``).

#. Remove ``src/AppBundle/``.

#. Move the source of the assets (e.g. the SCSS files) to ``assets/`` and use
   :doc:`Webpack Encore </frontend>` to manage and compile them.

#. ``SYMFONY_DEBUG`` and ``SYMFONY_ENV`` environment variables were replaced by
   ``APP_DEBUG`` and ``APP_ENV``. Copy their values to the new vars and then remove
   the former ones.

#. Create the new ``public/index.php`` front controller
   `copying Symfony's index.php source`_ and, if you made any customization in
   your ``web/app.php`` and ``web/app_dev.php`` files, copy those changes into
   the new file. You can now remove the old ``web/`` dir.

#. Update the ``bin/console`` script `copying Symfony's bin/console source`_
   and changing anything according to your original console script.

#. Remove the ``bin/symfony_requirements`` script and if you need a replacement
   for it, use the new `Symfony Requirements Checker`_.

#. Update the ``.gitignore`` file to replace the existing ``var/logs/`` entry
   by ``var/log/``, which is the new name for the log directory.

.. _flex-customize-paths:

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

Learn more
----------

* :doc:`/setup/flex_private_recipes`

.. _`default services.yaml file`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/5.3/config/services.yaml
.. _`shown in this example`: https://github.com/symfony/skeleton/blob/a0770a7f26eeda9890a104fa3de8f68c4120fca5/composer.json#L30-L39
.. _`shown in this example of the skeleton-project`: https://github.com/symfony/skeleton/blob/a0770a7f26eeda9890a104fa3de8f68c4120fca5/composer.json#L55-L57
.. _`copying Symfony's index.php source`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/5.3/public/index.php
.. _`copying Symfony's bin/console source`: https://github.com/symfony/recipes/blob/master/symfony/console/5.3/bin/console
.. _`Symfony Requirements Checker`: https://github.com/symfony/requirements-checker
