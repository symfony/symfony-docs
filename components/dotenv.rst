.. index::
   single: Dotenv
   single: Components; Dotenv

The Dotenv Component
====================

    The Dotenv Component parses ``.env`` files to make environment variables
    stored in them accessible via ``$_ENV`` or ``$_SERVER``.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/dotenv

.. include:: /components/require_autoload.rst.inc

Usage
-----

Sensitive information and environment-dependent settings should be defined as
environment variables (as recommended for `twelve-factor applications`_). Using
a ``.env`` file to store those environment variables eases development and CI
management by keeping them in one "standard" place and agnostic of the
technology stack you are using (nginx vs PHP built-in server for instance).

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

.. code-block:: terminal

    # .env
    DB_USER=root
    DB_PASS=pass

Access the value with ``$_ENV`` in your code::

    $dbUser = $_ENV['DB_USER'];
    // you can also use ``$_SERVER``

The ``load()`` method never overwrites existing environment variables. Use the
``overload()`` method if you need to overwrite them::

    // ...
    $dotenv->overload(__DIR__.'/.env');

As you're working with the Dotenv component you'll notice that you might want
to have different files depending on the environment you're working in. Typically
this happens for local development or Continuous Integration where you might
want to have different files for your ``test`` and ``dev`` environments.

You can use ``Dotenv::loadEnv()`` to ease this process::

    use Symfony\Component\Dotenv\Dotenv;

    $dotenv = new Dotenv();
    $dotenv->loadEnv(__DIR__.'/.env');

The Dotenv component will then look for the correct ``.env`` file to load
in the following order whereas the files loaded later override the variables
defined in previously loaded files:

#. If ``.env`` exists, it is loaded first. In case there's no ``.env`` file but a
   ``.env.dist``, this one will be loaded instead.
#. If one of the previously mentioned files contains the ``APP_ENV`` variable, the
   variable is populated and used to load environment-specific files hereafter. If
   ``APP_ENV`` is not defined in either of the previously mentioned files, ``dev`` is
   assumed for ``APP_ENV`` and populated by default.
#. If there's a ``.env.local`` representing general local environment variables it's loaded now.
#. If there's a ``.env.$env.local`` file, this one is loaded. Otherwise, it falls
   back to ``.env.$env``.

This might look complicated at first glance but it gives you the opportunity to
commit multiple environment-specific files that can then be adjusted to your
local environment. Given you commit ``.env``, ``.env.test`` and ``.env.dev`` to
represent different configuration settings for your environments, each of them
can be adjusted by using ``.env.local``, ``.env.test.local`` and
``.env.dev.local`` respectively.

.. note::

    ``.env.local`` is always ignored in ``test`` environment because tests should produce the
    same results for everyone.

You can adjust the variable defining the environment, default environment and test
environments by passing them as additional arguments to ``Dotenv::loadEnv()``
(see :method:`Symfony\\Component\\Dotenv\\Dotenv::loadEnv` for details).

You should never store a ``.env.local`` file in your code repository as it might
contain sensitive information; create a ``.env`` file (or multiple
environment-specific ones as shown above) with sensible defaults instead.

.. note::

    Symfony Dotenv can be used in any environment of your application:
    development, testing, staging and even production. However, in production
    it's recommended to configure real environment variables to avoid the
    performance overhead of parsing the ``.env`` file for every request.

As a ``.env`` file is a regular shell script, you can ``source`` it in your own
shell scripts:

.. code-block:: terminal

    source .env

Add comments by prefixing them with ``#``:

.. code-block:: terminal

    # Database credentials
    DB_USER=root
    DB_PASS=pass # This is the secret password

Use environment variables in values by prefixing variables with ``$``:

.. code-block:: terminal

    DB_USER=root
    DB_PASS=${DB_USER}pass # Include the user as a password prefix

.. note::

    The order is important when some env var depends on the value of other env
    vars. In the above example, ``DB_PASS`` must be defined after ``DB_USER``.
    Moreover, if you define multiple ``.env`` files and put ``DB_PASS`` first,
    its value will depend on the ``DB_USER`` value defined in other files
    instead of the value defined in this file.

Define a default value in case the environment variable is not set:

.. code-block:: terminal

    DB_USER=
    DB_PASS=${DB_USER:-root}pass # results in DB_PASS=rootpass

Embed commands via ``$()`` (not supported on Windows):

.. code-block:: terminal

    START_TIME=$(date)

.. note::

    Note that using ``$()`` might not work depending on your shell.

.. _twelve-factor applications: https://12factor.net/
