.. index::
   single: Bundles; Best Practices

Bundle Best Practices
=====================

A bundle is a directory that has a well-defined structure and can host anything
from classes to controllers and web resources. Even if bundles are very
flexible, you should follow some best practices if you want to distribute them.

.. index::
   pair: Bundles; Naming Conventions

Bundle Name
-----------

A bundle is also a PHP namespace. The namespace must follow the technical
interoperability `standards`_ for PHP 5.3 namespaces and class names: it
starts with a vendor segment, followed by zero or more category segments, and
it ends with the namespace short name, which must end with a ``Bundle``
suffix.

A namespace becomes a bundle as soon as you add a bundle class to it. The
bundle class name must follow these simple rules:

* Use only alphanumeric characters and underscores;
* Use a CamelCased name;
* Use a descriptive and short name (no more than 2 words);
* Prefix the name with the concatenation of the vendor (and optionally the
  category namespaces);
* Suffix the name with ``Bundle``.

Here are some valid bundle namespaces and class names:

=================================== ==============================
Namespace                           Bundle Class Name
=================================== ==============================
``Sensio\Bundle\BlogBundle``        ``SensioBlogBundle``
``Sensio\Bundle\Social\BlogBundle`` ``SensioSocialBlogBundle``
``Sensio\BlogBundle``               ``SensioBlogBundle``
=================================== ==============================

By convention, the ``getName()`` method of the bundle class should return the
class name.

Directory Structure
-------------------

The basic directory structure of a ``HelloBundle`` bundle must read as
follows:

.. code-block:: text

    XXX/...
        HelloBundle/
            HelloBundle.php
            Controller/
            Resources/
                meta/
                    LICENSE
                config/
                doc/
                    index.rst
                translations/
                views/
                public/
            Tests/

The ``XXX`` directory(ies) reflects the namespace structure of the bundle.

The following files are mandatory:

* ``HelloBundle.php``;
* ``Resources/meta/LICENSE``: The full license for the code;
* ``Resources/doc/index.rst``: The root file for the Bundle documentation.

.. note::

    These conventions ensure that automated tools can rely on this default
    structure to work.

The depth of sub-directories should be kept to the minimal for most used
classes and files (2 levels at a maximum). More levels can be defined for
non-strategic, less-used files.

The bundle directory is read-only. If you need to write temporary files, store
them under the ``cache/`` or ``log/`` directory of the host application. Tools
can generate files in the bundle directory structure, but only if the generated
files are going to be part of the repository.

The following classes and files have specific emplacements:

========================= ===========================
Type                      Directory
========================= ===========================
Controllers               ``Controller/``
Translation files         ``Resources/translations/``
Templates                 ``Resources/views/``
Unit and Functional Tests ``Tests/``
Web Resources             ``Resources/public/``
Configuration             ``Resources/config/``
Commands                  ``Command/``
========================= ===========================

Classes
-------

The bundle directory structure is used as the namespace hierarchy. For
instance, a ``HelloController`` controller is stored in
``Bundle/HelloBundle/Controller/HelloController.php`` and the fully qualified
class name is ``Bundle\HelloBundle\Controller\HelloController``.

All classes and files must follow the Symfony2 coding :doc:`standards
</contributing/code/standards>`.

Some classes should be seen as facades and should be as short as possible, like
Commands, Helpers, Listeners, and Controllers.

Classes that connects to the Event Dispatcher should be suffixed with
``Listener``.

Exceptions classes should be stored in an ``Exception`` sub-namespace.

Vendors
-------

A bundle must not embed third-party PHP libraries. It should rely on the
standard Symfony2 autoloading instead.

A bundle should not embed third-party libraries written in JavaScript, CSS, or
any other language.

Tests
-----

A bundle should come with a test suite written with PHPUnit and stored under
the ``Tests/`` directory. Tests should follow the following principles:

* The test suite must be executable with a simple ``phpunit`` command run from
  a sample application;
* The functional tests should only be used to test the response output and
  some profiling information if you have some;
* The code coverage should at least covers 95% of the code base.

.. note::
   A test suite must not contain ``AllTests.php`` scripts, but must rely on the
   existence of a ``phpunit.xml.dist`` file.

Documentation
-------------

All classes and functions must come with full PHPDoc.

Extensive documentation should also be provided in the :doc:`reStructuredText
</contributing/documentation/format>` format, under the ``Resources/doc/``
directory; the ``Resources/doc/index.rst`` file is the only mandatory file.

Controllers
-----------

Controllers in a bundle must not extend
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller`. They can
implement
:class:`Symfony\\Foundation\\DependencyInjection\\ContainerAwareInterface` or
extend :class:`Symfony\\Foundation\\DependencyInjection\\ContainerAware`
instead.

.. note::

    If you have a look at
    :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` methods,
    you will see that they are only nice shortcuts to ease the learning curve.

Templates
---------

If a bundle provides templates, they must use Twig. A bundle must not provide
a main layout, except if it provides a full working application.

Translation Files
-----------------

If a bundle provides message translations, they must be defined in the XLIFF
format; the domain should be named after the bundle name (``bundle.hello``).

A bundle must not override existing messages from another bundle.

Configuration
-------------

Configuration must be done via the Symfony2 built-in :doc:`mechanism
</guides/bundles/configuration>`. A bundle should provide all its default
configurations in XML.

.. _standards: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
