.. index::
   single: Doctrine; Event listeners and subscribers

.. _doctrine-event-config:
.. _how-to-register-event-listeners-and-subscribers:

Doctrine Event Listeners and Subscribers
========================================

Doctrine packages have a rich event system that fires events when almost anything
happens inside the system. For you, this means that you can create arbitrary
:doc:`services </service_container>` and tell Doctrine to notify those
objects whenever a certain action (e.g. ``prePersist()``) happens within Doctrine.
This could be useful, for example, to create an independent search index
whenever an object in your database is saved.

Doctrine defines two types of objects that can listen to Doctrine events:
listeners and subscribers. Both are very similar, but listeners are a bit
more straightforward. For more, see `The Event System`_ on Doctrine's website.

The Doctrine website also explains all existing events that can be listened to.

Configuring the Listener/Subscriber
-----------------------------------

To register a service to act as an event listener or subscriber you have
to :doc:`tag </service_container/tags>` it with the appropriate name. Depending
on your use-case, you can hook a listener into every DBAL connection and ORM
entity manager or just into one specific DBAL connection and all the entity
managers that use this connection.

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            App\EventListener\SearchIndexer:
                tags:
                    - { name: doctrine.event_listener, event: postPersist }
            App\EventListener\SearchIndexer2:
                tags:
                    - { name: doctrine.event_listener, event: postPersist, connection: default }
            App\EventListener\SearchIndexerSubscriber:
                tags:
                    - { name: doctrine.event_subscriber, connection: default }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">
            <services>
                <!-- ... -->

                <service id="App\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener" event="postPersist"/>
                </service>
                <service id="App\EventListener\SearchIndexer2">
                    <tag name="doctrine.event_listener" event="postPersist" connection="default"/>
                </service>
                <service id="App\EventListener\SearchIndexerSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use App\EventListener\SearchIndexer;
        use App\EventListener\SearchIndexer2;
        use App\EventListener\SearchIndexerSubscriber;

        $container->autowire(SearchIndexer::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist'])
        ;
        $container->autowire(SearchIndexer2::class)
            ->addTag('doctrine.event_listener', [
                'event' => 'postPersist',
                'connection' => 'default',
            ])
        ;
        $container->autowire(SearchIndexerSubscriber::class)
            ->addTag('doctrine.event_subscriber', ['connection' => 'default'])
        ;

Creating the Listener Class
---------------------------

In the previous example, a ``SearchIndexer`` service was configured as a Doctrine
listener on the event ``postPersist``. The class behind that service must have
a ``postPersist()`` method, which will be called when the event is dispatched::

    // src/EventListener/SearchIndexer.php
    namespace App\EventListener;

    // for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
    use App\Entity\Product;

    class SearchIndexer
    {
        public function postPersist(LifecycleEventArgs $args)
        {
            $entity = $args->getObject();

            // only act on some "Product" entity
            if (!$entity instanceof Product) {
                return;
            }

            $entityManager = $args->getObjectManager();
            // ... do something with the Product
        }
    }

In each event, you have access to a ``LifecycleEventArgs`` object, which
gives you access to both the entity object of the event and the entity manager
itself.

One important thing to notice is that a listener will be listening for *all*
entities in your application. So, if you're interested in only handling a
specific type of entity (e.g. a ``Product`` entity but not a ``BlogPost``
entity), you should check for the entity's class type in your method
(as shown above).

.. tip::

    In Doctrine 2.4, a feature called Entity Listeners was introduced.
    It is a lifecycle listener class used for an entity. You can read
    about it in `the DoctrineBundle documentation`_.

Creating the Subscriber Class
-----------------------------

A Doctrine event subscriber must implement the ``Doctrine\Common\EventSubscriber``
interface and have an event method for each event it subscribes to::

    // src/EventListener/SearchIndexerSubscriber.php
    namespace App\EventListener;

    use Doctrine\Common\EventSubscriber;
    // for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
    use App\Entity\Product;
    use Doctrine\ORM\Events;

    class SearchIndexerSubscriber implements EventSubscriber
    {
        public function getSubscribedEvents()
        {
            return [
                Events::postPersist,
                Events::postUpdate,
            ];
        }

        public function postUpdate(LifecycleEventArgs $args)
        {
            $this->index($args);
        }

        public function postPersist(LifecycleEventArgs $args)
        {
            $this->index($args);
        }

        public function index(LifecycleEventArgs $args)
        {
            $entity = $args->getObject();

            // perhaps you only want to act on some "Product" entity
            if ($entity instanceof Product) {
                $entityManager = $args->getObjectManager();
                // ... do something with the Product
            }
        }
    }

.. tip::

    Doctrine event subscribers cannot return a flexible array of methods to
    call for the events like the :ref:`Symfony event subscriber <event_dispatcher-using-event-subscribers>`
    can. Doctrine event subscribers must return a simple array of the event
    names they subscribe to. Doctrine will then expect methods on the subscriber
    with the same name as each subscribed event, just as when using an event listener.

For a full reference, see chapter `The Event System`_ in the Doctrine documentation.

Performance Considerations
--------------------------

One important difference between listeners and subscribers is that Symfony loads
entity listeners lazily. This means that the listener classes are only fetched
from the service container (and instantiated) if the related event is actually
fired.

That's why it is preferable to use entity listeners instead of subscribers
whenever possible.

Priorities for Event Listeners
------------------------------

In case you have multiple listeners for the same event you can control the order
in which they are invoked using the ``priority`` attribute on the tag. Priorities
are defined with positive or negative integers (they default to ``0``). Higher
numbers mean that listeners are invoked earlier.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\EventListener\MyHighPriorityListener:
                tags:
                    - { name: doctrine.event_listener, event: postPersist, priority: 10 }

            App\EventListener\MyLowPriorityListener:
                tags:
                    - { name: doctrine.event_listener, event: postPersist, priority: 1 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <services>
                <service id="App\EventListener\MyHighPriorityListener" autowire="true">
                    <tag name="doctrine.event_listener" event="postPersist" priority="10"/>
                </service>
                <service id="App\EventListener\MyLowPriorityListener" autowire="true">
                    <tag name="doctrine.event_listener" event="postPersist" priority="1"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\EventListener\MyHighPriorityListener;
        use App\EventListener\MyLowPriorityListener;

        $container
            ->autowire(MyHighPriorityListener::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist', 'priority' => 10])
        ;

        $container
            ->autowire(MyLowPriorityListener::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist', 'priority' => 1])
        ;

.. _`The Event System`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html
.. _`the DoctrineBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
