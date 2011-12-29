.. index::
   single: Service Container; Tags

How to make your Services use Tags
==================================

Several of Symfony2's core services depend on tags to recognize which services
should be loaded, notified of events, or handled in some other special way.
For example, Twig uses the tag  ``twig.extension`` to load extra extensions.

But you can also use tags in your own bundles. For example in case your service
handles a collection of some kind, or implements a "chain", in which several alternative
strategies are tried until one of them is successful. In this article I will use the example
of a "transport chain", which is a collection of classes implementing ``\Swift_Transport``.
Using the chain, the Swift mailer may try several ways of transport, until one succeeds.
This post focuses mainly on the dependency injection part of the story.

To begin with, define the ``TransportChain`` class::

    namespace Acme\MailerBundle;
    
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

        # src/Acme/MailerBundle/Resources/config/services.yml
        parameters:
            acme_mailer.transport_chain.class: Acme\MailerBundle\TransportChain
        
        services:
            acme_mailer.transport_chain:
                class: %acme_mailer.transport_chain.class%

    .. code-block:: xml

        <!-- src/Acme/MailerBundle/Resources/config/services.xml -->

        <parameters>
            <parameter key="acme_mailer.transport_chain.class">Acme\MailerBundle\TransportChain</parameter>
        </parameters>
    
        <services>
            <service id="acme_mailer.transport_chain" class="%acme_mailer.transport_chain.class%" />
        </services>
        
    .. code-block:: php
    
        // src/Acme/MailerBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $container->setParameter('acme_mailer.transport_chain.class', 'Acme\MailerBundle\TransportChain');
        
        $container->setDefinition('acme_mailer.transport_chain', new Definition('%acme_mailer.transport_chain.class%'));

Define Services with a Custom Tag
---------------------------------

Now we want several of the ``\Swift_Transport`` classes to be instantiated
and added to the chain automatically using the ``addTransport()`` method.
As an example we add the following transports as services:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/MailerBundle/Resources/config/services.yml
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

        <!-- src/Acme/MailerBundle/Resources/config/services.xml -->
        <service id="acme_mailer.transport.smtp" class="\Swift_SmtpTransport">
            <argument>%mailer_host%</argument>
            <tag name="acme_mailer.transport" />
        </service>
    
        <service id="acme_mailer.transport.sendmail" class="\Swift_SendmailTransport">
            <tag name="acme_mailer.transport" />
        </service>
        
    .. code-block:: php
    
        // src/Acme/MailerBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $definitionSmtp = new Definition('\Swift_SmtpTransport', array('%mailer_host%'));
        $definitionSmtp->addTag('acme_mailer.transport');
        $container->setDefinition('acme_mailer.transport.smtp', $definitionSmtp);
        
        $definitionSendmail = new Definition('\Swift_SendmailTransport');
        $definitionSendmail->addTag('acme_mailer.transport');
        $container->setDefinition('acme_mailer.transport.sendmail', $definitionSendmail);

Notice the tags named "acme_mailer.transport". We want the bundle to recognize
these transports and add them to the chain all by itself. In order to achieve
this, we need to  add a ``build()`` method to the ``AcmeMailerBundle`` class::

    namespace Acme\MailerBundle;
    
    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    
    use Acme\MailerBundle\DependencyInjection\Compiler\TransportCompilerPass;
    
    class AcmeMailerBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);
    
            $container->addCompilerPass(new TransportCompilerPass());
        }
    }

Create a ``CompilerPass``
-------------------------

You will have spotted a reference to the not yet existing ``TransportCompilerPass`` class.
This class will make sure that all services with a tag ``acme_mailer.transport``
will be added to the ``TransportChain`` class by calling the ``addTransport()``
method. The ``TransportCompilerPass`` should look like this::

    namespace Acme\MailerBundle\DependencyInjection\Compiler;
    
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

.. note::

    By convention, tag names consist of the name of the bundle (lowercase,
    underscores as separators), followed by a dot, and finally the "real"
    name, so the tag "transport" in the AcmeMailerBundle should be: ``acme_mailer.transport``.

The Compiled Service Definition
-------------------------------

Adding the compiler pass will result in the automatic generation of the following
lines of code in the compiled service container. In case you are working
in the "dev" environment, open the file ``/cache/dev/appDevDebugProjectContainer.php``
and look for the method ``getTransportChainService()``. It should look like this::

    protected function getAcmeMailer_TransportChainService()
    {
        $this->services['acme_mailer.transport_chain'] = $instance = new \Acme\MailerBundle\TransportChain();

        $instance->addTransport($this->get('acme_mailer.transport.smtp'));
        $instance->addTransport($this->get('acme_mailer.transport.sendmail'));

        return $instance;
    }
