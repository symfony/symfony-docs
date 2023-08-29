Doctrine Events
===============

`Doctrine`_, the set of PHP libraries used by Symfony to work with databases,
provides a lightweight event system to update entities during the application
execution. These events, called `lifecycle events`_, allow to perform tasks such
as *"update the createdAt property automatically right before persisting entities
of this type"*.

Doctrine triggers events before/after performing the most common entity
operations (e.g. ``prePersist/postPersist``, ``preUpdate/postUpdate``) and also
on other common tasks (e.g. ``loadClassMetadata``, ``onClear``).

There are different ways to listen to these Doctrine events:

* **Lifecycle callbacks**, they are defined as public methods on the entity classes and
  they are called when the events are triggered;
* **Lifecycle listeners**, they are classes with callback
  methods for one or more events and they are called for all entities;
* **Entity listeners**, they are similar to lifecycle listeners, but they are
  called only for the entities of a certain class.

These are the **drawbacks and advantages** of each one:

* Callbacks have better performance because they only apply to a single entity
  class, but you can't reuse the logic for different entities and they don't
  have access to :doc:`Symfony services </service_container>`;
* Lifecycle listeners can reuse logic among different entities
  and can access Symfony services but their performance is worse because they
  are called for all entities;
* Entity listeners have the same advantages of lifecycle listeners and they have
  better performance because they only apply to a single entity class.

This article only explains the basics about Doctrine events when using them
inside a Symfony application. Read the `official docs about Doctrine events`_
to learn everything about them.

.. seealso::

    This article covers listeners for Doctrine ORM. If you are
    using ODM for MongoDB, read the `DoctrineMongoDBBundle documentation`_.

Doctrine Lifecycle Callbacks
----------------------------

Lifecycle callbacks are defined as public methods inside the entity you want to modify.
For example, suppose you want to set a ``createdAt`` date column to the current
date, but only when the entity is first persisted (i.e. inserted). To do so,
define a callback for the ``prePersist`` Doctrine event:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Product.php
        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;

        // When using attributes, don't forget to add #[ORM\HasLifecycleCallbacks]
        // to the class of the entity where you define the callback

        #[ORM\Entity]
        #[ORM\HasLifecycleCallbacks]
        class Product
        {
            // ...

            #[ORM\PrePersist]
            public function setCreatedAtValue(): void
            {
                $this->createdAt = new \DateTimeImmutable();
            }
        }

    .. code-block:: yaml

        # config/doctrine/Product.orm.yml
        App\Entity\Product:
            type: entity
            # ...
            lifecycleCallbacks:
                prePersist: ['setCreatedAtValue']

    .. code-block:: xml

        <!-- config/doctrine/Product.orm.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                https://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <entity name="App\Entity\Product">
                <!-- ... -->
                <lifecycle-callbacks>
                    <lifecycle-callback type="prePersist" method="setCreatedAtValue"/>
                </lifecycle-callbacks>
            </entity>
        </doctrine-mapping>

.. note::

    Some lifecycle callbacks receive an argument that provides access to
    useful information such as the current entity manager (e.g. the ``preUpdate``
    callback receives a ``PreUpdateEventArgs $event`` argument).

.. _doctrine-lifecycle-listener:

Doctrine Lifecycle Listeners
----------------------------

Lifecycle listeners are defined as PHP classes that listen to a single Doctrine
event on all the application entities. For example, suppose that you want to
update some search index whenever a new entity is persisted in the database. To
do so, define a listener for the ``postPersist`` Doctrine event::

    // src/EventListener/SearchIndexer.php
    namespace App\EventListener;

    use App\Entity\Product;
    use Doctrine\ORM\Event\PostPersistEventArgs;

    class SearchIndexer
    {
        // the listener methods receive an argument which gives you access to
        // both the entity object of the event and the entity manager itself
        public function postPersist(PostPersistEventArgs $args): void
        {
            $entity = $args->getObject();

            // if this listener only applies to certain entity types,
            // add some code to check the entity type as early as possible
            if (!$entity instanceof Product) {
                return;
            }

            $entityManager = $args->getObjectManager();
            // ... do something with the Product entity
        }
    }

.. note::

    In previous Doctrine versions, instead of ``PostPersistEventArgs``, you had
    to use ``LifecycleEventArgs``, which was deprecated in Doctrine ORM 2.14.

Then, add the ``#[AsDoctrineListener]`` attribute to the class to enable it as
a Doctrine listener in your application::

        // src/EventListener/SearchIndexer.php
        namespace App\EventListener;

        use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
        use Doctrine\ORM\Events;

        #[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
        class SearchIndexer
        {
            // ...
        }

Alternatively, if you prefer to not use PHP attributes, you must enable the
listener in the Symfony application by creating a new service for it and
:doc:`tagging it </service_container/tags>` with the ``doctrine.event_listener`` tag:

.. configuration-block::

    .. code-block:: php-attributes

        // src/EventListener/SearchIndexer.php
        namespace App\EventListener;

        use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
        use Doctrine\ORM\Event\PostPersistEventArgs;

        #[AsDoctrineListener('postPersist'/*, 500, 'default'*/)]
        class SearchIndexer
        {
            public function postPersist(PostPersistEventArgs $event): void
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\EventListener\SearchIndexer:
                tags:
                    -
                        name: 'doctrine.event_listener'
                        # this is the only required option for the lifecycle listener tag
                        event: 'postPersist'

                        # listeners can define their priority in case listeners are associated
                        # to the same event (default priority = 0; higher numbers = listener is run earlier)
                        priority: 500

                        # you can also restrict listeners to a specific Doctrine connection
                        connection: 'default'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">
            <services>
                <!-- ... -->

                <!--
                    * 'event' is the only required option that defines the lifecycle listener
                    * 'priority': used when multiple listeners are associated to the same event
                    *             (default priority = 0; higher numbers = listener is run earlier)
                    * 'connection': restricts the listener to a specific Doctrine connection
                -->
                <service id="App\EventListener\SearchIndexer">
                    <tag name="doctrine.event_listener"
                        event="postPersist"
                        priority="500"
                        connection="default"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\EventListener\SearchIndexer;

        return static function (ContainerConfigurator $container): void {
            $services = $container->services();

            // listeners are applied by default to all Doctrine connections
            $services->set(SearchIndexer::class)
                ->tag('doctrine.event_listener', [
                    // this is the only required option for the lifecycle listener tag
                    'event' => 'postPersist',

                    // listeners can define their priority in case multiple listeners are associated
                    // to the same event (default priority = 0; higher numbers = listener is run earlier)
                    'priority' => 500,

                    # you can also restrict listeners to a specific Doctrine connection
                    'connection' => 'default',
                ])
            ;
        };

.. versionadded:: 2.7.2

    The `AsDoctrineListener`_ attribute was introduced in DoctrineBundle 2.7.2.

.. tip::

    The value of the ``connection`` option can also be a
    :ref:`configuration parameter <configuration-parameters>`.

Doctrine Entity Listeners
-------------------------

Entity listeners are defined as PHP classes that listen to a single Doctrine
event on a single entity class. For example, suppose that you want to send some
notifications whenever a ``User`` entity is modified in the database.

First, define a PHP class that handles the ``postUpdate`` Doctrine event::

    // src/EventListener/UserChangedNotifier.php
    namespace App\EventListener;

    use App\Entity\User;
    use Doctrine\ORM\Event\PostUpdateEventArgs;

    class UserChangedNotifier
    {
        // the entity listener methods receive two arguments:
        // the entity instance and the lifecycle event
        public function postUpdate(User $user, PostUpdateEventArgs $event): void
        {
            // ... do something to notify the changes
        }
    }

Then, add the ``#[AsEntityListener]`` attribute to the class to enable it as
a Doctrine entity listener in your application::

        // src/EventListener/UserChangedNotifier.php
        namespace App\EventListener;

        // ...
        use App\Entity\User;
        use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
        use Doctrine\ORM\Events;

        #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
        class UserChangedNotifier
        {
            // ...
        }

Alternatively, if you prefer to not use PHP attributes, you must
configure a service for the entity listener and :doc:`tag it </service_container/tags>`
with the ``doctrine.orm.entity_listener`` tag as follows:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\EventListener\UserChangedNotifier:
                tags:
                    -
                        # these are the options required to define the entity listener
                        name: 'doctrine.orm.entity_listener'
                        event: 'postUpdate'
                        entity: 'App\Entity\User'

                        # these are other options that you may define if needed

                        # set the 'lazy' option to TRUE to only instantiate listeners when they are used
                        # lazy: true

                        # set the 'entity_manager' option if the listener is not associated to the default manager
                        # entity_manager: 'custom'

                        # by default, Symfony looks for a method called after the event (e.g. postUpdate())
                        # if it doesn't exist, it tries to execute the '__invoke()' method, but you can
                        # configure a custom method name with the 'method' option
                        # method: 'checkUserChanges'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:doctrine="http://symfony.com/schema/dic/doctrine">
            <services>
                <!-- ... -->

                <service id="App\EventListener\UserChangedNotifier">
                    <!--
                        * These are the options required to define the entity listener:
                        *   * name
                        *   * event
                        *   * entity
                        *
                        * These are other options that you may define if needed:
                        *   * lazy: if TRUE, listeners are only instantiated when they are used
                        *   * entity_manager: define it if the listener is not associated to the default manager
                        *   * method: by default, Symfony looks for a method called after the event (e.g. postUpdate())
                        *           if it doesn't exist, it tries to execute the '__invoke()' method, but
                        *           you can configure a custom method name with the 'method' option
                    -->
                    <tag name="doctrine.orm.entity_listener"
                        event="postUpdate"
                        entity="App\Entity\User"
                        lazy="true"
                        entity_manager="custom"
                        method="checkUserChanges"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\User;
        use App\EventListener\UserChangedNotifier;

        return static function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(UserChangedNotifier::class)
                ->tag('doctrine.orm.entity_listener', [
                    // These are the options required to define the entity listener:
                    'event' => 'postUpdate',
                    'entity' => User::class,

                    // These are other options that you may define if needed:

                    // set the 'lazy' option to TRUE to only instantiate listeners when they are used
                    // 'lazy' => true,

                    // set the 'entity_manager' option if the listener is not associated to the default manager
                    // 'entity_manager' => 'custom',

                    // by default, Symfony looks for a method called after the event (e.g. postUpdate())
                    // if it doesn't exist, it tries to execute the '__invoke()' method, but you can
                    // configure a custom method name with the 'method' option
                    // 'method' => 'checkUserChanges',
                ])
            ;
        };

.. _`Doctrine`: https://www.doctrine-project.org/
.. _`lifecycle events`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/events.html#lifecycle-events
.. _`official docs about Doctrine events`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/events.html
.. _`DoctrineMongoDBBundle documentation`: https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html
.. _`AsDoctrineListener`: https://github.com/doctrine/DoctrineBundle/blob/2.10.x/Attribute/AsDoctrineListener.php
