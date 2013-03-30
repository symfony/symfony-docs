.. index::
   single: Doctrine; Event listeners and subscribers

.. _doctrine-event-config:

How to Register Event Listeners and Subscribers
===============================================

Doctrine packages a rich event system that fires events when almost anything
happens inside the system. For you, this means that you can create arbitrary
:doc:`services</book/service_container>` and tell Doctrine to notify those
objects whenever a certain action (e.g. ``prePersist``) happens within Doctrine.
This could be useful, for example, to create an independent search index
whenever an object in your database is saved.

Doctrine defines two types of objects that can listen to Doctrine events:
listeners and subscribers. Both are very similar, but listeners are a bit
more straightforward. For more, see `The Event System`_ on Doctrine's website.

The Doctrine website also explains all existing events that can be listened to.

Configuring the Listener/Subscriber
-----------------------------------

To register a service to act as an event listener or subscriber you just have
to :ref:`tag<book-service-container-tags>` it with the appropriate name. Depending
on your use-case, you can hook a listener into every DBAL connection and ORM
entity manager or just into one specific DBAL connection and all the entity
managers that use this connection.

.. configuration-block::

    .. code-block:: yaml

        doctrine:
            dbal:
                default_connection: default
                connections:
                    default:
                        driver: pdo_sqlite
                        memory: true

        services:
            my.listener:
                class: Acme\SearchBundle\EventListener\SearchIndexer
                tags:
                    - { name: doctrine.event_listener, event: postPersist }
            my.listener2:
                class: Acme\SearchBundle\EventListener\SearchIndexer2
                tags:
                    - { name: doctrine.event_listener, event: postPersist, connection: default }
            my.subscriber:
                class: Acme\SearchBundle\EventListener\SearchIndexerSubscriber
                tags:
                    - { name: doctrine.event_subscriber, connection: default }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">

            <doctrine:config>
                <doctrine:dbal default-connection="default">
                    <doctrine:connection driver="pdo_sqlite" memory="true" />
                </doctrine:dbal>
            </doctrine:config>

            <services>
                <service id="my.listener" class="Acme\SearchBundle\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener" event="postPersist" />
                </service>
                <service id="my.listener2" class="Acme\SearchBundle\EventListener\SearchIndexer2">
                    <tag name="doctrine.event_listener" event="postPersist" connection="default" />
                </service>
                <service id="my.subscriber" class="Acme\SearchBundle\EventListener\SearchIndexerSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->loadFromExtension('doctrine', array(
            'dbal' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'driver' => 'pdo_sqlite',
                        'memory' => true,
                    ),
                ),
            ),
        ));

        $container
            ->setDefinition(
                'my.listener',
                new Definition('Acme\SearchBundle\EventListener\SearchIndexer')
            )
            ->addTag('doctrine.event_listener', array('event' => 'postPersist'))
        ;
        $container
            ->setDefinition(
                'my.listener2',
                new Definition('Acme\SearchBundle\EventListener\SearchIndexer2')
            )
            ->addTag('doctrine.event_listener', array('event' => 'postPersist', 'connection' => 'default'))
        ;
        $container
            ->setDefinition(
                'my.subscriber',
                new Definition('Acme\SearchBundle\EventListener\SearchIndexerSubscriber')
            )
            ->addTag('doctrine.event_subscriber', array('connection' => 'default'))
        ;

Creating the Listener Class
---------------------------

In the previous example, a service ``my.listener`` was configured as a Doctrine
listener on the event ``postPersist``. The class behind that service must have
a ``postPersist`` method, which will be called when the event is dispatched::

    // src/Acme/SearchBundle/EventListener/SearchIndexer.php
    namespace Acme\SearchBundle\EventListener;

    use Doctrine\ORM\Event\LifecycleEventArgs;
    use Acme\StoreBundle\Entity\Product;

    class SearchIndexer
    {
        public function postPersist(LifecycleEventArgs $args)
        {
            $entity = $args->getEntity();
            $entityManager = $args->getEntityManager();

            // perhaps you only want to act on some "Product" entity
            if ($entity instanceof Product) {
                // ... do something with the Product
            }
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

Creating the Subscriber Class
-----------------------------

A doctrine event subscriber must implement the ``Doctrine\Common\EventSubscriber``
interface and have an event method for each event it subscribes to::

    // src/Acme/SearchBundle/EventListener/SearchIndexerSubscriber.php
    namespace Acme\SearchBundle\EventListener;

    use Doctrine\Common\EventSubscriber;
    use Doctrine\ORM\Event\LifecycleEventArgs;
    // for doctrine 2.4: Doctrine\Common\Persistence\Event\LifecycleEventArgs;
    use Acme\StoreBundle\Entity\Product;

    class SearchIndexerSubscriber implements EventSubscriber
    {
        public function getSubscribedEvents()
        {
            return array(
                'postPersist',
                'postUpdate',
            );
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
            $entity = $args->getEntity();
            $entityManager = $args->getEntityManager();

            // perhaps you only want to act on some "Product" entity
            if ($entity instanceof Product) {
                // ... do something with the Product
            }
        }
    }

.. tip::

    Doctrine event subscribers can not return a flexible array of methods to
    call for the events like the :ref:`Symfony event subscriber <event_dispatcher-using-event-subscribers>`
    can. Doctrine event subscribers must return a simple array of the event
    names they subscribe to. Doctrine will then expect methods on the subscriber
    with the same name as each subscribed event, just as when using an event listener.

For a full reference, see chapter `The Event System`_ in the Doctrine documentation.

.. _`The Event System`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html
