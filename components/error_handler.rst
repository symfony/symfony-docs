.. index::
   single: Debug
   single: Error
   single: Exception
   single: Components; ErrorHandler

The ErrorHandler Component
==========================

    The ErrorHandler component provides tools to manage errors and ease
    debugging PHP code.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/error-handler

.. include:: /components/require_autoload.rst.inc

Usage
-----

The ErrorHandler component provides several tools to help you debug PHP code:

* An **error handler** that turns PHP errors into exceptions;
* An **exception handler** that turns uncaught PHP exceptions into nice PHP responses;
* A **debug class loader** that provides better errors when a class is not found.

Call this method (e.g. in your :ref:`front controller <architecture-front-controller>`)
to enable all these features in your application::

    // public/index.php
    use Symfony\Component\ErrorHandler\Debug;

    if ($_SERVER['APP_DEBUG']) {
        Debug::enable();
    }

    // ...

Keep reading this article to learn more about each feature, including how to
enable each of them separately.

.. caution::

    You should never enable the debug tools, except for the error handler, in a
    production environment as they might disclose sensitive information to the user.

Turning PHP Errors into Exceptions
----------------------------------

The :class:`Symfony\\Component\\ErrorHandler\\ErrorHandler` class catches PHP
errors and turns them into PHP's :phpclass:`ErrorException` objects, except for
fatal PHP errors, which are turned into Symfony's
:class:`Symfony\\Component\\ErrorHandler\\Exception\\FatalErrorException` objects.

If the application uses the FrameworkBundle, this error handler is enabled by
default in the :ref:`production environment <configuration-environments>`
because it generates better error logs.

Use the following code (e.g. in your :ref:`front controller <architecture-front-controller>`)
to enable this error handler::

    use Symfony\Component\ErrorHandler\ErrorHandler;

    ErrorHandler::register();

.. tip::

    If you want to get even better exception pages, install the
    :doc:`ErrorRenderer component </components/error_renderer>` too.

Catching PHP Function Errors and Turning Them into Exceptions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Consider the following example::

    $data = json_decode(file_get_contents($filename), true);
    $data['read_at'] = date($datetimeFormat);
    file_put_contents($filename, json_encode($data));

Most PHP core functions were written before exception handling was introduced,
so they return ``false`` or ``null`` in case of error instead of throwing an
exception. That's why you need to add something like these to check for errors::

    $content = @file_get_contents($filename);
    if (false === $content) {
        throw new \RuntimeException('Could not load file.');
    }

    // since PHP 7.3 json_decode() defines an option to throw JSON_THROW_ON_ERROR
    // but you need to enable that option explicitly
    $data = @json_decode($content, true);
    if (null === $data) {
        throw new \RuntimeException('File does not contain valid JSON.');
    }

    $datetime = @date($datetimeFormat);
    if (false === $datetime) {
        throw new \RuntimeException('Invalid datetime format.');
    }

To simplify this code, the :class:`Symfony\\Component\\ErrorHandler\\ErrorHandler`
class provides a :method:`Symfony\\Component\\ErrorHandler\\ErrorHandler::call`
method that throws an exception automatically when a PHP error occurs::

    $content = ErrorHandler::call('file_get_contents', $filename);

The first argument of ``call()`` is the name of the PHP function to execute and
the rest of arguments are passed to the PHP function. The result of the PHP
function is returned as the result of ``call()``.

You can pass any PHP callable as the first argument of ``call()``, so you can
wrap several function calls inside an anonymous function::

    $data = ErrorHandler::call(static function () use ($filename, $datetimeFormat) {
        // if any code executed inside this anonymous function fails, a PHP exception
        // will be thrown, even if the code uses the '@' PHP silence operator
        $data = json_decode(file_get_contents($filename), true);
        $data['read_at'] = date($datetimeFormat);
        file_put_contents($filename, json_encode($data));

        return $data;
    });

Debugging Uncaught PHP Exceptions
---------------------------------

The :class:`Symfony\\Component\\ErrorHandler\\ExceptionHandler` class catches
uncaught PHP exceptions and turns them into a nice response, so you can debug
them. It is useful in :ref:`debug mode <debug-mode>` to replace the default
PHP/XDebug output with something prettier and more useful.

If the :doc:`HttpFoundation component </components/http_foundation>` is
available, the handler returns a Symfony's
:class:`Symfony\\Component\\HttpFoundation\\Response` object. Otherwise, it returns
a generic PHP response.

Use the following code (e.g. in your :ref:`front controller <architecture-front-controller>`)
to enable this exception handler::

    use Symfony\Component\ErrorHandler\ExceptionHandler;

    ExceptionHandler::register();

.. tip::

    If you want to get even better exception pages, install the
    :doc:`ErrorRenderer component </components/error_renderer>` too.

.. _component-debug-class-loader:

Class Loading Debugger
----------------------

The :class:`Symfony\\Component\\ErrorHandler\\DebugClassLoader` class throws
more useful exceptions when a class isn't found by the registered autoloaders
(e.g. looks for typos in the class names and suggest the right class name).

In practice, this debugger looks for all registered autoloaders that implement a
``findFile()`` method and replaces them by its own method to find class files.

Use the following code (e.g. in your :ref:`front controller <architecture-front-controller>`)
to enable this class loading debugger::

    use Symfony\Component\ErrorHandler\DebugClassLoader;

    DebugClassLoader::enable();
