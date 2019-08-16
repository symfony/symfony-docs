.. index::
   single: Debug
   single: Error
   single: Exception
   single: Components; ErrorHandler

The ErrorHandler Component
==========================

    The ErrorHandler component provides tools to manage errors and ease debugging PHP code.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/error-handler

.. include:: /components/require_autoload.rst.inc

Usage
-----

The ErrorHandler component provides several tools to help you debug PHP code.
Enable all of them by calling this method::

    use Symfony\Component\ErrorHandler\Debug;

    Debug::enable();

The :method:`Symfony\\Component\\ErrorHandler\\Debug::enable` method registers an
error handler, an exception handler and
:ref:`a special class loader <component-debug-class-loader>`.

Read the following sections for more information about the different available
tools.

.. caution::

    You should never enable the debug tools, except for the error handler, in a
    production environment as they might disclose sensitive information to the user.

Handling PHP Errors and Exceptions
----------------------------------

Enabling the Error Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\ErrorHandler\\ErrorHandler` class catches PHP
errors and converts them to exceptions (of class :phpclass:`ErrorException` or
:class:`Symfony\\Component\\ErrorHandler\\Exception\\FatalErrorException` for
PHP fatal errors)::

    use Symfony\Component\ErrorHandler\ErrorHandler;

    ErrorHandler::register();

This error handler is enabled by default in the production environment when the
application uses the FrameworkBundle because it generates better error logs.

Enabling the Exception Handler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\ErrorHandler\\ExceptionHandler` class catches
uncaught PHP exceptions and converts them to a nice PHP response. It is useful
in :ref:`debug mode <debug-mode>` to replace the default PHP/XDebug output with
something prettier and more useful::

    use Symfony\Component\ErrorHandler\ExceptionHandler;

    ExceptionHandler::register();

.. note::

    If the :doc:`HttpFoundation component </components/http_foundation>` is
    available, the handler uses a Symfony Response object; if not, it falls
    back to a regular PHP response.

.. _component-debug-class-loader:

Debugging a Class Loader
------------------------

The :class:`Symfony\\Component\\ErrorHandler\\DebugClassLoader` attempts to
throw more helpful exceptions when a class isn't found by the registered
autoloaders. All autoloaders that implement a ``findFile()`` method are replaced
with a ``DebugClassLoader`` wrapper.

Using the ``DebugClassLoader`` is done by calling its static
:method:`Symfony\\Component\\ErrorHandler\\DebugClassLoader::enable` method::

    use Symfony\Component\ErrorHandler\DebugClassLoader;

    DebugClassLoader::enable();
