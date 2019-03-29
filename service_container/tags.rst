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
                public: false
                tags: ['twig.extension']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension" public="false">
                    <tag name="twig.extension"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Twig\AppExtension;

        $container->register(AppExtension::class)
            ->setPublic(false)
            ->addTag('twig.extension');

Services tagged with the ``twig.extension`` tag are collected during the
initialization of TwigBundle and added to Twig as extensions.

Other tags are used to integrate your services into other systems. For a list of
all the tags available in the core Symfony Framework, check out
:doc:`/reference/dic_tags`. Each of these has a different effect on your service
and many tags require additional arguments (beyond just the ``name`` parameter).

**For most users, this is all you need to know**. If you want to go further and
learn how to create your own custom tags, keep reading.

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
        <?xml version="1.0" encoding="UTF-8"?>
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
        use App\Security\CustomInterface;
        // ...

        // services whose classes are instances of CustomInterface will be tagged automatically
        $container->registerForAutoconfiguration(CustomInterface::class)
            ->addTag('app.custom_tag')
            ->setAutowired(true);

For more advanced needs, you can define the automatic tags using the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerForAutoconfiguration`
method in an :doc:`extension </bundles/extension>` or from your kernel::

    // src/Kernel.php
    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $container)
        {
            $container->registerForAutoconfiguration(CustomInterface::class)
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

For example, if you are using Swift Mailer you might imagine that you want
to implement a "transport chain", which is a collection of classes implementing
``\Swift_Transport``. Using the chain, you'll want Swift Mailer to try several
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

        public function addTransport(\Swift_Transport $transport)
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
        use App\Mail\TransportChain;

        $container->autowire(TransportChain::class);

Define Services with a Custom Tag
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you might want several of the ``\Swift_Transport`` classes to be instantiated
and added to the chain automatically using the ``addTransport()`` method.
For example, you may add the following transports as services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Swift_SmtpTransport:
                arguments: ['%mailer_host%']
                tags: ['app.mail_transport']

            Swift_SendmailTransport:
                tags: ['app.mail_transport']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Swift_SmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport"/>
                </service>

                <service class="\Swift_SendmailTransport">
                    <tag name="app.mail_transport"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        $container->register(\Swift_SmtpTransport::class)
            ->addArgument('%mailer_host%')
            ->addTag('app.mail_transport');

        $container->register(\Swift_SendmailTransport::class)
            ->addTag('app.mail_transport');

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

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;
    use App\Mail\TransportChain;

    class MailTransportPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            // always first check if the primary service is defined
            if (!$container->has(TransportChain::class)) {
                return;
            }

            $definition = $container->findDefinition(TransportChain::class);

            // find all service IDs with the app.mail_transport tag
            $taggedServices = $container->findTaggedServiceIds('app.mail_transport');

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

        protected function build(ContainerBuilder $container)
        {
            $container->addCompilerPass(new MailTransportPass());
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

        public function addTransport(\Swift_Transport $transport, $alias)
        {
            $this->transports[$alias] = $transport;
        }

        public function getTransport($alias)
        {
            if (array_key_exists($alias, $this->transports)) {
                return $this->transports[$alias];
            }
        }
    }

As you can see, when ``addTransport()`` is called, it takes not only a ``Swift_Transport``
object, but also a string alias for that transport. So, how can you allow
each tagged transport service to also supply an alias?

To answer this, change the service declaration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Swift_SmtpTransport:
                arguments: ['%mailer_host%']
                tags:
                    - { name: 'app.mail_transport', alias: 'smtp' }

            Swift_SendmailTransport:
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
                <service id="Swift_SmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport" alias="smtp"/>
                </service>

                <service id="Swift_SendmailTransport">
                    <tag name="app.mail_transport" alias="sendmail"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        $container->register(\Swift_SmtpTransport::class)
            ->addArgument('%mailer_host%')
            ->addTag('app.mail_transport', ['alias' => 'smtp']);

        $container->register(\Swift_SendmailTransport::class)
            ->addTag('app.mail_transport', ['alias' => 'sendmail']);

.. tip::

    In YAML format, you may provide the tag as a simple string as long as
    you don't need to specify additional attributes. The following definitions
    are equivalent.

    .. code-block:: yaml

        # config/services.yaml
        services:
            # Compact syntax
            Swift_SendmailTransport:
                class: \Swift_SendmailTransport
                tags: ['app.mail_transport']

            # Verbose syntax
            Swift_SendmailTransport:
                class: \Swift_SendmailTransport
                tags:
                    - { name: 'app.mail_transport' }

Notice that you've added a generic ``alias`` key to the tag. To actually
use this, update the compiler::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;

    class TransportCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            // ...

            foreach ($taggedServices as $id => $tags) {

                // a service could have the same tag twice
                foreach ($tags as $attributes) {
                    $definition->addMethodCall('addTransport', [
                        new Reference($id),
                        $attributes["alias"]
                    ]);
                }
            }
        }
    }

The double loop may be confusing. This is because a service can have more
than one tag. You tag a service twice or more with the ``app.mail_transport``
tag. The second foreach loop iterates over the ``app.mail_transport``
tags set for the current service and gives you the attributes.

Reference Tagged Services
~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a shortcut to inject all services tagged with a specific tag,
which is a common need in some applications, so you don't have to write a
compiler pass just for that.

In the following example, all services tagged with ``app.handler`` are passed as
first  constructor argument to the ``App\HandlerCollection`` service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags: ['app.handler']

            App\Handler\Two:
                tags: ['app.handler']

            App\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments: [!tagged app.handler]

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
                    <argument type="tagged" tag="app.handler"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

        $container->register(App\Handler\One::class)
            ->addTag('app.handler');

        $container->register(App\Handler\Two::class)
            ->addTag('app.handler');

        $container->register(App\HandlerCollection::class)
            // inject all services tagged with app.handler as first argument
            ->addArgument(new TaggedIteratorArgument('app.handler'));

After compilation the ``HandlerCollection`` service is able to iterate over your
application handlers::

    // src/HandlerCollection.php
    namespace App;

    class HandlerCollection
    {
        public function __construct(iterable $handlers)
        {
        }
    }

.. tip::

    The collected services can be prioritized using the ``priority`` attribute:

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
            $container->register(App\Handler\One::class)
                ->addTag('app.handler', ['priority' => 20]);

    Note that any other custom attributes will be ignored by this feature.
