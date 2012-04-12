.. index::
   single: Event Dispatcher; Container Aware; Dependency Injection

The Container Aware Event Dispatcher
====================================

Introduction
------------

The :class:`Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher` is
a special event dispatcher implementation which is coupled to the Symfony2
Dependency Injection Container Component (DIC). It allows DIC services to be
specified as event listeners making the event dispatcher extremely powerful.

Services are lazy loaded meaning the services attached as listeners will only be
created if an event is dispatched that requires those listeners.

Setup
-----

Setup is straightforward by injecting a :class:`Symfony\Component\DependencyInjection\ContainerInterface`
into the :class:`Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher`::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

    $container = new Container();
    $dispatcher = new ContainerAwareEventDispatcher($container);

Adding Listeners
----------------

The ``Container Aware Event Dispatcher`` can either load directly specified
services, or services as defined by :class:`Symfony\Component\EventDispatcher\EventSubscriberInterface`.

The following examples assume the DIC has been loaded with the relevent services.

Adding Services
~~~~~~~~~~~~~~~

Connecting existing service definitions is done with the
:method:`Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher::addListenerService`
method where the ``$callback`` is an array of ``array($serviceId, $methodName)``::

    $dispatcher->addListenerService($eventName, array('foo', 'logListener'));

Adding Subscriber Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

``EventSubscribers`` can be added using the
:method:`Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher::addSubscriberService`
method as follows::

    $dispatcher->addSubscriberService('kernel, 'StoreSubscriber');

The ``EventSubscriberInterface`` will be exactly as you would expect::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class StoreSubscriber implements EventSubscriberInterface
    {
        static public function getSubscribedEvents()
        {
            return array(
                'kernel.response' => array(
                    array('onKernelResponsePre', 10),
                    array('onKernelResponsePost', 0),
                ),
                'store.order'     => array('onStoreOrder', 0),
            );
        }

        public function onKernelResponsePre(FilterResponseEvent $event)
        {
            // ...
        }

        public function onKernelResponsePost(FilterResponseEvent $event)
        {
            // ...
        }

        public function onStoreOrder(FilterOrderEvent $event)
        {
            // ...
        }
    }