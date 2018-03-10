.. index::
   single: Debug
   single: Components; Debug

The Debug Component
===================

    The Debug component provides tools to ease debugging PHP code.

.. versionadded:: 2.3
    The Debug component was introduced in Symfony 2.3. Previously, the classes
    were located in the HttpKernel component.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/debug

Alternatively, you can clone the `<https://github.com/symfony/debug>`_ repository.

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Debug component provides several tools to help you debug PHP code.
Enabling them all is as easy as it can get::

    use Symfony\Component\Debug\Debug;

    Debug::enable();

The :method:`Symfony\\Component\\Debug\\Debug::enable` method registers an
error handler, an exception handler and
:ref:`a special class loader <component-debug-class-loader>`.

Read the following sections for more information about the different available
tools.

.. caution::

    You should never enable the debug tools in a production environment as
    they might disclose sensitive information to the user.

Enabling the Error Handler
--------------------------

The :class:`Symfony\\Component\\Debug\\ErrorHandler` class catches PHP errors
and converts them to exceptions (of class :phpclass:`ErrorException` or
:class:`Symfony\\Component\\Debug\\Exception\\FatalErrorException` for PHP
fatal errors)::

    use Symfony\Component\Debug\ErrorHandler;

    ErrorHandler::register();

Enabling the Exception Handler
------------------------------

The :class:`Symfony\\Component\\Debug\\ExceptionHandler` class catches
uncaught PHP exceptions and converts them to a nice PHP response. It is useful
in debug mode to replace the default PHP/XDebug output with something prettier
and more useful::

    use Symfony\Component\Debug\ExceptionHandler;

    ExceptionHandler::register();

.. note::

    If the :doc:`HttpFoundation component </components/http_foundation>` is
    available, the handler uses a Symfony Response object; if not, it falls
    back to a regular PHP response.

.. _component-debug-class-loader:

Debugging a Class Loader
------------------------

The :class:`Symfony\\Component\\Debug\\DebugClassLoader` attempts to
throw more helpful exceptions when a class isn't found by the registered
autoloaders. All autoloaders that implement a ``findFile()`` method are replaced
with a ``DebugClassLoader`` wrapper.

Using the ``DebugClassLoader`` is as easy as calling its static
:method:`Symfony\\Component\\Debug\\DebugClassLoader::enable` method::

    use Symfony\Component\Debug\DebugClassLoader;

    DebugClassLoader::enable();

.. _Packagist: https://packagist.org/packages/symfony/debug
