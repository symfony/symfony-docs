.. _doctrine-event-config:

Registering Event Listeners and Subscribers
===========================================

Doctrine uses the lightweight ``Doctrine\Common\EventManager`` class to
trigger a number of different events which you can hook into. You can register
Event Listeners or Subscribers by tagging the respective services with
``doctrine.event_listener`` or ``doctrine.event_subscriber`` using the service
container.

To register services to act as event listeners or subscribers (listeners from
here) you have to tag them with the appropriate names. Depending on your
use-case you can hook a listener into every DBAL Connection and ORM Entity
Manager or just into one specific DBAL connection and all the EntityManagers
that use this connection.

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
                class: MyEventListener
                tags:
                    - { name: doctrine.event_listener, event: postLoad }
            my.listener2:
                class: MyEventListener2
                tags:
                    - { name: doctrine.event_listener, event: postLoad, connection: default }
            my.subscriber:
                class: MyEventSubscriber
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
                <service id="my.listener" class="MyEventListener">
                    <tag name="doctrine.event_listener" event="postLoad" />
                </service>
                <service id="my.listener2" class="MyEventListener2">
                    <tag name="doctrine.event_listener" event="postLoad" connection="default" />
                </service>
                <service id="my.subscriber" class="MyEventSubscriber">
                    <tag name="doctrine.event_subscriber" connection="default" />
                </service>
            </services>
        </container>
