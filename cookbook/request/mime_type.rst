.. index::
   single: Request; Add a request format and mime type

How to register a new Request Format and Mime Type
==================================================

Every ``Request`` has a "format" (e.g. ``html``, ``json``), which is used
to determine what type of content to return in the ``Response``. In fact,
the request format, accessible via
:method:`Symfony\\Component\\HttpFoundation\\Request::getRequestFormat`,
is used to set the MIME type of the ``Content-Type`` header on the ``Response``
object. Internally, Symfony contains a map of the most common formats (e.g.
``html``, ``json``) and their associated MIME types (e.g. ``text/html``,
``application/json``). Of course, additional format-MIME type entries can
easily be added. This document will show how you can add the ``jsonp`` format
and corresponding MIME type.

.. index::
   single: Bundles; Extension
   single: Dependency Injection, Extension

Create an ``onCoreRequest`` Listener
------------------------------------

The key to defining a new MIME type, or making any other global change to
the ``Request`` object, is to create a new class that will "listen" to the
``onCoreRequest`` event thrown by the Symfony kernel. The ``onCoreRequest``
event is thrown early in Symfony's bootstrapping process. By listening to
this event, you can make any modifications needed to the request object.

To define a new MIME type, create a new configuration extension class that
loads a service configuration file (e.g. ``services.xml``). A configuration
extension class is one useful way to modify configuration from inside a
bundle (see :ref:`service-container-extension-configuration` for more information).

.. note::

   This guide places all of the code inside the ``Acme\DemoBundle`` namespace.
   Of course, the code can live inside any bundle in your project. Be sure
   to update the paths and namespaces accordingly.

.. code-block:: php

    // src/Acme/DemoBundle/DependencyInjection/AcmeDemoExtension.php
    namespace Acme\DemoBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\Config\FileLocator;

    class AcmeDemoExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.xml');
        }

        public function getAlias()
        {
            return 'acme_demo';
        }
    }

.. tip::

    If you're more comfortable defining configuration using either YAML
    or PHP, that's no problem. Simply change the ``XmlFileLoader`` namespace
    and class declaration to ``YamlFileLoader`` or ``PhpFileLoader`` and
    then load either ``services.yml`` or ``services.php``. You'll see how
    each configuration file would look next.

Now that the ``services.xml`` file is being loaded, you can use it to do the
following:

 * Define a service that will add the request format to the ``Request`` object;

 * Tag the service correctly so that it is notified when the ``onCoreRequest``
   event is thrown.

.. configuration-block::

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/services.xml -->
        <?xml version="1.0" ?>

		<container xmlns="http://symfony.com/schema/dic/services"
		    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service id="acme.demobundle.listener.request" class="Acme\DemoBundle\RequestListener">
                <tag name="kernel.listener" event="onCoreRequest" />
            </service>

           <!-- ... -->

        </container>

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/services.yml
        acme.demobundle.listener.request:
            class: Acme\DemoBundle\RequestListener
            tags:
                - { name: kernel.listener event: onCoreRequest }

    .. code-block:: php
    
        # src/Acme/DemoBundle/Resources/config/services.php
        
        $definition = new Definition('Acme\DemoBundle\RequestListener');
        $definition->addTag('kernel.listener', array('event' => 'onCoreRequest'));
        $container->setDefinition('acme.demobundle.listener.request', $definition);

At this point, the ``acme.demobundle.listener.request`` service has been
configured and will be notified when the Symfony kernel throws the ``onCoreRequest``
event.

Create the Request Listener Class
---------------------------------

All that's left now is to create the actual class that will modify the ``Request``
object. Create the following class, replacing the path with a path to a bundle in
your project:

.. code-block:: php

    // src/Acme/DemoBundle/RequestListener.php

    namespace Acme\DemoBundle;

    use Symfony\Component\HttpKernel\HttpKernelInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    
    class RequestListener
    {
        public function onCoreRequest(GetResponseEvent $event)
        {
            $request = $event->getRequest();
            $request->setFormat('jsonp', 'application/javascript');
        }
    }

Looking Back
------------

Adding a new request format and mime type to Symfony is easy, and ultimately
involves calling the ``setFormat`` method on the ``Request`` object. To
be sure that the ``Request`` object is modified at a global level, you can
listen to one of the Symfony kernel events (``onCoreRequest``), allowing
you to modifying anything related to the ``Request`` object.