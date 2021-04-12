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

#. Update your ``public/index.php`` file to add the code of the `public/index.php`_
   file provided by Symfony. If you've customized this file, make sure to keep
   those changes (but add the rest of the changes made by Symfony).

#. Update your ``bin/console`` file to add the code of the `bin/console`_ file
   provided by Symfony.

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
   and update your `phpunit.xml.dist file`_ so it loads the ``tests/bootstrap.php``
   file.

.. _`public/index.php`: https://github.com/symfony/recipes/blob/master/symfony/framework-bundle/5.2/public/index.php
.. _`bin/console`: https://github.com/symfony/recipes/blob/master/symfony/console/5.1/bin/console
.. _`comment on the top of .env`: https://github.com/symfony/recipes/blob/master/symfony/flex/1.0/.env
.. _`create a new .env.test`: https://github.com/symfony/recipes/blob/master/symfony/phpunit-bridge/3.3/.env.test
.. _`phpunit.xml.dist file`: https://github.com/symfony/recipes/blob/master/symfony/phpunit-bridge/3.3/phpunit.xml.dist
.. _`One further change to the recipe in January 2019`: https://github.com/symfony/recipes/pull/501
