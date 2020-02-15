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
more straightforward. For more, see `The Event System`_ on Doctrine's documentation.

Before using them, keep in mind that Doctrine events are intended for
persistence hooks (i.e. *"save also this when saving that"*). They should not be
used for domain logic, such as logging changes, setting ``updatedAt`` and
``createdAt`` properties, etc.

.. seealso::

    This article covers listeners and subscribers for Doctrine ORM. If you are
    using ODM for MongoDB, read the `DoctrineMongoDBBundle documentation`_.

Configuring the Listener/Subscriber
-----------------------------------

To register a service to act as an event listener or subscriber you just have
to :doc:`tag </service_container/tags>` it with the appropriate name. Depending
on your use-case, you can hook a listener into every DBAL connection and ORM
entity manager or just into one specific DBAL connection and all the entity
managers that use this connection.

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            AppBundle\EventListener\SearchIndexer:
                tags:
                    - { name: doctrine.event_listener, event: postPersist }
            AppBundle\EventListener\SearchIndexer2:
                tags:
                    - { name: doctrine.event_listener, event: postPersist, connection: default }
            AppBundle\EventListener\SearchIndexerSubscriber:
                tags:
                    - { name: doctrine.event_subscriber, connection: default }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">
            <services>
                <!-- ... -->

                <service id="AppBundle\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener" event="postPersist"/>
                </service>
                <service id="AppBundle\EventListener\SearchIndexer2">
                    <tag name="doctrine.event_listener" event="postPersist" connection="default"/>
                </service>
                <service id="AppBundle\EventListener\SearchIndexerSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\EventListener\SearchIndexer;
        use AppBundle\EventListener\SearchIndexer2;
        use AppBundle\EventListener\SearchIndexerSubscriber;

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

    // src/AppBundle/EventListener/SearchIndexer.php
    namespace AppBundle\EventListener;

    use AppBundle\Entity\Product;
    // for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\Persistence\Event\LifecycleEventArgs;

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

    // src/AppBundle/EventListener/SearchIndexerSubscriber.php
    namespace AppBundle\EventListener;

    use AppBundle\Entity\Product;
    use Doctrine\Common\EventSubscriber;
    // for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\ORM\Events;
    use Doctrine\Persistence\Event\LifecycleEventArgs;

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

Lazy loading for Event Listeners
--------------------------------

One subtle difference between listeners and subscribers is that Symfony can load
entity listeners lazily. This means that your listener class will only be fetched
from the service container (and thus be instantiated) once the event it is linked
to actually fires.

Lazy loading might give you a slight performance improvement when your listener
runs for events that rarely fire. Also, it can help you when you run into
*circular dependency issues* that may occur when your listener service in turn
depends on the DBAL connection.

To mark a listener service as lazily loaded, just add the ``lazy`` attribute
to the tag like so:

.. configuration-block::

    .. code-block:: yaml

        services:
            my.listener:
                class: AppBundle\EventListener\SearchIndexer
                tags:
                    - { name: doctrine.event_listener, event: postPersist, lazy: true }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <services>
                <service id="my.listener" class="AppBundle\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener" event="postPersist" lazy="true"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\EventListener\SearchIndexer;

        $container
            ->register('my.listener', SearchIndexer::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist', 'lazy' => 'true'])
        ;

.. note::

    Marking an event listener as ``lazy`` has nothing to do with lazy service
    definitions which are described :doc:`in their own article </service_container/lazy_services>`

Priorities for Event Listeners
------------------------------

In case you have multiple listeners for the same event you can control the order
in which they are invoked using the ``priority`` attribute on the tag. Priorities
are defined with positive or negative integers (they default to ``0``). Higher
numbers mean that listeners are invoked earlier.

.. configuration-block::

    .. code-block:: yaml

        services:
            my.listener.with_high_priority:
                class: AppBundle\EventListener\MyHighPriorityListener
                tags:
                    - { name: doctrine.event_listener, event: postPersist, priority: 10 }

            my.listener.with_low_priority:
                class: AppBundle\EventListener\MyLowPriorityListener
                tags:
                    - { name: doctrine.event_listener, event: postPersist, priority: 1 }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <services>
                <service id="my.listener.with_high_priority" class="AppBundle\EventListener\MyHighPriorityListener">
                    <tag name="doctrine.event_listener" event="postPersist" priority="10"/>
                </service>
                <service id="my.listener.with_low_priority" class="AppBundle\EventListener\MyLowPriorityListener">
                    <tag name="doctrine.event_listener" event="postPersist" priority="1"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\EventListener\MyHighPriorityListener;
        use AppBundle\EventListener\MyLowPriorityListener;

        $container
            ->register('my.listener.with_high_priority', MyHighPriorityListener::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist', 'priority' => 10])
        ;

        $container
            ->register('my.listener.with_low_priority', MyLowPriorityListener::class)
            ->addTag('doctrine.event_listener', ['event' => 'postPersist', 'priority' => 1])
        ;

.. _`The Event System`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html
.. _`the DoctrineBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
.. _`DoctrineMongoDBBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html
