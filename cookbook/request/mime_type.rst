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

Create an ``onCoreRequest`` Listener
------------------------------------

The key to defining a new MIME type is to create a class that will "listen" to
the ``onCoreRequest`` event dispatched by the Symfony kernel. The
``onCoreRequest`` event is dispatched early in Symfony's request handling
process and allows you to modify the request object.

Create the following class, replacing the path with a path to a bundle in your
project::

    // src/Acme/DemoBundle/RequestListener.php
    namespace Acme\DemoBundle;

    use Symfony\Component\HttpKernel\HttpKernelInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;

    class RequestListener
    {
        public function onCoreRequest(GetResponseEvent $event)
        {
            $event->getRequest()->setFormat('jsonp', 'application/javascript');
        }
    }

Registering you Listener
------------------------

As for any other listener, you need to add it in one of your configuration
file and register it as a listener by adding the ``kernel.listener`` tag:

.. configuration-block::

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <service id="acme.demobundle.listener.request" class="Acme\DemoBundle\RequestListener">
                <tag name="kernel.listener" event="onCoreRequest" />
            </service>

        </container>

    .. code-block:: yaml

        # app/config/config.yml
        services:
            acme.demobundle.listener.request:
                class: Acme\DemoBundle\RequestListener
                tags:
                    - { name: kernel.listener event: onCoreRequest }

    .. code-block:: php

        # app/config/config.php
        $definition = new Definition('Acme\DemoBundle\RequestListener');
        $definition->addTag('kernel.listener', array('event' => 'onCoreRequest'));
        $container->setDefinition('acme.demobundle.listener.request', $definition);

At this point, the ``acme.demobundle.listener.request`` service has been
configured and will be notified when the Symfony kernel dispatches the
``onCoreRequest`` event.

.. tip::

    You can also register the listener in a configuration extension class (see
    :ref:`service-container-extension-configuration` for more information).
