﻿.. index::
   single: Dependency Injection; Tags

How to make your Services use Tags
==================================

Tags are a generic string (along with some options) that can be applied to
any service. By themselves, tags don't actually the functionality of your
services in any way. But if you choose to, you can ask a container builder
for a list of all services that were tagged with some specific tag. This
is useful in compiler passes where you can find these services and use or
modify them in some specific way.

For example, if you are using Swift Mailer you might imagine that you want
to implement a "transport chain", which is a collection of classes implementing
``\Swift_Transport``. Using the chain, you'll want Swift Mailer to try several
ways of transporting the message until one succeeds.

To begin with, define the ``TransportChain`` class::

    class TransportChain
    {
        private $transports;

        public function __construct()
        {
            $this->transports = array();
        }

        public function addTransport(\Swift_Transport  $transport)
        {
            $this->transports[] = $transport;
        }
    }

Then, define the chain as a service:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            acme_mailer.transport_chain.class: TransportChain

        services:
            acme_mailer.transport_chain:
                class: %acme_mailer.transport_chain.class%

    .. code-block:: xml

        <parameters>
            <parameter key="acme_mailer.transport_chain.class">TransportChain</parameter>
        </parameters>

        <services>
            <service id="acme_mailer.transport_chain" class="%acme_mailer.transport_chain.class%" />
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('acme_mailer.transport_chain.class', 'TransportChain');

        $container->setDefinition('acme_mailer.transport_chain', new Definition('%acme_mailer.transport_chain.class%'));

Define Services with a Custom Tag
---------------------------------

Now we want several of the ``\Swift_Transport`` classes to be instantiated
and added to the chain automatically using the ``addTransport()`` method.
As an example we add the following transports as services:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_mailer.transport.smtp:
                class: \Swift_SmtpTransport
                arguments:
                    - %mailer_host%
                tags:
                    -  { name: acme_mailer.transport }
            acme_mailer.transport.sendmail:
                class: \Swift_SendmailTransport
                tags:
                    -  { name: acme_mailer.transport }

    .. code-block:: xml

        <service id="acme_mailer.transport.smtp" class="\Swift_SmtpTransport">
            <argument>%mailer_host%</argument>
            <tag name="acme_mailer.transport" />
        </service>

        <service id="acme_mailer.transport.sendmail" class="\Swift_SendmailTransport">
            <tag name="acme_mailer.transport" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definitionSmtp = new Definition('\Swift_SmtpTransport', array('%mailer_host%'));
        $definitionSmtp->addTag('acme_mailer.transport');
        $container->setDefinition('acme_mailer.transport.smtp', $definitionSmtp);

        $definitionSendmail = new Definition('\Swift_SendmailTransport');
        $definitionSendmail->addTag('acme_mailer.transport');
        $container->setDefinition('acme_mailer.transport.sendmail', $definitionSendmail);

Notice that each was given a tag named ``acme_mailer.transport``. This is
the custom tag that you'll use in your compiler pass. The compiler pass
is what makes this tag "mean" something.

Create a ``CompilerPass``
-------------------------

Your compiler pass can now ask the container for any services with the
custom tag::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;

    class TransportCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            if (false === $container->hasDefinition('acme_mailer.transport_chain')) {
                return;
            }

            $definition = $container->getDefinition('acme_mailer.transport_chain');

            foreach ($container->findTaggedServiceIds('acme_mailer.transport') as $id => $attributes) {
                $definition->addMethodCall('addTransport', array(new Reference($id)));
            }
        }
    }

The ``process()`` method checks for the existence of the ``acme_mailer.transport_chain``
service, then looks for all services tagged ``acme_mailer.transport``. It adds
to the definition of the ``acme_mailer.transport_chain`` service a call to
``addTransport()`` for each "acme_mailer.transport" service it has found.
The first argument of each of these calls will be the mailer transport service
itself.

Register the Pass with the Container
------------------------------------

You also need to register the pass with the container, it will then be
run when the container is compiled::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->addCompilerPass(new TransportCompilerPass);
