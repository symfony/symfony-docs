.. index::
   single: Configuration; Semantic
   single: Bundle; Extension configuration

How to Create Friendly Configuration for a Bundle
=================================================

If you open your application configuration file (usually ``app/config/config.yml``),
you'll see a number of different configuration sections, such as ``framework``,
``twig`` and ``doctrine``. Each of these configures a specific bundle, allowing
you to define options at a high level and then let the bundle make all the
low-level, complex changes based on your settings.

For example, the following configuration tells the FrameworkBundle to enable the
form integration, which involves the definition of quite a few services as well
as integration of other related components:

.. configuration-block::

    .. code-block:: yaml

        framework:
            form: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:form />
            </framework:config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            'form' => true,
        ));

.. sidebar:: Using Parameters to Configure your Bundle

    If you don't have plans to share your bundle between projects, it doesn't
    make sense to use this more advanced way of configuration. Since you use
    the bundle only in one project, you can just change the service
    configuration each time.

    If you *do* want to be able to configure something from within
    ``config.yml``, you can always create a parameter there and use that
    parameter somewhere else.

Using the Bundle Extension
--------------------------

The basic idea is that instead of having the user override individual
parameters, you let the user configure just a few, specifically created,
options. As the bundle developer, you then parse through that configuration and
load correct services and parameters inside an "Extension" class.

As an example, imagine you are creating a social bundle, which provides
integration with Twitter and such. To be able to reuse your bundle, you have to
make the ``client_id`` and ``client_secret`` variables configurable. Your
bundle configuration would look like:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        acme_social:
            twitter:
                client_id: 123
                client_secret: $ecret

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:acme-social="http://example.org/dic/schema/acme_social"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

           <acme-social:config>
               <twitter client-id="123" client-secret="$ecret" />
           </acme-social:config>

           <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('acme_social', array(
            'client_id'     => 123,
            'client_secret' => '$ecret',
        ));

.. seealso::

    Read more about the extension in :doc:`/cookbook/bundles/extension`.

.. tip::

    If a bundle provides an Extension class, then you should *not* generally
    override any service container parameters from that bundle. The idea
    is that if an Extension class is present, every setting that should be
    configurable should be present in the configuration made available by
    that class. In other words, the extension class defines all the public
    configuration settings for which backward compatibility will be maintained.

.. seealso::

    For parameter handling within a dependency injection container see
    :doc:`/cookbook/configuration/using_parameters_in_dic`.


Processing the ``$configs`` Array
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First things first, you have to create an extension class as explained in
:doc:`extension`.

Whenever a user includes the ``acme_social`` key (which is the DI alias) in a
configuration file, the configuration under it is added to an array of
configurations and passed to the ``load()`` method of your extension (Symfony
automatically converts XML and YAML to an array).

For the configuration example in the previous section, the array passed to your
``load()`` method will look like this::

    array(
        array(
            'twitter' => array(
                'client_id' => 123,
                'client_secret' => '$ecret',
            ),
        ),
    )

Notice that this is an *array of arrays*, not just a single flat array of the
configuration values. This is intentional, as it allows Symfony to parse
several configuration resources. For example, if ``acme_social`` appears in
another configuration file - say ``config_dev.yml`` - with different values
beneath it, the incoming array might look like this::

    array(
        // values from config.yml
        array(
            'twitter' => array(
                'client_id' => 123,
                'client_secret' => '$secret',
            ),
        ),
        // values from config_dev.yml
        array(
            'twitter' => array(
                'client_id' => 456,
            ),
        ),
    )

The order of the two arrays depends on which one is set first.

But don't worry! Symfony's Config component will help you merge these values,
provide defaults and give the user validation errors on bad configuration.
Here's how it works. Create a ``Configuration`` class in the
``DependencyInjection`` directory and build a tree that defines the structure
of your bundle's configuration.

The ``Configuration`` class to handle the sample configuration looks like::

    // src/Acme/SocialBundle/DependencyInjection/Configuration.php
    namespace Acme\SocialBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    class Configuration implements ConfigurationInterface
    {
        public function getConfigTreeBuilder()
        {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('acme_social');

            $rootNode
                ->children()
                    ->arrayNode('twitter')
                        ->children()
                            ->integerNode('client_id')->end()
                            ->scalarNode('client_secret')->end()
                        ->end()
                    ->end() // twitter
                ->end()
            ;

            return $treeBuilder;
        }
    }

.. seealso::

    The ``Configuration`` class can be much more complicated than shown here,
    supporting "prototype" nodes, advanced validation, XML-specific normalization
    and advanced merging. You can read more about this in
    :doc:`the Config component documentation </components/config/definition>`. You
    can also see it in action by checking out some core Configuration
    classes, such as the one from the `FrameworkBundle Configuration`_ or the
    `TwigBundle Configuration`_.

This class can now be used in your ``load()`` method to merge configurations and
force validation (e.g. if an additional option was passed, an exception will be
thrown)::

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
        // ...
    }

The ``processConfiguration()`` method uses the configuration tree you've defined
in the ``Configuration`` class to validate, normalize and merge all the
configuration arrays together.

.. tip::

    Instead of calling ``processConfiguration()`` in your extension each time you
    provide some configuration options, you might want to use the
    :class:`Symfony\\Component\\HttpKernel\\DependencyInjection\\ConfigurableExtension`
    to do this automatically for you::

        // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php
        namespace Acme\HelloBundle\DependencyInjection;

        use Symfony\Component\DependencyInjection\ContainerBuilder;
        use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

        class AcmeHelloExtension extends ConfigurableExtension
        {
            // note that this method is called loadInternal and not load
            protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
            {
                // ...
            }
        }

    This class uses the ``getConfiguration()`` method to get the Configuration
    instance. You should override it if your Configuration class is not called
    ``Configuration`` or if it is not placed in the same namespace as the
    extension.

.. sidebar:: Processing the Configuration yourself

    Using the Config component is fully optional. The ``load()`` method gets an
    array of configuration values. You can simply parse these arrays yourself
    (e.g. by overriding configurations and using :phpfunction:`isset` to check
    for the existence of a value). Be aware that it'll be very hard to support XML.

    .. code-block:: php

        public function load(array $configs, ContainerBuilder $container)
        {
            $config = array();
            // let resources override the previous set value
            foreach ($configs as $subConfig) {
                $config = array_merge($config, $subConfig);
            }

            // ... now use the flat $config array
        }

Modifying the Configuration of Another Bundle
---------------------------------------------

If you have multiple bundles that depend on each other, it may be useful
to allow one ``Extension`` class to modify the configuration passed to another
bundle's ``Extension`` class, as if the end-developer has actually placed that
configuration in their ``app/config/config.yml`` file. This can be achieved
using a prepend extension. For more details, see
:doc:`/cookbook/bundles/prepend_extension`.

Dump the Configuration
----------------------

The ``config:dump-reference`` command dumps the default configuration of a
bundle in the console using the Yaml format.

As long as your bundle's configuration is located in the standard location
(``YourBundle\DependencyInjection\Configuration``) and does not have
a constructor it will work automatically. If you
have something different, your ``Extension`` class must override the
:method:`Extension::getConfiguration() <Symfony\\Component\\HttpKernel\\DependencyInjection\\Extension::getConfiguration>`
method and return an instance of your ``Configuration``.

Supporting XML
--------------

Symfony allows people to provide the configuration in three different formats:
Yaml, XML and PHP. Both Yaml and PHP use the same syntax and are supported by
default when using the Config component. Supporting XML requires you to do some
more things. But when sharing your bundle with others, it is recommended that
you follow these steps.

Make your Config Tree ready for XML
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Config component provides some methods by default to allow it to correctly
process XML configuration. See ":ref:`component-config-normalization`" of the
component documentation. However, you can do some optional things as well, this
will improve the experience of using XML configuration:

Choosing an XML Namespace
~~~~~~~~~~~~~~~~~~~~~~~~~

In XML, the `XML namespace`_ is used to determine which elements belong to the
configuration of a specific bundle. The namespace is returned from the
:method:`Extension::getNamespace() <Symfony\\Component\\DependencyInjection\\Extension\\Extension::getNamespace>`
method. By convention, the namespace is a URL (it doesn't have to be a valid
URL nor does it need to exists). By default, the namespace for a bundle is
``http://example.org/dic/schema/DI_ALIAS``, where ``DI_ALIAS`` is the DI alias of
the extension. You might want to change this to a more professional URL::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php

    // ...
    class AcmeHelloExtension extends Extension
    {
        // ...

        public function getNamespace()
        {
            return 'http://acme_company.com/schema/dic/hello';
        }
    }

Providing an XML Schema
~~~~~~~~~~~~~~~~~~~~~~~

XML has a very useful feature called `XML schema`_. This allows you to
describe all possible elements and attributes and their values in an XML Schema
Definition (an xsd file). This XSD file is used by IDEs for auto completion and
it is used by the Config component to validate the elements.

In order to use the schema, the XML configuration file must provide an
``xsi:schemaLocation`` attribute pointing to the XSD file for a certain XML
namespace. This location always starts with the XML namespace. This XML
namespace is then replaced with the XSD validation base path returned from
:method:`Extension::getXsdValidationBasePath() <Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface::getXsdValidationBasePath>`
method. This namespace is then followed by the rest of the path from the base
path to the file itself.

By convention, the XSD file lives in the ``Resources/config/schema``, but you
can place it anywhere you like. You should return this path as the base path::

    // src/Acme/HelloBundle/DependencyInjection/AcmeHelloExtension.php

    // ...
    class AcmeHelloExtension extends Extension
    {
        // ...

        public function getXsdValidationBasePath()
        {
            return __DIR__.'/../Resources/config/schema';
        }
    }

Assume the XSD file is called ``hello-1.0.xsd``, the schema location will be
``http://acme_company.com/schema/dic/hello/hello-1.0.xsd``:

.. code-block:: xml

    <!-- app/config/config.xml -->
    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:acme-hello="http://acme_company.com/schema/dic/hello"
        xsi:schemaLocation="http://acme_company.com/schema/dic/hello
            http://acme_company.com/schema/dic/hello/hello-1.0.xsd">

        <acme-hello:config>
            <!-- ... -->
        </acme-hello:config>

        <!-- ... -->
    </container>

.. _`FrameworkBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/Configuration.php
.. _`TwigBundle Configuration`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/TwigBundle/DependencyInjection/Configuration.php
.. _`XML namespace`: https://en.wikipedia.org/wiki/XML_namespace
.. _`XML schema`: https://en.wikipedia.org/wiki/XML_schema
