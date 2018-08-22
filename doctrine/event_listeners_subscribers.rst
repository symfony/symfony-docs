.. index::
   single: Doctrine; Event listeners and subscribers

.. _doctrine-event-config:
.. _how-to-register-event-listeners-and-subscribers:

Doctrine Event Listeners and Subscribers
========================================

Doctrine has a rich event system that fires when almost anything happens
inside the system. This means that you can create arbitrary
:doc:`services </service_container>` and configure Doctrine to call those
whenever a certain action occurs (e.g. an entity gets persisted).
So instead of having to call some service *every single time* whenever
you're doing something in the database ...

    $userService->doWhateverPrePersist();
    $em->persist($user);

... you could easily set up `doWhateverPrePersist()` as a listener and
then just call `$em->persist($user);` in your main code.

As a first step, you should decide which event you want to listen for. Here
are some popular events - for a full list see `Doctrine's Lifecycle Events`_:

* `prePersist` and `postPersist`
* `preUpdate` and `postUpdate`
* `preRemove` and `postRemove`
* `preFlush` and `postFlush`

The second question would be:

* Should your service only listen for a *specific* entity? Then go for
  Entity Listeners
* Or should your service be called for *all* entities (or at least
  *several* entities)? Then go for Event Listeners or Event Subscribers
  
Entity Listeners
----------------

To set up an entity listener, start by creating a listener class:

.. code-block:: php

    // src/EventListener/UserListener
    namespace App\EventListener;

    use Doctrine\ORM\Event\LifecycleEventArgs;
    use App\Entity\User;

    class UserListener
    {    
        public function prePersist(User $user, LifecycleEventArgs $event)
        {
            // ...
        }
    }

Next, you have to register the class as a listener. There are two ways to
achieve this:

You can either do the registration in your entity like this:

.. code-block:: php

    // src/Entity/User.php
    namespace App\Entity\User;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\EntityListeners({"App\EventListener\UserListener"})
     */
    class User
    {
        // ...
    }

This works fine, but your event listener will not be registered as a service
(even if you have ``autowire: true`` in your ``services.yaml``). So to register
it as a service, you have to add this to your ``services.yaml``:

.. configuration-block::

    .. code-block:: yaml

        services:
            App\EventListener\UserListener:
                tags:
                    - { name: doctrine.orm.entity_listener }

    .. code-block:: xml

        <?xml version="1.0" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

            <services>
                <service id="App\EventListener\UserListener">
                    <tag name="doctrine.orm.entity_listener" />
                </service>
            </services>
        </container>

Alternatively, you could do the entire configuration in ``services.yaml`` and
omit the ``@ORM\EntityListeners`` annotation in the entity:

.. code-block:: yaml

    services:
        App\EventListener\UserListener:
            tags:
                - { name: doctrine.orm.entity_listener, entity: App\Entity\User, event: prePersist }

To register the listener for a custom entity manager, just add the ``entity_manager`` attribute.

For more info on entity listeners, see `Entity Listeners`_

Event Listeners and Event Subscribers
-------------------------------------

Event listeners and subscribers are very similar, but listeners are a bit
more straightforward. For more, see `The Event System`_ on Doctrine's website.

Configuring the Listener/Subscriber
-----------------------------------

To register a service to act as an event listener or subscriber you just have
to :doc:`tag </service_container/tags>` it with the appropriate name. Depending
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
                class: AppBundle\EventListener\SearchIndexer
                tags:
                    - { name: doctrine.event_listener, event: postPersist }
            my.listener2:
                class: AppBundle\EventListener\SearchIndexer2
                tags:
                    - { name: doctrine.event_listener, event: postPersist, connection: default }
            my.subscriber:
                class: AppBundle\EventListener\SearchIndexerSubscriber
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
                <service id="my.listener" class="AppBundle\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener" event="postPersist" />
                </service>
                <service id="my.listener2" class="AppBundle\EventListener\SearchIndexer2">
                    <tag name="doctrine.event_listener" event="postPersist" connection="default" />
                </service>
                <service id="my.subscriber" class="AppBundle\EventListener\SearchIndexerSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\EventListener\SearchIndexer;
        use AppBundle\EventListener\SearchIndexer2;
        use AppBundle\EventListener\SearchIndexerSubscriber;

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
            ->register('my.listener', SearchIndexer::class)
            ->addTag('doctrine.event_listener', array('event' => 'postPersist'))
        ;
        $container
            ->register('my.listener2', SearchIndexer2::class)
            ->addTag('doctrine.event_listener', array(
                'event' => 'postPersist',
                'connection' => 'default',
            ))
        ;
        $container
            ->register('my.subscriber', SearchIndexerSubscriber::class)
            ->addTag('doctrine.event_subscriber', array('connection' => 'default'))
        ;

Creating the Listener Class
---------------------------

In the previous example, a service ``my.listener`` was configured as a Doctrine
listener on the event ``postPersist``. The class behind that service must have
a ``postPersist()`` method, which will be called when the event is dispatched::

    // src/AppBundle/EventListener/SearchIndexer.php
    namespace AppBundle\EventListener;

    use Doctrine\ORM\Event\LifecycleEventArgs;
    use AppBundle\Entity\Product;

    class SearchIndexer
    {
        public function postPersist(LifecycleEventArgs $args)
        {
            $entity = $args->getEntity();

            // only act on some "Product" entity
            if (!$entity instanceof Product) {
                return;
            }

            $entityManager = $args->getEntityManager();
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
    about it in `the Doctrine Documentation`_.

Creating the Subscriber Class
-----------------------------

A Doctrine event subscriber must implement the ``Doctrine\Common\EventSubscriber``
interface and have an event method for each event it subscribes to::

    // src/AppBundle/EventListener/SearchIndexerSubscriber.php
    namespace AppBundle\EventListener;

    use Doctrine\Common\EventSubscriber;
    // for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
    use AppBundle\Entity\Product;

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

            // perhaps you only want to act on some "Product" entity
            if ($entity instanceof Product) {
                $entityManager = $args->getEntityManager();
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
                    <tag name="doctrine.event_listener" event="postPersist" lazy="true" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\EventListener\SearchIndexer;

        $container
            ->register('my.listener', SearchIndexer::class)
            ->addTag('doctrine.event_listener', array('event' => 'postPersist', 'lazy' => 'true'))
        ;

.. note::

    Marking an event listener as ``lazy`` has nothing to do with lazy service
    definitions which are described :doc:`in their own article </service_container/lazy_services>`

.. _`Doctrine's Lifecycle Events`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/events.html#lifecycle-events
.. _`The Event System`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html
.. _`the Doctrine Documentation`: https://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
.. _`Entity Listeners`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/events.html#entity-listeners
