.. index::
   single: Configuration; Semantic
   single: Bundle; Extension Configuration

How to register a custom MIME type for the _format routing parameter
==============================================================================

The _format parameter (see :ref:`Advanced Routing Example<advanced-routing-example>`) 
allows you to set the request format and determines the MIME type delivered to 
the browser in the Content-Type header of the response. Symfony already 
includes several formats and their associated MIME types which can be found in 
:method:`Symfony\\Component\\HttpFoundation\\Request::initializeFormats`. This 
document shows how to add an additional format and MIME type for JSONP.

.. index::
   single: Bundles; Extension
   single: Dependency Injection, Extension

Create an Extension
---------------------

To define a new MIME type, create a new extension that extends
:class:`Symfony\\Component\\DependencyInjection\\Extension\\Extension` 
and loads a services configuration file::

    // HelloBundle/DependencyInjection/HelloExtension.php
    use Symfony\Component\DependencyInjection\Extension\Extension;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class HelloExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            // XML
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.xml');
            
            //
        }

        public function getAlias()
        {
            return 'hello';
        }
    }

Define a core.request Listener Service
--------------------------------------

Within your services config file add a core.request listener service:

.. configuration-block::

    .. code-block:: xml

        <!-- Resources/config/services.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:hello="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://www.example.com/symfony/schema/ http://www.example.com/symfony/schema/hello-1.0.xsd">

           
            <service id="hellobundle.listener.request" class="HelloBundle\RequestListener">
                <tag name="kernel.listener" event="core.request" method="handle" />
            </service>
           ...

        </container>

Create the Request Listener Class
---------------------------------

The core.request listener class is where we will insert our new extension and 
MIME type:::

    // HelloBundle/RequestListener.php

    use Symfony\Component\HttpKernel\HttpKernelInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\EventDispatcher\EventInterface;
    
    class RequestListener
    {
        public function handle(EventInterface $event)
        {
            if (HttpKernelInterface::MASTER_REQUEST !== $event->get('request_type')) {
                return;   
            }
            $request = $event->get('request');
            $request->setFormat('jsonp', 'application/javascript');
        }
    }
