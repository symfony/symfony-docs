.. index::
   single: Bundle; Best practices

How to use Best Practices for Structuring Bundles
=================================================

A bundle is a directory that has a well-defined structure and can host anything
from classes to controllers and web resources. Even if bundles are very
flexible, you should follow some best practices if you want to distribute them.

.. index::
   pair: Bundle; Naming conventions

.. _bundles-naming-conventions:

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

+-----------------------------------+--------------------------+
| Namespace                         | Bundle Class Name        |
+===================================+==========================+
| ``Acme\Bundle\BlogBundle``        | ``AcmeBlogBundle``       |
+-----------------------------------+--------------------------+
| ``Acme\Bundle\Social\BlogBundle`` | ``AcmeSocialBlogBundle`` |
+-----------------------------------+--------------------------+
| ``Acme\BlogBundle``               | ``AcmeBlogBundle``       |
+-----------------------------------+--------------------------+

By convention, the ``getName()`` method of the bundle class should return the
class name.

.. note::

    If you share your bundle publicly, you must use the bundle class name as
    the name of the repository (``AcmeBlogBundle`` and not ``BlogBundle``
    for instance).

.. note::

    Symfony2 core Bundles do not prefix the Bundle class with ``Symfony``
    and always add a ``Bundle`` subnamespace; for example:
    :class:`Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle`.

Each bundle has an alias, which is the lower-cased short version of the bundle
name using underscores (``acme_hello`` for ``AcmeHelloBundle``, or
``acme_social_blog`` for ``Acme\Social\BlogBundle`` for instance). This alias
is used to enforce uniqueness within a bundle (see below for some usage
examples).

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

+------------------------------+-----------------------------+
| Type                         | Directory                   |
+==============================+=============================+
| Commands                     | ``Command/``                |
+------------------------------+-----------------------------+
| Controllers                  | ``Controller/``             |
+------------------------------+-----------------------------+
| Service Container Extensions | ``DependencyInjection/``    |
+------------------------------+-----------------------------+
| Event Listeners              | ``EventListener/``          |
+------------------------------+-----------------------------+
| Configuration                | ``Resources/config/``       |
+------------------------------+-----------------------------+
| Web Resources                | ``Resources/public/``       |
+------------------------------+-----------------------------+
| Translation files            | ``Resources/translations/`` |
+------------------------------+-----------------------------+
| Templates                    | ``Resources/views/``        |
+------------------------------+-----------------------------+
| Unit and Functional Tests    | ``Tests/``                  |
+------------------------------+-----------------------------+

.. note::

    When building a reusable bundle, model classes should be placed in the
    ``Model`` namespace. See :doc:`/cookbook/doctrine/mapping_model_classes` for
    how to handle the mapping with a compiler pass.

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

Classes that connect to the Event Dispatcher should be suffixed with
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
* The tests should cover at least 95% of the code base.

.. note::
   A test suite must not contain ``AllTests.php`` scripts, but must rely on the
   existence of a ``phpunit.xml.dist`` file.

Documentation
-------------

All classes and functions must come with full PHPDoc.

Extensive documentation should also be provided in the :doc:`reStructuredText
</contributing/documentation/format>` format, under the ``Resources/doc/``
directory; the ``Resources/doc/index.rst`` file is the only mandatory file and
must be the entry point for the documentation.

Controllers
-----------

As a best practice, controllers in a bundle that's meant to be distributed
to others must not extend the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` base class.
They can implement
:class:`Symfony\\Component\\DependencyInjection\\ContainerAwareInterface` or
extend :class:`Symfony\\Component\\DependencyInjection\\ContainerAware`
instead.

.. note::

    If you have a look at
    :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` methods,
    you will see that they are only nice shortcuts to ease the learning curve.

Routing
-------

If the bundle provides routes, they must be prefixed with the bundle alias.
For an AcmeBlogBundle for instance, all routes must be prefixed with
``acme_blog_``.

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

To provide more flexibility, a bundle can provide configurable settings by
using the Symfony2 built-in mechanisms.

For simple configuration settings, rely on the default ``parameters`` entry of
the Symfony2 configuration. Symfony2 parameters are simple key/value pairs; a
value being any valid PHP value. Each parameter name should start with the
bundle alias, though this is just a best-practice suggestion. The rest of the
parameter name will use a period (``.``) to separate different parts (e.g.
``acme_hello.email.from``).

The end user can provide values in any configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            acme_hello.email.from: fabien@example.com

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="acme_hello.email.from">fabien@example.com</parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('acme_hello.email.from', 'fabien@example.com');

    .. code-block:: ini

        ; app/config/config.ini
        [parameters]
        acme_hello.email.from = fabien@example.com

Retrieve the configuration parameters in your code from the container::

    $container->getParameter('acme_hello.email.from');

Even if this mechanism is simple enough, you are highly encouraged to use the
semantic configuration described in the cookbook.

.. note::

    If you are defining services, they should also be prefixed with the bundle
    alias.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/bundles/extension`

.. _standards: http://symfony.com/PSR0
