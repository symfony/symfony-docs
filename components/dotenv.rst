.. index::
   single: Dotenv
   single: Components; Dotenv

The Dotenv Component
====================

    The Dotenv Component parses ``.env`` files to make environment variables
    stored in them accessible via ``getenv()``, ``$_ENV`` or ``$_SERVER``.

.. versionadded:: 3.3
    The Dotenv component was introduced in Symfony 3.3.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/dotenv`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/dotenv).

.. include:: /components/require_autoload.rst.inc

Usage
-----

Sensitive information and environment-dependent settings should be defined as
environment variables (as recommended for `twelve-factor applications`_). Using
a ``.env`` file to store those environment variables eases development and CI
management by keeping them in one "standard" place and agnostic of the
technology stack you are using (Nginx vs PHP built-in server for instance).

.. note::

    PHP has a lot of different implementations of this "pattern". This
    implementation's goal is to replicate what ``source .env`` would do. It
    tries to be as similar as possible with the standard shell's behavior (so
    no value validation for instance).

Load a ``.env`` file in your PHP application via ``Dotenv::load()``::

    use Symfony\Component\Dotenv\Dotenv;

    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/.env');

    // You can also load several files
    $dotenv->load(__DIR__.'/.env', __DIR__.'/.env.dev');

Given the following ``.env`` file content:

.. code-block:: shell

    # .env
    DB_USER=root
    DB_PASS=pass

Access the value with ``getenv()`` in your code::

    $dbUser = getenv('DB_USER');
    // you can also use ``$_ENV`` or ``$_SERVER``

.. note::

    Symfony Dotenv never overwrites existing environment variables.

You should never store a ``.env`` file in your code repository as it might
contain sensitive information; create a ``.env.dist`` file with sensible
defaults instead.

Symfony Dotenv should only be used in development/testing/staging environments.
For production environments, use "real" environment variables.

As a ``.env`` file is a regular shell script, you can ``source`` it in your own
shell scripts:

.. code-block:: terminal

    $ source .env

Add comments by prefixing them with ``#``:

.. code-block:: shell

    # Database credentials
    DB_USER=root
    DB_PASS=pass # This is the secret password

Use environment variables in values by prefixing variables with ``$``:

.. code-block:: shell

    DB_USER=root
    DB_PASS=${DB_USER}pass # Include the user as a password prefix

.. note::

    When using the Dotenv component within the Symfony Framework, beware that
    variables can't contain container parameters because they are not resolved:

    .. code-block:: shell

        # '%kernel.project_dir%' is not resolved, so the value of this
        # variable won't be the expected one and the application won't work
        DATABASE_URL=sqlite:///%kernel.project_dir%/test.db

Embed commands via ``$()`` (not supported on Windows):

.. code-block:: shell

    START_TIME=$(date)

.. note::

    Note that using ``$()`` might not work depending on your shell.

.. _Packagist: https://packagist.org/packages/symfony/dotenv
.. _twelve-factor applications: http://www.12factor.net/
