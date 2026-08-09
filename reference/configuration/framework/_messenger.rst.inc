messenger
~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable or not Messenger.

.. seealso::

    For more details, see the :doc:`Messenger component </messenger>`
    documentation.

default_bus
...........

**type**: ``string`` **default**: ``null``

The name of the default bus. When you define more than one bus, you must set
this option.

failure_transport
.................

**type**: ``string`` **default**: ``null``

The transport name to send failed messages to (after all retries have failed).

routing
.......

**type**: ``array``

Defines the routing of messages to transports. The keys are the fully qualified
class names (or a parent class/interface) and the values are the transport names
(or a list of transport names) the messages should be routed to:

.. code-block:: yaml

    framework:
        messenger:
            routing:
                'App\Message\SmsNotification': async
                'App\Message\OtherMessage': [async, audit]

senders
"""""""

**type**: ``array``

A list of transport names to route the message to.

serializer
..........

default_serializer
""""""""""""""""""

**type**: ``string`` **default**: ``messenger.transport.native_php_serializer``

Service id to use as the default serializer for the transports.

symfony_serializer
""""""""""""""""""

format
^^^^^^

**type**: ``string`` **default**: ``json``

Serialization format for the ``messenger.transport.symfony_serializer``
service (which is not the serializer used by default).

context
^^^^^^^

**type**: ``array`` **default**: ``[]``

Context array for the ``messenger.transport.symfony_serializer`` service
(which is not the serializer used by default).

transports
..........

**type**: ``array``

Defines the transports used to send messages. Each transport has a name (used
in routing), a DSN and optional configuration:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            transports:
                async:
                    dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                    retry_strategy:
                        max_retries: 3
                        delay: 1000
                        multiplier: 2

dsn
"""

**type**: ``string``

The DSN of the transport (e.g. ``doctrine://default``, ``amqp://guest:guest@localhost/%2f/messages``).

serializer
""""""""""

**type**: ``string`` **default**: ``null``

Service id of a custom serializer to use for this transport.

options
"""""""

**type**: ``array`` **default**: ``[]``

Transport-specific options.

failure_transport
"""""""""""""""""

**type**: ``string`` **default**: ``null``

Transport name to send failed messages to (after all retries have failed).

rate_limiter
""""""""""""

**type**: ``string`` **default**: ``null``

Rate limiter name to use when processing messages.

retry_strategy
""""""""""""""

service
^^^^^^^

**type**: ``string`` **default**: ``null``

Service id to override the retry strategy entirely. When set, the other
``retry_strategy`` options are not allowed.

max_retries
^^^^^^^^^^^

**type**: ``integer`` **default**: ``3``

Maximum number of retries before the message is sent to the failure transport.
A value of ``0`` means no retries.

delay
^^^^^

**type**: ``integer`` **default**: ``1000``

Time in milliseconds to delay (or the initial value when ``multiplier`` is used).

multiplier
^^^^^^^^^^

**type**: ``float`` **default**: ``2``

If greater than 1, the delay will grow exponentially for each retry:
``delay = delay * (multiplier ^ retries)``.

max_delay
^^^^^^^^^

**type**: ``integer`` **default**: ``0``

Maximum time in milliseconds that a retry should ever be delayed. A value of
``0`` means infinite.

jitter
^^^^^^

**type**: ``float`` **default**: ``0.1``

Randomness to apply to the delay (between ``0`` and ``1``).

stop_worker_on_signals
......................

**type**: ``array`` **default**: ``[]``

A list of signals that should stop the worker. Defaults to ``SIGTERM`` and
``SIGINT`` when empty:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            stop_worker_on_signals: ['SIGTERM', 'SIGINT']

buses
.....

**type**: ``array`` **default**: ``{ messenger.bus.default: { default_middleware: { enabled: true, allow_no_handlers: false, allow_no_senders: true }, middleware: [] } }``

Defines the message buses used in the application:

.. code-block:: yaml

    # config/packages/messenger.yaml
    framework:
        messenger:
            default_bus: command.bus
            buses:
                command.bus: ~
                event.bus:
                    default_middleware:
                        allow_no_handlers: true

default_middleware
""""""""""""""""""

**type**: ``boolean`` | ``array`` **default**: ``true``

Whether to add the default middleware to the bus. When set to ``true`` or an
array, the following options are available:

enabled
^^^^^^^

**type**: ``boolean`` **default**: ``true``

Whether default middleware is enabled.

allow_no_handlers
^^^^^^^^^^^^^^^^^

**type**: ``boolean`` **default**: ``false``

Whether to allow dispatching a message without any handler.

allow_no_senders
^^^^^^^^^^^^^^^^

**type**: ``boolean`` **default**: ``true``

Whether to allow dispatching a message without any sender.

middleware
""""""""""

**type**: ``array`` **default**: ``[]``

A list of middleware service ids to add to the bus, ordered by priority:

.. code-block:: yaml

    framework:
        messenger:
            buses:
                command.bus:
                    middleware:
                        - 'App\Middleware\MyMiddleware'
                        - doctrine_transaction
