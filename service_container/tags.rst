.. index::
    single: DependencyInjection; Tags
    single: Service Container; Tags

How to Work with Service Tags
=============================

**Service tags** are a way to tell Symfony or other third-party bundles that
your service should be registered in some special way. Take the following
example:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Twig\AppExtension:
                tags: ['twig.extension']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension">
                    <tag name="twig.extension"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(AppExtension::class)
                ->tag('twig.extension');
        };


Services tagged with the ``twig.extension`` tag are collected during the
initialization of TwigBundle and added to Twig as extensions.

Other tags are used to integrate your services into other systems. For a list of
all the tags available in the core Symfony Framework, check out
:doc:`/reference/dic_tags`. Each of these has a different effect on your service
and many tags require additional arguments (beyond the ``name`` parameter).

**For most users, this is all you need to know**. If you want to go further and
learn how to create your own custom tags, keep reading.

.. _di-instanceof:

Autoconfiguring Tags
--------------------

If you enable :ref:`autoconfigure <services-autoconfigure>`, then some tags are
automatically applied for you. That's true for the ``twig.extension`` tag: the
container sees that your class extends ``AbstractExtension`` (or more accurately,
that it implements ``ExtensionInterface``) and adds the tag for you.

If you want to apply tags automatically for your own services, use the
``_instanceof`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # this config only applies to the services created by this file
            _instanceof:
                # services whose classes are instances of CustomInterface will be tagged automatically
                App\Security\CustomInterface:
                    tags: ['app.custom_tag']
            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- this config only applies to the services created by this file -->
                <instanceof id="App\Security\CustomInterface" autowire="true">
                    <!-- services whose classes are instances of CustomInterface will be tagged automatically -->
                    <tag name="app.custom_tag"/>
                </instanceof>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\CustomInterface;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            // this config only applies to the services created by this file
            $services
                ->instanceof(CustomInterface::class)
                    // services whose classes are instances of CustomInterface will be tagged automatically
                    ->tag('app.custom_tag');
        };

It is also possible to use the ``#[AutoconfigureTag]`` attribute directly on the
base class or interface::

    // src/Security/CustomInterface.php
    namespace App\Security;

    use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

    #[AutoconfigureTag('app.custom_tag')]
    interface CustomInterface
    {
        // ...
    }

.. tip::

    If you need more capabilities to autoconfigure instances of your base class
    like their laziness, their bindings or their calls for example, you may rely
    on the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure` attribute.

For more advanced needs, you can define the automatic tags using the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerForAutoconfiguration` method.

In a Symfony application, call this method in your kernel class::

    // src/Kernel.php
    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $containerBuilder): void
        {
            $containerBuilder->registerForAutoconfiguration(CustomInterface::class)
                ->addTag('app.custom_tag')
            ;
        }
    }

In a Symfony bundle, call this method in the ``load()`` method of the
:doc:`bundle extension class </bundles/extension>`::

    // src/DependencyInjection/MyBundleExtension.php
    class MyBundleExtension extends Extension
    {
        // ...

        public function load(array $configs, ContainerBuilder $containerBuilder): void
        {
            $containerBuilder->registerForAutoconfiguration(CustomInterface::class)
                ->addTag('app.custom_tag')
            ;
        }
    }

Creating custom Tags
--------------------

Tags on their own don't actually alter the functionality of your services in
any way. But if you choose to, you can ask a container builder for a list of
all services that were tagged with some specific tag. This is useful in
compiler passes where you can find these services and use or modify them in
some specific way.

For example, if you are using the Symfony Mailer component you might want
to implement a "transport chain", which is a collection of classes implementing
``\MailerTransport``. Using the chain, you'll want Mailer to try several
ways of transporting the message until one succeeds.

To begin with, define the ``TransportChain`` class::

    // src/Mail/TransportChain.php
    namespace App\Mail;

    class TransportChain
    {
        private $transports;

        public function __construct()
        {
            $this->transports = [];
        }

        public function addTransport(\MailerTransport $transport): void
        {
            $this->transports[] = $transport;
        }
    }

Then, define the chain as a service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mail\TransportChain: ~

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mail\TransportChain"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\TransportChain;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(TransportChain::class);
        };


Define Services with a Custom Tag
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you might want several of the ``\MailerTransport`` classes to be instantiated
and added to the chain automatically using the ``addTransport()`` method.
For example, you may add the following transports as services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            MailerSmtpTransport:
                arguments: ['%mailer_host%']
                tags: ['app.mail_transport']

            MailerSendmailTransport:
                tags: ['app.mail_transport']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="MailerSmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport"/>
                </service>

                <service id="MailerSendmailTransport">
                    <tag name="app.mail_transport"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(\MailerSmtpTransport::class)
                // the param() method was introduced in Symfony 5.2.
                ->args([param('mailer_host')])
                ->tag('app.mail_transport')
            ;

            $services->set(\MailerSendmailTransport::class)
                ->tag('app.mail_transport')
            ;
        };

Notice that each service was given a tag named ``app.mail_transport``. This is
the custom tag that you'll use in your compiler pass. The compiler pass is what
makes this tag "mean" something.

.. _service-container-compiler-pass-tags:

Create a Compiler Pass
~~~~~~~~~~~~~~~~~~~~~~

You can now use a :ref:`compiler pass <components-di-separate-compiler-passes>` to ask the
container for any services with the ``app.mail_transport`` tag::

    // src/DependencyInjection/Compiler/MailTransportPass.php
    namespace App\DependencyInjection\Compiler;

    use App\Mail\TransportChain;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class MailTransportPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $containerBuilder): void
        {
            // always first check if the primary service is defined
            if (!$containerBuilder->has(TransportChain::class)) {
                return;
            }

            $definition = $containerBuilder->findDefinition(TransportChain::class);

            // find all service IDs with the app.mail_transport tag
            $taggedServices = $containerBuilder->findTaggedServiceIds('app.mail_transport');

            foreach ($taggedServices as $id => $tags) {
                // add the transport service to the TransportChain service
                $definition->addMethodCall('addTransport', [new Reference($id)]);
            }
        }
    }

Register the Pass with the Container
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to run the compiler pass when the container is compiled, you have to
add the compiler pass to the container in a :doc:`bundle extension </bundles/extension>`
or from your kernel::

    // src/Kernel.php
    namespace App;

    use App\DependencyInjection\Compiler\MailTransportPass;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    // ...

    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $containerBuilder): void
        {
            $containerBuilder->addCompilerPass(new MailTransportPass());
        }
    }

.. tip::

    When implementing the ``CompilerPassInterface`` in a service extension, you
    do not need to register it. See the
    :ref:`components documentation <components-di-compiler-pass>` for more
    information.

Adding Additional Attributes on Tags
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes you need additional information about each service that's tagged
with your tag. For example, you might want to add an alias to each member
of the transport chain.

To begin with, change the ``TransportChain`` class::

    class TransportChain
    {
        private $transports;

        public function __construct()
        {
            $this->transports = [];
        }

        public function addTransport(\MailerTransport $transport, $alias): void
        {
            $this->transports[$alias] = $transport;
        }

        public function getTransport($alias): ?\MailerTransport
        {
            if (array_key_exists($alias, $this->transports)) {
                return $this->transports[$alias];
            }

            return null;
        }
    }

As you can see, when ``addTransport()`` is called, it takes not only a ``MailerTransport``
object, but also a string alias for that transport. So, how can you allow
each tagged transport service to also supply an alias?

To answer this, change the service declaration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            MailerSmtpTransport:
                arguments: ['%mailer_host%']
                tags:
                    - { name: 'app.mail_transport', alias: 'smtp' }

            MailerSendmailTransport:
                tags:
                    - { name: 'app.mail_transport', alias: 'sendmail' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="MailerSmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport" alias="smtp"/>
                </service>

                <service id="MailerSendmailTransport">
                    <tag name="app.mail_transport" alias="sendmail"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(\MailerSmtpTransport::class)
                // the param() method was introduced in Symfony 5.2.
                ->args([param('mailer_host')])
                ->tag('app.mail_transport', ['alias' => 'smtp'])
            ;

            $services->set(\MailerSendmailTransport::class)
                ->tag('app.mail_transport', ['alias' => 'sendmail'])
            ;
        };

.. tip::

    In YAML format, you may provide the tag as a simple string as long as
    you don't need to specify additional attributes. The following definitions
    are equivalent.

    .. code-block:: yaml

        # config/services.yaml
        services:
            # Compact syntax
            MailerSendmailTransport:
                class: \MailerSendmailTransport
                tags: ['app.mail_transport']

            # Verbose syntax
            MailerSendmailTransport:
                class: \MailerSendmailTransport
                tags:
                    - { name: 'app.mail_transport' }

Notice that you've added a generic ``alias`` key to the tag. To actually
use this, update the compiler::

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class TransportCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $containerBuilder): void
        {
            // ...

            foreach ($taggedServices as $id => $tags) {

                // a service could have the same tag twice
                foreach ($tags as $attributes) {
                    $definition->addMethodCall('addTransport', [
                        new Reference($id),
                        $attributes['alias'],
                    ]);
                }
            }
        }
    }

The double loop may be confusing. This is because a service can have more
than one tag. You tag a service twice or more with the ``app.mail_transport``
tag. The second ``foreach`` loop iterates over the ``app.mail_transport``
tags set for the current service and gives you the attributes.

Reference Tagged Services
~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a shortcut to inject all services tagged with a specific tag,
which is a common need in some applications, so you don't have to write a
compiler pass just for that.

Consider the following ``HandlerCollection`` class where you want to inject
all services tagged with ``app.handler`` into its constructor argument::

    // src/HandlerCollection.php
    namespace App;

    class HandlerCollection
    {
        public function __construct(iterable $handlers)
        {
        }
    }

Symfony allows you to inject the services using YAML/XML/PHP configuration or
directly via PHP attributes:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

        class HandlerCollection
        {
            public function __construct(
                // the attribute must be applied directly to the argument to autowire
                #[TaggedIterator('app.handler')] iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags: ['app.handler']

            App\Handler\Two:
                tags: ['app.handler']

            App\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments:
                    - !tagged_iterator app.handler

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler"/>
                </service>

                <service id="App\HandlerCollection">
                    <!-- inject all services tagged with app.handler as first argument -->
                    <argument type="tagged_iterator" tag="app.handler"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(App\Handler\One::class)
                ->tag('app.handler')
            ;

            $services->set(App\Handler\Two::class)
                ->tag('app.handler')
            ;

            $services->set(App\HandlerCollection::class)
                // inject all services tagged with app.handler as first argument
                ->args([tagged_iterator('app.handler')])
            ;
        };

.. seealso::

    See also :doc:`tagged locator services </service_container/service_subscribers_locators>`

Tagged Services with Priority
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The tagged services can be prioritized using the ``priority`` attribute. The
priority is a positive or negative integer that defaults to ``0``. The higher
the number, the earlier the tagged service will be located in the collection:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', priority: 20 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" priority="20"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(One::class)
                ->tag('app.handler', ['priority' => 20])
            ;
        };

Another option, which is particularly useful when using autoconfiguring
tags, is to implement the static ``getDefaultPriority()`` method on the
service itself::

    // src/Handler/One.php
    namespace App\Handler;

    class One
    {
        public static function getDefaultPriority(): int
        {
            return 3;
        }
    }

If you want to have another method defining the priority
(e.g. ``getPriority()`` rather than ``getDefaultPriority()``),
you can define it in the configuration of the collecting service:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

        class HandlerCollection
        {
            public function __construct(
                #[TaggedIterator('app.handler', defaultPriorityMethod: 'getPriority')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments:
                    - !tagged_iterator { tag: app.handler, default_priority_method: getPriority }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <service id="App\HandlerCollection">
                    <argument type="tagged_iterator" tag="app.handler" default-priority-method="getPriority"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

        return function (ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            // ...

            $services->set(App\HandlerCollection::class)
                ->args([
                    tagged_iterator('app.handler', null, null, 'getPriority'),
                ])
            ;
        };

Tagged Services with Index
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to retrieve a specific service within the injected collection
you can use the ``index_by`` and ``default_index_method`` options of the
argument in combination with ``!tagged_iterator``.

Using the previous example, this service configuration creates a collection
indexed by the ``key`` attribute:

.. configuration-block::

    .. code-block:: php-attributes

        // src/HandlerCollection.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

        class HandlerCollection
        {
            public function __construct(
                #[TaggedIterator('app.handler', indexAttribute: 'key')]
                iterable $handlers
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', key: 'handler_one' }

            App\Handler\Two:
                tags:
                    - { name: 'app.handler', key: 'handler_two' }

            App\HandlerCollection:
                arguments: [!tagged_iterator { tag: 'app.handler', index_by: 'key' }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" key="handler_one"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler" key="handler_two"/>
                </service>

                <service id="App\HandlerCollection">
                    <argument type="tagged_iterator" tag="app.handler" index-by="key"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Handler\One;
        use App\Handler\Two;
        use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

        return function (ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(One::class)
                ->tag('app.handler', ['key' => 'handler_one']);

            $services->set(Two::class)
                ->tag('app.handler', ['key' => 'handler_two']);

            $services->set(App\HandlerCollection::class)
                ->args([
                    // 2nd argument is the index attribute name
                    tagged_iterator('app.handler', 'key'),
                ])
            ;
        };

After compilation the ``HandlerCollection`` is able to iterate over your
application handlers. To retrieve a specific service from the iterator, call the
``iterator_to_array()`` function and then use the ``key`` attribute to get the
array element. For example, to retrieve the ``handler_two`` handler::

    // src/Handler/HandlerCollection.php
    namespace App\Handler;

    class HandlerCollection
    {
        public function __construct(iterable $handlers)
        {
            $handlers = $handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers;

            $handlerTwo = $handlers['handler_two'];
        }
    }

.. tip::

    Just like the priority, you can also implement a static
    ``getDefaultIndexName()`` method in the handlers and set ``index_by`` to ``index`` in config then omit the
    index attribute (``key``)::

        // src/Handler/One.php
        namespace App\Handler;

        class One
        {
            // ...
            public static function getDefaultIndexName(): string
            {
                return 'handler_one';
            }
        }

    You also can define the name of the static method to implement on each service
    with the ``default_index_method`` attribute on the tagged argument:

    .. configuration-block::

        .. code-block:: php-attributes

            // src/HandlerCollection.php
            namespace App;

            use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

            class HandlerCollection
            {
                public function __construct(
                    #[TaggedIterator('app.handler', defaultIndexMethod: 'getIndex')]
                    iterable $handlers
                ) {
                }
            }

        .. code-block:: yaml

            # config/services.yaml
            services:
                # ...

                App\HandlerCollection:
                    # use getIndex() instead of getDefaultIndexName()
                    arguments: [!tagged_iterator { tag: 'app.handler', default_index_method: 'getIndex' }]

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... -->

                    <service id="App\HandlerCollection">
                        <!-- use getIndex() instead of getDefaultIndexName() -->
                        <argument type="tagged_iterator"
                            tag="app.handler"
                            default-index-method="someFunctionName"
                        />
                    </service>
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\HandlerCollection;
            use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

            return function (ContainerConfigurator $containerConfigurator) {
                $services = $containerConfigurator->services();

                // ...

                // use getIndex() instead of getDefaultIndexName()
                $services->set(HandlerCollection::class)
                    ->args([
                        tagged_iterator('app.handler', null, 'getIndex'),
                    ])
                ;
            };

The ``#[AsTaggedItem]`` attribute
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is possible to define both the priority and the index of a tagged
item thanks to the ``#[AsTaggedItem]`` attribute. This attribute must
be used directly on the class of the service you want to configure::

    // src/Handler/One.php
    namespace App\Handler;

    use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

    #[AsTaggedItem(index: 'handler_one', priority: 10)]
    class One
    {
        // ...
    }
