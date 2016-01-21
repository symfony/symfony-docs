.. index::
   single: EventDispatcher; Service container aware

The Container Aware Event Dispatcher
====================================

Introduction
------------

The :class:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher`
is a special ``EventDispatcher`` implementation which is coupled to the
service container that is part of
:doc:`the DependencyInjection component </components/dependency_injection/introduction>`.
It allows services to be specified as event listeners making the ``EventDispatcher``
extremely powerful.

Services are lazy loaded meaning the services attached as listeners will
only be created if an event is dispatched that requires those listeners.

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

The ``ContainerAwareEventDispatcher`` can either load specified services
directly or services that implement :class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`.

The following examples assume the service container has been loaded with
any services that are mentioned.

.. note::

    Services must be marked as public in the container.

Adding Services
~~~~~~~~~~~~~~~

To connect existing service definitions, use the
:method:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher::addListenerService`
method where the ``$callback`` is an array of ``array($serviceId, $methodName)``::

    $dispatcher->addListenerService($eventName, array('foo', 'logListener'));

Adding Subscriber Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

Event subscribers can be added using the
:method:`Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher::addSubscriberService`
method where the first argument is the service ID of the subscriber service,
and the second argument is the service's class name (which must implement
:class:`Symfony\\Component\\EventDispatcher\\EventSubscriberInterface`) as follows::

    $dispatcher->addSubscriberService(
        'kernel.store_subscriber',
        'StoreSubscriber'
    );

The ``EventSubscriberInterface`` is exactly as you would expect::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    // ...

    class StoreSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
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
