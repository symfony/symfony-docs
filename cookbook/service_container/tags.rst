How to Make Your Services Use Tags
==================================

Several of Symfony2's core services depend on tags to recognize which services 
should be loaded, notified of events, etc. For example, Twig uses the tag 
``twig.extension`` to load extra extensions.

But you can also use tags in your own bundles. For example in case your service  
handles a collection of some kind, or implements a "chain", in which several alternative 
strategies are tried until one of them is successful. In this article I will use the example 
of a "transport chain", which is a collection of classes implementing ``\Swift_Transport``. 
Using the chain, the Swift mailer may try several ways of transport, until one succeeds. 
This post focuses mainly on the dependency injection part of the story.

To begin with, define the ``TransportChain`` class.

    namespace Acme\TransportBundle;
    
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

        # src/Acme/TransportBundle/Resources/config/services.yml
        parameters:
            transport_chain.class: Acme\TransportBundle\TransportChain
        
        services:
            transport_chain:
                class: %transport_chain.class%

    .. code-block:: xml

        <!-- src/Acme/TransportBundle/Resources/config/services.xml -->

        <parameters>
            <parameter key="transport_chain.class">Acme\TransportBundle\TransportChain</parameter>
        </parameters>
    
        <services>
            <service id="transport_chain" class="%transport_chain.class%" />
        </services>
        
    .. code-block:: php
    
        // src/Acme/TransportBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $container->setParameter('transport_chain.class', 'Acme\TransportBundle\TransportChain');
        
        $container->setDefinition('transport_chain', new Definition('%transport_chain.class'));

Define Services with a Custom Tag
---------------------------------

Now we want several of the ``\Swift_Transport`` classes to be instantiated and added to the chain 
automatically using the ``addTransport()`` method. As an example we add the following transports 
as services:

.. configuration-block::

    .. code-block:: yaml
    
        services:
            transport.smtp:
                class: \Swift_SmtpTransport
                arguments:
                    - %mailer_host%
                tags:
                    -  { name: mailer.transport }
            transport.sendmail:
                class: \Swift_SendmailTransport
                tags:
                    -  { name: mailer.transport }
    
    .. code-block:: xml

        <service id="transport.smtp" class="\Swift_SmtpTransport">
            <argument>%mailer_host%</argument>
            <tag name="mailer.transport" />
        </service>
    
        <service id="transport.sendmail" class="\Swift_SendmailTransport">
            <tag name="mailer.transport" />
        </service>
        
    .. code-block:: php
    
        // src/Acme/TransportBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $definitionSmtp = new Definition('\Swift_SmtpTransport', array('%mailer_host%'));
        $definitionSmtp->addTag('mailer.transport');
        $container->setDefinition('transport.smtp', $definitionSmtp);
        
        $definitionSendmail = new Definition('\Swift_SendmailTransport');
        $definitionSendmail->addTag('mailer.transport');
        $container->setDefinition('transport.sendmail', $definitionSendmail);

Notice the tags named "mailer.transport". We want the bundle to recognize these transports 
and add them to the chain all by itself. In order to achieve this, we need to 
add a ``build()`` method to the ``AcmeTransportBundle`` class:

    namespace Acme\TransportBundle;
    
    use Symfony\Component\HttpKernel\Bundle\Bundle;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    
    use Acme\TransportBundle\DependencyInjection\Compiler\TransportCompilerPass;
    
    class AcmeTransportBundle extends Bundle
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
This class will make sure that all services with a tag "mailer.transport" will be added to 
the ``TransportChain`` class by calling the ``addTransport()`` method. 
The ``TransportCompilerPass`` should look like this:

    namespace Acme\TransportBundle\DependencyInjection\Compiler;
    
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\Reference;
    
    class TransportCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            if (false === $container->hasDefinition('transport_chain')) {
                return;
            }
    
            $definition = $container->getDefinition('transport_chain');
    
            foreach ($container->findTaggedServiceIds('mailer.transport') as $id => $attributes) {
                $definition->addMethodCall('addTransport', array(new Reference($id)));
            }
        }
    }

The ``process()`` method checks for the existence of the ``transport_chain`` service, 
then looks for all services tagged "mailer.transport". It adds to the definition of the 
``transport_chain`` service a call to ``addTransport()`` for each "mailer.transport" service 
it has found. The first argument of each of these calls will be the mailer transport service itself.

The Compiled Service Definition
-------------------------------

Adding the compiler pass will result in the automatic generation of the following lines of code 
in the compiled service container. In case you are working in the "dev" environment, open the file 
``/cache/dev/appDevDebugProjectContainer.php`` and look for the method ``getTransportChainService()``.
It should look like this:

    protected function getTransportChainService()
    {
        $this->services['transport_chain'] = $instance = new \Acme\TransportBundle\TransportChain();

        $instance->addTransport($this->get('transport.smtp'));
        $instance->addTransport($this->get('transport.sendmail'));

        return $instance;
    }
