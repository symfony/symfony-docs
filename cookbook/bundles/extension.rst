.. index::
   single: Configuration; Semantic
   single: Bundle; Extension Configuration

How to expose a Semantic Configuration for a Bundle
===================================================

Semantic configuration provides an even more flexible way to provide
configuration for a bundle with the following advantages over simple
parameters:

* Possibility to define more than just parameters (services for instance);

* Better hierarchy in the configuration (you can define nested configurations);

* Smart merging when several configuration files override an existing
  configuration;

* Configuration validation (if you define an XSD file and use XML);

* Completion when you use XSD and XML.

.. index::
   single: Bundles; Extension
   single: Dependency Injection, Extension

Creating an Extension
---------------------

To define a semantic configuration, create a Dependency Injection extension
that extends
:class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension`::

    // HelloBundle/DependencyInjection/HelloExtension.php
    use Symfony\Component\DependencyInjection\Extension\Extension;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class HelloExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
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

The previous class defines a ``hello`` namespace, usable in any configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        hello: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

           <hello:config />
           ...

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('hello', array());

.. tip::

    Your extension code is always called, even if the user does not provide
    any configuration. In that case, the array of configurations will be empty
    and you can still provide some sensible defaults if you want.

Parsing a Configuration
-----------------------

Whenever a user includes the ``hello`` namespace in a configuration file, it
is added to an array of configurations and passed to the ``load()`` method of
your extension (Symfony2 automatically converts XML and YAML to an array).

So, given the following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        hello:
            foo: foo
            bar: bar

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

            <hello:config foo="foo">
                <hello:bar>foo</hello:bar>
            </hello:config>

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('hello', array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));

The array passed to your method looks like the following::

    array(
        array(
            'foo' => 'foo',
            'bar' => 'bar',
        )
    )

Within ``load()``, the ``$container`` variable refers to a container that only
knows about this namespace configuration. You can manipulate it the way you
want to add services and parameters.

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
---------------------

When creating an extension, follow these simple conventions:

* The extension must be stored in the ``DependencyInjection`` sub-namespace;

* The extension must be named after the bundle name and suffixed with
  ``Extension`` (``SensioHelloExtension`` for ``SensioHelloBundle``);

* The alias must be unique and named after the bundle name (``sensio_blog``
  for ``SensioBlogBundle``);

* The extension should provide an XSD schema.

If you follow these simple conventions, your extensions will be registered
automatically by Symfony2. If not, override the Bundle
:method:`Symfony\\Component\\HttpKernel\\Bundle\\Bundle::build` method::

    class HelloBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            // register the extension(s) found in DependencyInjection/ directory
            parent::build($container);

            // register extensions that do not follow the conventions manually
            $container->registerExtension(new ExtensionHello());
        }
    }
