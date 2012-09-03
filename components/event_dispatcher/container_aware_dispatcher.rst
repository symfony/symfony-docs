.. index::
   single: Event Dispatcher; Container Aware; Dependency Injection; DIC

The Container Aware Event Dispatcher
====================================

.. versionadded:: 2.1
    This feature was moved into the EventDispatcher component in Symfony 2.1.

Introduction
------------

The :class:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher` is
a special event dispatcher implementation which is coupled to the Symfony2
Dependency Injection Container Component (DIC). It allows DIC services to be
specified as event listeners making the event dispatcher extremely powerful.

Services are lazy loaded meaning the services attached as listeners will only be
created if an event is dispatched that requires those listeners.

Setup
-----

Setup is straightforward by injecting a :class:`Symfony\\Component\\DependencyInjection\\ContainerInterface`
into the :class:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher`::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

    $container = new ContainerBuilder();
    $dispatcher = new ContainerAwareEventDispatcher($container);

Adding Listeners
----------------

The *Container Aware Event Dispatcher* can either load specified services
directly, or services that implement :class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`.

The following examples assume the DIC has been loaded with any services that
are mentioned.

.. note::

    Services must be marked as public in the DIC.

Adding Services
~~~~~~~~~~~~~~~

To connect existing service definitions, use the
:method:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher::addListenerService`
method where the ``$callback`` is an array of ``array($serviceId, $methodName)``::

    $dispatcher->addListenerService($eventName, array('foo', 'logListener'));

Adding Subscriber Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

``EventSubscribers`` can be added using the
:method:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher::addSubscriberService`
method where the first argument is the service ID of the subscriber service,
and the second argument is the the service's class name (which must implement
:class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`) as follows::

    $dispatcher->addSubscriberService('kernel.store_subscriber', 'StoreSubscriber');

The ``EventSubscriberInterface`` will be exactly as you would expect::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    // ...

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