.. index::
    single: DependencyInjection; Tags
    single: Service Container; Tags

How to Work with Service Tags
=============================

In the same way that a blog post on the web might be tagged with things such
as "Symfony" or "PHP", services configured in your container can also be
tagged. In the service container, a tag implies that the service is meant
to be used for a specific purpose. Take the following example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            AppBundle\Twig\AppExtension:
                public: false
                tags: [twig.extension]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Twig\AppExtension" public="false">
                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Twig\AppExtension;

        $container->register(AppExtension::class)
            ->setPublic(false)
            ->addTag('twig.extension');

The ``twig.extension`` tag is a special tag that the TwigBundle uses
during configuration. By giving the service this ``twig.extension`` tag,
the bundle knows that the ``AppExtension::class`` service should be registered
as a Twig extension with Twig. In other words, Twig finds all services tagged
with ``twig.extension`` and automatically registers them as extensions.

Tags, then, are a way to tell Symfony or other third-party bundles that
your service should be registered or used in some special way by the bundle.

For a list of all the tags available in the core Symfony Framework, check
out :doc:`/reference/dic_tags`. Each of these has a different effect on your
service and many tags require additional arguments (beyond just the ``name``
parameter).

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

    // src/AppBundle/Mail/TransportChain.php
    namespace AppBundle\Mail;

    class TransportChain
    {
        private $transports;

        public function __construct()
        {
            $this->transports = array();
        }

        public function addTransport(\Swift_Transport $transport)
        {
            $this->transports[] = $transport;
        }
    }

Then, define the chain as a service:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.mailer_transport_chain:
                class: AppBundle\Mail\TransportChain

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="app.mailer_transport_chain"
                    class="AppBundle\Mail\TransportChain"
                />

            </services>
        </container>

    .. code-block:: php

        use AppBundle\Mail\TransportChain;

        $container->register('app.mailer_transport_chain', TransportChain::class);

Define Services with a Custom Tag
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you might want several of the ``\Swift_Transport`` classes to be instantiated
and added to the chain automatically using the ``addTransport()`` method.
For example, you may add the following transports as services:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.smtp_transport:
                class: \Swift_SmtpTransport
                arguments: ['%mailer_host%']
                tags: [app.mail_transport]

            app.sendmail_transport:
                class: \Swift_SendmailTransport
                tags: [app.mail_transport]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.smtp_transport" class="\Swift_SmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport" />
                </service>

                <service id="app.sendmail_transport" class="\Swift_SendmailTransport">
                    <tag name="app.mail_transport" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definitionSmtp = new Definition('\Swift_SmtpTransport', array('%mailer_host%'));
        $definitionSmtp->addTag('app.mail_transport');
        $container->setDefinition('app.smtp_transport', $definitionSmtp);

        $definitionSendmail = new Definition('\Swift_SendmailTransport');
        $definitionSendmail->addTag('app.mail_transport');
        $container->setDefinition('app.sendmail_transport', $definitionSendmail);

Notice that each service was given a tag named ``app.mail_transport``. This is
the custom tag that you'll use in your compiler pass. The compiler pass is what
makes this tag "mean" something.

.. _service-container-compiler-pass-tags:

Create a Compiler Pass
~~~~~~~~~~~~~~~~~~~~~~

You can now use a :ref:`compiler pass <components-di-separate-compiler-passes>` to ask the
container for any services with the ``app.mail_transport`` tag::

    // src/AppBundle/DependencyInjection/Compiler/MailTransportPass.php
    namespace AppBundle\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;

    class MailTransportPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            // always first check if the primary service is defined
            if (!$container->has('app.mailer_transport_chain')) {
                return;
            }

            $definition = $container->findDefinition('app.mailer_transport_chain');

            // find all service IDs with the app.mail_transport tag
            $taggedServices = $container->findTaggedServiceIds('app.mail_transport');

            foreach ($taggedServices as $id => $tags) {
                // add the transport service to the ChainTransport service
                $definition->addMethodCall('addTransport', array(new Reference($id)));
            }
        }
    }

Register the Pass with the Container
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to run the compiler pass when the container is compiled, you have to
add the compiler pass to the container in the ``build()`` method of your
bundle::

    // src/AppBundle/AppBundle.php

    // ...
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use AppBundle\DependencyInjection\Compiler\MailTransportPass;

    class AppBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
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
            $this->transports = array();
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

        services:
            app.smtp_transport:
                class: \Swift_SmtpTransport
                arguments: ['%mailer_host%']
                tags:
                    - { name: app.mail_transport, alias: foo }

            app.sendmail_transport:
                class: \Swift_SendmailTransport
                tags:
                    - { name: app.mail_transport, alias: bar }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.smtp_transport" class="\Swift_SmtpTransport">
                    <argument>%mailer_host%</argument>

                    <tag name="app.mail_transport" alias="foo" />
                </service>

                <service id="app.sendmail_transport" class="\Swift_SendmailTransport">
                    <tag name="app.mail_transport" alias="bar" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definitionSmtp = new Definition('\Swift_SmtpTransport', array('%mailer_host%'));
        $definitionSmtp->addTag('app.mail_transport', array('alias' => 'foo'));
        $container->setDefinition('app.smtp_transport', $definitionSmtp);

        $definitionSendmail = new Definition('\Swift_SendmailTransport');
        $definitionSendmail->addTag('app.mail_transport', array('alias' => 'bar'));
        $container->setDefinition('app.sendmail_transport', $definitionSendmail);

.. tip::

    In YAML format, you may provide the tag as a simple string as long as
    you don't need to specify additional attributes. The following definitions
    are equivalent.

    .. code-block:: yaml

        services:

            # Compact syntax
            app.sendmail_transport:
                class: \Swift_SendmailTransport
                tags: [app.mail_transport]

            # Verbose syntax
            app.sendmail_transport:
                class: \Swift_SendmailTransport
                tags:
                    - { name: app.mail_transport }

    .. versionadded:: 3.3
        Support for the compact tag notation in the YAML format was introduced
        in Symfony 3.3.

Notice that you've added a generic ``alias`` key to the tag. To actually
use this, update the compiler::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;

    class TransportCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            if (!$container->hasDefinition('app.mailer_transport_chain')) {
                return;
            }

            $definition = $container->getDefinition('app.mailer_transport_chain');
            $taggedServices = $container->findTaggedServiceIds('app.mail_transport');

            foreach ($taggedServices as $id => $tags) {
                foreach ($tags as $attributes) {
                    $definition->addMethodCall('addTransport', array(
                        new Reference($id),
                        $attributes["alias"]
                    ));
                }
            }
        }
    }

The double loop may be confusing. This is because a service can have more
than one tag. You tag a service twice or more with the ``app.mail_transport``
tag. The second foreach loop iterates over the ``app.mail_transport``
tags set for the current service and gives you the attributes.
