.. index::
   single: Debug
   single: Components; Debug

The Debug Component
===================

    The Debug component provides tools to ease debugging PHP code.

Installation
------------

.. code-block:: terminal

    $ composer require --dev symfony/debug

.. include:: /components/require_autoload.rst.inc

Usage
-----

The Debug component provides several tools to help you debug PHP code.
Enable all of them by calling this method::

    use Symfony\Component\Debug\Debug;

    Debug::enable();

.. caution::

    You should never enable the debug tools, except for the error handler, in a
    production environment as they might disclose sensitive information to the user.

.. deprecated:: 4.4

    In Symfony versions before 4.4, this component also provided error and
    exception handlers. In Symfony 4.4 they were deprecated in favor of their
    equivalent handlers included in the new :doc:`ErrorCatcher component </components/error_catcher>`.

.. _component-debug-class-loader:

Debugging a Class Loader
------------------------

The :class:`Symfony\\Component\\Debug\\DebugClassLoader` attempts to
throw more helpful exceptions when a class isn't found by the registered
autoloaders. All autoloaders that implement a ``findFile()`` method are replaced
with a ``DebugClassLoader`` wrapper.

Using the ``DebugClassLoader`` is done by calling its static
:method:`Symfony\\Component\\Debug\\DebugClassLoader::enable` method::

    use Symfony\Component\Debug\DebugClassLoader;

    DebugClassLoader::enable();
