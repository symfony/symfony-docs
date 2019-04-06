.. index::
    single: Doctrine; Lifecycle Callbacks

How to Work with Lifecycle Callbacks
====================================

Sometimes, you need to perform an action right before or after an entity
is inserted, updated, or deleted. These types of actions are known as "lifecycle"
callbacks, as they're callback methods that you need to execute during different
stages of the lifecycle of an entity (e.g. the entity is inserted, updated,
deleted, etc).

If you're using annotations for your metadata, start by enabling the lifecycle
callbacks. This is not necessary if you're using YAML or XML for your mapping.

.. code-block:: php-annotations

    // src/Entity/Product.php
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity()
     * @ORM\HasLifecycleCallbacks()
     */
    class Product
    {
        // ...
    }

Now, you can tell Doctrine to execute a method on any of the available lifecycle
events. For example, suppose you want to set a ``createdAt`` date column to
the current date, only when the entity is first persisted (i.e. inserted):

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Product.php
        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\PrePersist
         */
        public function setCreatedAtValue()
        {
            $this->createdAt = new \DateTime();
        }

    .. code-block:: yaml

        # config/doctrine/Product.orm.yml
        App\Entity\Product:
            type: entity
            # ...
            lifecycleCallbacks:
                prePersist: [setCreatedAtValue]

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

    The above example assumes that you've created and mapped a ``createdAt``
    property (not shown here).

Now, right before the entity is first persisted, Doctrine will automatically
call this method and the ``createdAt`` field will be set to the current date.

There are several other lifecycle events that you can hook into. For more
information on other lifecycle events and lifecycle callbacks in general, see
Doctrine's `Lifecycle Events documentation`_.

.. sidebar:: Lifecycle Callbacks and Event Listeners

    Notice that the ``setCreatedAtValue()`` method receives no arguments. This
    is always the case for lifecycle callbacks and is intentional: lifecycle
    callbacks should be simple methods that are concerned with internally
    transforming data in the entity (e.g. setting a created/updated field,
    generating a slug value).

    If you need to do some heavier lifting - like performing logging or sending
    an email - you should register an external class as an event listener
    or subscriber and give it access to whatever resources you need. For
    more information, see :doc:`/doctrine/event_listeners_subscribers`.

.. _`Lifecycle Events documentation`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#lifecycle-events
