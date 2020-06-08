Nov 2018 Changes to .env & How to Update
========================================

In November 2018, several changes were made to the core Symfony *recipes* related
to the ``.env`` file. These changes make working with environment variables easier
and more consistent - especially when writing functional tests.

If your app was started before November 2018, your app **does not require any changes
to keep working**. However, if/when you are ready to take advantage of these improvements,
you will need to make a few small updates.

What Changed Exactly?
---------------------

But first, what changed? On a high-level, not much. Here's a summary of the most
important changes:

* A) The ``.env.dist`` file no longer exists. Its contents should be moved to your
  ``.env`` file (see the next point).

* B) The ``.env`` file **is** now committed to your repository. It was previously ignored
  via the ``.gitignore`` file (the updated recipe does not ignore this file). Because
  this file is committed, it should contain non-sensitive, default values. The
  ``.env`` can be seen as the previous ``.env.dist`` file.

* C) A ``.env.local`` file can now be created to *override* values in ``.env`` for
  your machine. This file is ignored in the new ``.gitignore``.

* D) When testing, your ``.env`` file is now read, making it consistent with all
  other environments. You can also create a ``.env.test`` file for test-environment
  overrides.

* E) `One further change to the recipe in January 2019`_ means that your ``.env``
  files are *always* loaded, even if you set an ``APP_ENV=prod`` environment
  variable. The purpose is for the ``.env`` files to define default values that
  you can override if you want to with real environment values.

There are a few other improvements, but these are the most important. To take advantage
of these, you *will* need to modify a few files in your existing app.

Updating My Application
-----------------------

If you created your application after November 15th 2018, you don't need to make
any changes! Otherwise, here is the list of changes you'll need to make - these
changes can be made to any Symfony 3.4 or higher app:

#. Create a new `config/bootstrap.php`_ file in your project. This file loads Composer's
   autoloader and loads all the ``.env`` files as needed (note: in an earlier recipe,
   this file was called ``src/.bootstrap.php``; if you are upgrading from Symfony 3.3
   or 4.1, use the `3.3/config/bootstrap.php`_ file instead).

#. Update your `public/index.php`_ (`index.php diff`_) file to load the new ``config/bootstrap.php``
   file. If you've customized this file, make sure to keep those changes (but use
   the rest of the changes).

#. Update your `bin/console`_ file to load the new ``config/bootstrap.php`` file.

#. Update ``.gitignore``:

   .. code-block:: diff

       # .gitignore
       # ...

       ###> symfony/framework-bundle ###
       - /.env
       + /.env.local
       + /.env.local.php
       + /.env.*.local

       # ...

#. Rename ``.env`` to ``.env.local`` and ``.env.dist`` to ``.env``:

   .. code-block:: terminal

       # Unix
       $ mv .env .env.local
       $ git mv .env.dist .env

       # Windows
       C:\> move .env .env.local
       C:\> git mv .env.dist .env

   You can also update the `comment on the top of .env`_ to reflect the new changes.

#. If you're using PHPUnit, you will also need to `create a new .env.test`_ file
   and update your `phpunit.xml.dist file`_ so it loads the ``config/bootstrap.php``
   file.

.. _`config/bootstrap.php`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/4.2/config/bootstrap.php
.. _`3.3/config/bootstrap.php`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/3.3/config/bootstrap.php
.. _`public/index.php`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/4.2/public/index.php
.. _`index.php diff`: https://github.com/symfony/recipes/compare/8a4e5555e30d5dff64275e2788a901f31a214e79...86e2b6795c455f026e5ab0cba2aff2c7a18511f7#diff-7d73eabd1e5eb7d969ddf9a7ce94f954
.. _`bin/console`: https://github.com/symfony/recipes/blob/master/symfony/console/3.3/bin/console
.. _`comment on the top of .env`: https://github.com/symfony/recipes/blob/master/symfony/flex/1.0/.env
.. _`create a new .env.test`: https://github.com/symfony/recipes/blob/master/symfony/phpunit-bridge/3.3/.env.test
.. _`phpunit.xml.dist file`: https://github.com/symfony/recipes/blob/master/symfony/phpunit-bridge/3.3/phpunit.xml.dist
.. _`One further change to the recipe in January 2019`: https://github.com/symfony/recipes/pull/501
