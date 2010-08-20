.. index::
   single: Bundles; Configuration

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

The end user can provide values in any configuration file:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        parameters:
            hello.email.from: fabien@example.com

    .. code-block:: xml

        <!-- hello/config/config.xml -->
        <parameters>
            <parameter key="hello.email.from">fabien@example.com</parameter>
        </parameters>

    .. code-block:: php

        // hello/config/config.php
        $container->setParameter('hello.email.from', 'fabien@example.com');

    .. code-block:: ini

        [parameters]
        hello.email.from = fabien@example.com

Retrieve the configuration parameters in your code from the container::

    $container->getParameter('hello.email.from');

Even if this mechanism is simple enough, you are highly encouraged to use the
semantic configuration described below.

.. index::
   single: Configuration; Semantic
   single: Bundle; Extension Configuration

Semantic Configuration
----------------------

Semantic configuration provides an even more flexible way to provide
configuration for a bundle with the following advantages over simple
parameters:

* Possibility to define more than just parameters (services for instance);

* Better hierarchy in the configuration (you can define nested
  configurations);

* Smart merging when several configuration files override an existing
  configuration;

* Configuration validation (if you define an XSD file and use XML);

* Completion when you use XSD and XML.

.. index::
   single: Bundles; Extension
   single: Dependency Injection, Extension

Creating an Extension
~~~~~~~~~~~~~~~~~~~~~

To define a semantic configuration, create a Dependency Injection extension
that extend
:class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension`::

    // HelloBundle/DependencyInjection/HelloExtension.php
    use Symfony\\Component\\DependencyInjection\\Extension\\Extension;

    class HelloExtension extends Extension
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

The previous class define a ``hello:config`` namespace, usable in any
configuration file:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        hello.config: ~

    .. code-block:: xml

        <!-- hello/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://www.symfony-project.org/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

        <hello:config />

    .. code-block:: php

        // hello/config/config.php
        $container->loadFromExtension('hello', 'config', array());

.. note::
   You can create as many ``xxxLoad()`` methods as you want to define more
   configuration blocks for your extension.

Parsing a Configuration
~~~~~~~~~~~~~~~~~~~~~~~

Whenever a user include the ``hello.config`` namespace in a configuration
file, the ``configLoad()`` method of your extension is called and the
configuration is passed as an array (Symfony2 automatically converts XML and
YAML to an array).

So, given the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # hello/config/config.yml
        hello.config:
            foo: foo
            bar: bar

    .. code-block:: xml

        <!-- hello/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://www.symfony-project.org/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

        <hello:config foo="foo">
            <hello:bar>foo</hello:bar>
        </hello:config>

    .. code-block:: php

        // hello/config/config.php
        $container->loadFromExtension('hello', 'config', array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));

The array passed to your method looks like the following::

    array(
        'foo' => 'foo',
        'bar' => 'bar',
    )

Within ``configLoad()``, the ``$container`` variable refers to a container
that only knows about this namespace configuration. You can manipulate it the
way you want to add services and parameters. The first time the method is
called, the container only knows about global parameters. For subsequent
calls, it contains the configuration as defined by previous calls. So, the
method needs to merge new configuration settings with old ones::

    // only load default services and parameters once
    if (!$container->hasDefinition('xxxxx')) {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('hello.xml');
    }

The global parameters are the following:

* ``kernel.name``
* ``kernel.environment``
* ``kernel.debug``
* ``kernel.root_dir``
* ``kernel.cache_dir``
* ``kernel.logs_dir``
* ``kernel.bundle_dirs``
* ``kernel.bundles``
* ``kernel.charset``

.. caution::
    All parameter and service names starting with a ``_`` are reserved for the
    framework, and new ones must not be defined by bundles.

.. index::
   pair: Convention; Configuration

Extension Conventions
~~~~~~~~~~~~~~~~~~~~~

When creating an extension, follow these simple conventions:

* The extension must be stored in the ``DependencyInjection`` sub-namespace;

* The extension must be named after the bundle name and suffixed with
  ``Extension`` (``HelloExtension`` for ``HelloBundle``) -- when you provide
  several extensions for a single bundle, just end them with ``Extension``;

* The alias must be unique and named after the bundle name (``hello`` for
  ``HelloBundle`` or ``sensio.social.blog`` for ``Sensio\Social\BlogBundle``);

* The extension should provide an XSD schema.

If you follow these simple conventions, you extensions will be registered
automatically by Symfony. If not, override the Bundle
:method:`Symfony\\Framework\\Bundle\\Bundle::registerExtensions` method::

    class HelloBundle extends Bundle
    {
        public function registerExtensions(ContainerBuilder $container)
        {
            // register the extension(s) found in DependencyInjection/ directory
            parent::registerExtensions($container);

            // register extensions that do not follow the conventions manually
            $container->registerExtension(new ExtensionHello());
        }
    }

.. index::
   single: Bundles; Default Configuration

Default Configuration
~~~~~~~~~~~~~~~~~~~~~

As stated before, the user of the bundle should include the ``hello.config``
namespace in a configuration file for your extension code to be called. But
you can automatically register a default configuration by overriding the
Bundle :method:`Symfony\\Framework\\Bundle\\Bundle::registerExtensions`
method::

    class HelloBundle extends Bundle
    {
        public function registerExtensions(ContainerBuilder $container)
        {
            // will register the HelloBundle extension(s) found in DependencyInjection/ directory
            parent::registerExtensions($container);

            // load some defaults
            $container->load('hello', 'config', array(/* you default config for the hello.config namespace */));
        }
    }

.. caution::
   Symfony2 tries to be as explicit as possible. So, registering a default
   configuration automatically is probably not a good idea.
