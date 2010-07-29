Bundle Configuration
====================

To provide more flexibility, a bundle can provide configurable settings by
using the Symfony2 built-in mechanisms.

Simple Configuration
--------------------

For simple configuration settings, rely on the default ``parameters`` entry of
the Symfony2 configuration. Symfony2 parameters are simple key/value pairs; a
value being any valid PHP value. Each parameter name must start with a
lower-cased version of the bundle name (``hello`` for ``HelloBundle``, or
``sensio.social.blog`` for ``Sensio\Social\BlogBundle`` for instance).

The end user can provide values in any XML/YAML/INI configuration file:

.. code-block:: xml

    <!-- XML format -->
    <parameters>
        <parameter key="hello.email.from">fabien@example.com</parameter>
    </parameters>

.. code-block:: yaml

    parameters:
        hello.email.from: fabien@example.com

.. code-block:: ini

    [parameters]
    hello.email.from=fabien@example.com

Retrieve the configuration parameters in your code from the container::

    $container->getParameter('hello.email.from');

Even if this mechanism is simple enough, you are highly encouraged to use the
semantic configuration described below.

Semantic Configuration
----------------------

Semantic configuration provides an even more flexible way to provide
configuration for a bundle with the following advantages over simple
parameters:

* Possibility to define more than just configuration (services for
  instance);
* Better hierarchy in the configuration (you can define nested
  configuration);
* Smart merging when several configuration files override an existing
  configuration;
* Configuration validation (if you define an XSD file and use XML);
* Completion when you use XSD and XML.

To define a semantic configuration, create a Dependency Injection extension::

    // HelloBundle/DependencyInjection/HelloExtension.php
    class DocExtension extends LoaderExtension
    {
        public function configLoad($config)
        {
            // ...
        }

        public function getXsdValidationBasePath()
        {
            return __DIR__.'/../Resources/config/';
        }

        public function getNamespace()
        {
            return 'http://www.example.com/symfony/schema/';
        }

        public function getAlias()
        {
            return 'hello';
        }
    }

Follow these rules:

* The extension must be stored in the ``DependencyInjection`` sub-namespace;
* The extension must be named after the bundle name and suffixed with
  ``Extension`` (``HelloExtension`` for ``HelloBundle``);
* The alias must be unique and named after the bundle name (``hello`` for
  ``HelloBundle`` or ``sensio.social.blog`` for ``Sensio\Social\BlogBundle``);
* The extension should provide an XSD schema.

Eventually, register the extension::

    class HelloBundle extends BaseBundle
    {
        public function buildContainer(ContainerInterface $container)
        {
            Loader::registerExtension(new HelloExtension());
        }
    }

Naming Conventions
------------------

All parameter and service names starting with a ``_`` are reserved for the
framework, and new ones must not be defined by bundles.
