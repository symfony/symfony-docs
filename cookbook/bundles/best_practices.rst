.. index::
   single: Bundle; Best practices

Best Practices for Reusable Bundles
===================================

There are 2 types of bundles:

* Application-specific bundles: only used to build your application;
* Reusable bundles: meant to be shared across many projects.

This article is all about how to structure your **reusable bundles** so that
they're easy to configure and extend. Many of these recommendations do not
apply to application bundles because you'll want to keep those as simple
as possible. For application bundles, just follow the practices shown throughout
the book and cookbook.

.. seealso::

    The best practices for application-specific bundles are discussed in
    :doc:`/best_practices/introduction`.

.. index::
   pair: Bundle; Naming conventions

.. _bundles-naming-conventions:

Bundle Name
-----------

A bundle is also a PHP namespace. The namespace must follow the technical
interoperability `standards`_ for PHP namespaces and class names: it starts
with a vendor segment, followed by zero or more category segments, and it ends
with the namespace short name, which must end with a ``Bundle`` suffix.

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

    Symfony core Bundles do not prefix the Bundle class with ``Symfony``
    and always add a ``Bundle`` sub-namespace; for example:
    :class:`Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle`.

Each bundle has an alias, which is the lower-cased short version of the bundle
name using underscores (``acme_hello`` for ``AcmeHelloBundle``, or
``acme_social_blog`` for ``Acme\Social\BlogBundle`` for instance). This alias
is used to enforce uniqueness within a bundle (see below for some usage
examples).

Directory Structure
-------------------

The basic directory structure of a HelloBundle must read as follows:

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

All classes and files must follow the Symfony coding :doc:`standards </contributing/code/standards>`.

Some classes should be seen as facades and should be as short as possible, like
Commands, Helpers, Listeners, and Controllers.

Classes that connect to the event dispatcher should be suffixed with
``Listener``.

Exceptions classes should be stored in an ``Exception`` sub-namespace.

Vendors
-------

A bundle must not embed third-party PHP libraries. It should rely on the
standard Symfony autoloading instead.

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

Extensive documentation should also be provided in the
:doc:`reStructuredText </contributing/documentation/format>` format, under
the ``Resources/doc/`` directory; the ``Resources/doc/index.rst`` file is
the only mandatory file and must be the entry point for the documentation.

Installation Instructions
~~~~~~~~~~~~~~~~~~~~~~~~~

In order to ease the installation of third-party bundles, consider using the
following standardized instructions in your ``README.md`` file.

.. code-block:: text

    Installation
    ============

    Step 1: Download the Bundle
    ---------------------------

    Open a command console, enter your project directory and execute the
    following command to download the latest stable version of this bundle:

    ```bash
    $ composer require <package-name> "~1"
    ```

    This command requires you to have Composer installed globally, as explained
    in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
    of the Composer documentation.

    Step 2: Enable the Bundle
    -------------------------

    Then, enable the bundle by adding the following line in the `app/AppKernel.php`
    file of your project:

    ```php
    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new <vendor>\<bundle-name>\<bundle-long-name>(),
            );

            // ...
        }

        // ...
    }
    ```

This template assumes that your bundle is in its ``1.x`` version. If not, change
the ``"~1"`` installation version accordingly (``"~2"``, ``"~3"``, etc.)

Optionally, you can add more installation steps (*Step 3*, *Step 4*, etc.) to
explain other required installation tasks, such as registering routes or
dumping assets.

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
using the Symfony built-in mechanisms.

For simple configuration settings, rely on the default ``parameters`` entry of
the Symfony configuration. Symfony parameters are simple key/value pairs; a
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

Custom Validation Constraints
-----------------------------

Starting with Symfony 2.5, a new Validation API was introduced. In fact,
there are 3 modes, which the user can configure in their project:

* 2.4: the original 2.4 and earlier validation API;
* 2.5: the new 2.5 and later validation API;
* 2.5-BC: the new 2.5 API with a backwards-compatible layer so that the
  2.4 API still works. This is only available in PHP 5.3.9+.

As a bundle author, you'll want to support *both* API's, since some users
may still be using the 2.4 API. Specifically, if your bundle adds a violation
directly to the :class:`Symfony\\Component\\Validator\\Context\\ExecutionContext`
(e.g. like in a custom validation constraint), you'll need to check for which
API is being used. The following code, would work for *all* users::

    use Symfony\Component\Validator\ConstraintValidator;
    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\Context\ExecutionContextInterface;
    // ...

    class ContainsAlphanumericValidator extends ConstraintValidator
    {
        public function validate($value, Constraint $constraint)
        {
            if ($this->context instanceof ExecutionContextInterface) {
                // the 2.5 API
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $value)
                    ->addViolation()
                ;
            } else {
                // the 2.4 API
                $this->context->addViolation(
                    $constraint->message,
                    array('%string%' => $value)
                );
            }
        }
    }

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/bundles/extension`

.. _standards: http://www.php-fig.org/psr/psr-0/
