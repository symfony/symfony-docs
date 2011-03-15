.. index::
   single: Internals

Internals Overview
==================

Looks like you want to understand how Symfony2 works and how to extend it.
That makes me very happy! This section is an in-depth explanation of the
Symfony2 internals.

.. note::

    You need to read this section only if you want to understand how Symfony2
    works behind the scene, or if you want to extend Symfony2.

The Symfony2 code is made of several independent layers. Each layer is built
on top of the previous one.

.. tip::

    Autoloading is not managed by the framework directly; it's done
    independently with the help of the
    :class:`Symfony\\Component\\HttpFoundation\\UniversalClassLoader` class
    and the ``src/autoload.php`` file. Read the :doc:`dedicated chapter
    </cookbook/tools/autoloader>` for more information.

``HttpFoundation`` Component
----------------------------

The deepest level is the :namespace:`Symfony\\Component\\HttpFoundation`
component. HttpFoundation provides the main objects needed to deal with HTTP.
It is an Object-Oriented abstraction of some native PHP functions and
variables:

* The :class:`Symfony\\Component\\HttpFoundation\\Request` class abstracts
  the main PHP global variables like ``$_GET``, ``$_POST``, ``$_COOKIE``,
  ``$_FILES``, and ``$_SERVER``;

* The :class:`Symfony\\Component\\HttpFoundation\\Response` class abstracts
  some PHP functions like ``header()``, ``setcookie()``, and ``echo``;

* The :class:`Symfony\\Component\\HttpFoundation\\Session` class and
  :class:`Symfony\\Component\\HttpFoundation\\SessionStorage\\SessionStorageInterface`
  interface abstract session management ``session_*()`` functions.

.. seealso::

    Read more about the :doc:`HttpFoundation <http_foundation>` component.

``HttpKernel`` Component
------------------------

On top of HttpFoundation is the :namespace:`Symfony\\Component\\HttpKernel`
component. HttpKernel handles the dynamic part of HTTP; it is a thin wrapper
on top of the Request and Response classes to standardize the way requests are
handled. It also provides extension points and tools that makes it the ideal
starting point to create a Web framework without too much overhead.

It also optionally adds configurability and extensibility, thanks to the
Dependency Injection component and a powerful plugin system (bundles).

.. seealso::

    Read more about the :doc:`HttpKernel <kernel>` component. Read more about
    :doc:`Dependency Injection </book/service_container>` and :doc:`Bundles
    </book/bundles>`.

``FrameworkBundle`` Bundle
--------------------------

The :namespace:`Symfony\\Bundle\\FrameworkBundle` bundle is the bundle that
ties the main components and libraries together to make a lightweight and fast
MVC framework. It comes with a sensible default configuration and conventions
to ease the learning curve.
