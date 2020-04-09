.. index::
   single: Messenger

Messenger: Sync & Queued Message Handling
=========================================

Messenger provides a message bus with the ability to send messages and then
handle them immediately in your application or send them through transports
(e.g. queues) to be handled later. To learn more deeply about it, read the
:doc:`Messenger component docs </components/messenger>`.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install messenger:

.. code-block:: terminal

    $ composer require symfony/messenger

Creating a Message & Handler
----------------------------

Messenger centers around two different classes that you'll create: (1) a message
class that holds data and (2) a handler(s) class that will be called when that
message is dispatched. The handler class will read the message class and perform
some task.

There are no specific requirements for a message class, except that it can be
serialized::

    // src/Message/SmsNotification.php
    namespace App\Message;

    class SmsNotification
    {
        private $content;

        public function __construct(string $content)
        {
            $this->content = $content;
        }

        public function getContent(): string
        {
            return $this->content;
        }
    }

.. _messenger-handler:

A message handler is a PHP callable, the recommended way to create it is to
create a class that implements :class:`Symfony\\Component\\Messenger\\Handler\\MessageHandlerInterface`
and has an ``__invoke()`` method that's type-hinted with the message class (or a
message interface)::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\SmsNotification;
    use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

    class SmsNotificationHandler implements MessageHandlerInterface
    {
        public function __invoke(SmsNotification $message)
        {
            // ... do some work - like sending an SMS message!
        }
    }

Thanks to :ref:`autoconfiguration <services-autoconfigure>` and the ``SmsNotification``
type-hint, Symfony knows that this handler should be called when an ``SmsNotification``
message is dispatched. Most of the time, this is all you need to do. But you can
also :ref:`manually configure message handlers <messenger-handler-config>`. To
see all the configured handlers, run:

.. code-block:: terminal

    $ php bin/console debug:messenger

Dispatching the Message
-----------------------

You're ready! To dispatch the message (and call the handler), inject the
``message_bus`` service (via the ``MessageBusInterface``), like in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Message\SmsNotification;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends AbstractController
    {
        public function index(MessageBusInterface $bus)
        {
            // will cause the SmsNotificationHandler to be called
            $bus->dispatch(new SmsNotification('Look! I created a message!'));

            // or use the shortcut
            $this->dispatchMessage(new SmsNotification('Look! I created a message!'));

            // ...
        }
    }

Transports: Async/Queued Messages
---------------------------------

By default, messages are handled as soon as they are dispatched. If you want
to handle a message asynchronously, you can configure a transport. A transport
is capable of sending messages (e.g. to a queueing system) and then
:ref:`receiving them via a worker <messenger-worker>`. Messenger supports
:ref:`multiple transports <messenger-transports-config>`.

.. note::

    If you want to use a transport that's not supported, check out the
    `Enqueue's transport`_, which supports things like Kafka, Amazon SQS and
    Google Pub/Sub.

A transport is registered using a "DSN". Thanks to Messenger's Flex recipe, your
``.env`` file already has a few examples.

.. code-block:: bash

    # MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
    # MESSENGER_TRANSPORT_DSN=doctrine://default
    # MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages

Uncomment whichever transport you want (or set it in ``.env.local``). See
:ref:`messenger-transports-config` for more details.

Next, in ``config/packages/messenger.yaml``, let's define a transport called ``async``
that uses this configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async: "%env(MESSENGER_TRANSPORT_DSN)%"

                    # or expanded to configure more options
                    #async:
                    #    dsn: "%env(MESSENGER_TRANSPORT_DSN)%"
                    #    options: []

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async">%env(MESSENGER_TRANSPORT_DSN)%</framework:transport>

                    <!-- or expanded to configure more options -->
                    <framework:transport name="async"
                        dsn="%env(MESSENGER_TRANSPORT_DSN)%"
                    >
                        <option key="...">...</option>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async' => '%env(MESSENGER_TRANSPORT_DSN)%',

                    // or expanded to configure more options
                    'async' => [
                       'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                       'options' => []
                    ],
                ],
            ],
        ]);

.. _messenger-routing:

Routing Messages to a Transport
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that you have a transport configured, instead of handling a message immediately,
you can configure them to be sent to a transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async: "%env(MESSENGER_TRANSPORT_DSN)%"

                routing:
                    # async is whatever name you gave your transport above
                    'App\Message\SmsNotification':  async

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:routing message-class="App\Message\SmsNotification">
                        <!-- async is whatever name you gave your transport above -->
                        <framework:sender service="async"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    // async is whatever name you gave your transport above
                    'App\Message\SmsNotification' => 'async',
                ],
            ],
        ]);

Thanks to this, the ``App\Message\SmsNotification`` will be sent to the ``async``
transport and its handler(s) will *not* be called immediately. Any messages not
matched under ``routing`` will still be handled immediately.

You can also route classes by their parent class or interface. Or send messages
to multiple transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                routing:
                    # route all messages that extend this example base class or interface
                    'App\Message\AbstractAsyncMessage': async
                    'App\Message\AsyncMessageInterface': async

                    'My\Message\ToBeSentToTwoSenders': [async, audit]

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <!-- route all messages that extend this example base class or interface -->
                    <framework:routing message-class="App\Message\AbstractAsyncMessage">
                        <framework:sender service="async"/>
                    </framework:routing>
                    <framework:routing message-class="App\Message\AsyncMessageInterface">
                        <framework:sender service="async"/>
                    </framework:routing>
                    <framework:routing message-class="My\Message\ToBeSentToTwoSenders">
                        <framework:sender service="async"/>
                        <framework:sender service="audit"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'routing' => [
                    // route all messages that extend this example base class or interface
                    'App\Message\AbstractAsyncMessage' => 'async',
                    'App\Message\AsyncMessageInterface' => 'async',
                    'My\Message\ToBeSentToTwoSenders' => ['async', 'audit'],
                ],
            ],
        ]);

Doctrine Entities in Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to pass a Doctrine entity in a message, it's better to pass the entity's
primary key (or whatever relevant information the handler actually needs, like ``email``,
etc) instead of the object::

    // src/Message/NewUserWelcomeEmail.php
    namespace App\Message;

    class NewUserWelcomeEmail
    {
        private $userId;

        public function __construct(int $userId)
        {
            $this->userId = $userId;
        }

        public function getUserId(): int
        {
            return $this->userId;
        }
    }

Then, in your handler, you can query for a fresh object::

    // src/MessageHandler/NewUserWelcomeEmailHandler.php
    namespace App\MessageHandler;

    use App\Message\NewUserWelcomeEmail;
    use App\Repository\UserRepository;
    use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

    class NewUserWelcomeEmailHandler implements MessageHandlerInterface
    {
        private $userRepository;

        public function __construct(UserRepository $userRepository)
        {
            $this->userRepository = $userRepository;
        }

        public function __invoke(NewUserWelcomeEmail $welcomeEmail)
        {
            $user = $this->userRepository->find($welcomeEmail->getUserId());

            // ... send an email!
        }
    }

This guarantees the entity contains fresh data.

Handling Messages Synchronously
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If a message doesn't :ref:`match any routing rules <messenger-routing>`, it won't
be sent to any transport and will be handled immediately. In some cases (like
when `binding handlers to different transports`_),
it's easier or more flexible to handle this explicitly: by creating a ``sync``
transport and "sending" messages there to be handled immediately:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    # ... other transports

                    sync: 'sync://'

                routing:
                    App\Message\SmsNotification: sync

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <!-- ... other transports -->

                    <framework:transport name="sync" dsn="sync://"/>

                    <framework:routing message-class="App\Message\SmsNotification">
                        <framework:sender service="sync"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    // ... other transports

                    'sync' => 'sync://',
                ],
                'routing' => [
                    'App\Message\SmsNotification' => 'sync',
                ],
            ],
        ]);

Creating your Own Transport
~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also create your own transport if you need to send or receive messages
from something that is not supported. See :doc:`/messenger/custom-transport`.

.. _messenger-worker:

Consuming Messages (Running the Worker)
---------------------------------------

Once your messages have been routed, in most cases, you'll need to "consume" them.
You can do this with the ``messenger:consume`` command:

.. code-block:: terminal

    $ php bin/console messenger:consume async

    # use -vv to see details about what's happening
    $ php bin/console messenger:consume async -vv

The first argument is the receiver's name (or service id if you routed to a
custom service). By default, the command will run forever: looking for new messages
on your transport and handling them. This command is called your "worker".

Deploying to Production
~~~~~~~~~~~~~~~~~~~~~~~

On production, there are a few important things to think about:

**Use Supervisor to keep your worker(s) running**
    You'll want one or more "workers" running at all times. To do that, use a
    process control system like :ref:`Supervisor <messenger-supervisor>`.

**Don't Let Workers Run Forever**
    Some services (like Doctrine's EntityManager) will consume more memory
    over time. So, instead of allowing your worker to run forever, use a flag
    like ``messenger:consume --limit=10`` to tell your worker to only handle 10
    messages before exiting (then Supervisor will create a new process). There
    are also other options like ``--memory-limit=128M`` and ``--time-limit=3600``.

**Restart Workers on Deploy**
    Each time you deploy, you'll need to restart all your worker processes so
    that they see the newly deployed code. To do this, run ``messenger:stop-workers``
    on deploy. This will signal to each worker that it should finish the message
    it's currently handling and shut down gracefully. Then, Supervisor will create
    new worker processes. The command uses the :ref:`app <cache-configuration-with-frameworkbundle>`
    cache internally - so make sure this is configured to use an adapter you like.

Prioritized Transports
~~~~~~~~~~~~~~~~~~~~~~

Sometimes certain types of messages should have a higher priority and be handled
before others. To make this possible, you can create multiple transports and route
different messages to them. For example:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async_priority_high:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            # queue_name is specific to the doctrine transport
                            queue_name: high

                            # for AMQP send to a separate exchange then queue
                            #exchange:
                            #    name: high
                            #queues:
                            #    messages_high: ~
                            # or redis try "group"
                    async_priority_low:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            queue_name: low

                routing:
                    'App\Message\SmsNotification':  async_priority_low
                    'App\Message\NewUserWelcomeEmail':  async_priority_high

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async_priority_high" dsn="%env(MESSENGER_TRANSPORT_DSN)%">
                        <framework:options>
                            <framework:queue>
                                <framework:name>Queue</framework:name>
                            </framework:queue>
                        </framework:options>
                    </framework:transport>
                    <framework:transport name="async_priority_low" dsn="%env(MESSENGER_TRANSPORT_DSN)%">
                        <option key="queue_name">low</option>
                    </framework:transport>

                    <framework:routing message-class="App\Message\SmsNotification">
                        <framework:sender service="async_priority_low"/>
                    </framework:routing>
                    <framework:routing message-class="App\Message\NewUserWelcomeEmail">
                        <framework:sender service="async_priority_high"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async_priority_high' => [
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                        'options' => [
                            'queue_name' => 'high',
                        ],
                    ],
                    'async_priority_low' => [
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                        'options' => [
                            'queue_name' => 'low',
                        ],
                    ],
                ],
                'routing' => [
                    'App\Message\SmsNotification' => 'async_priority_low',
                    'App\Message\NewUserWelcomeEmail' => 'async_priority_high',
                ],
            ],
        ]);

You can then run individual workers for each transport or instruct one worker
to handle messages in a priority order:

.. code-block:: terminal

    $ php bin/console messenger:consume async_priority_high async_priority_low

The worker will always first look for messages waiting on ``async_priority_high``. If
there are none, *then* it will consume messages from ``async_priority_low``.

.. _messenger-supervisor:

Supervisor Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Supervisor is a great tool to guarantee that your worker process(es) is
*always* running (even if it closes due to failure, hitting a message limit
or thanks to ``messenger:stop-workers``). You can install it on Ubuntu, for
example, via:

.. code-block:: terminal

    $ sudo apt-get install supervisor

Supervisor configuration files typically live in a ``/etc/supervisor/conf.d``
directory. For example, you can create a new ``messenger-worker.conf`` file
there to make sure that 2 instances of ``messenger:consume`` are running at all
times:

.. code-block:: ini

    ;/etc/supervisor/conf.d/messenger-worker.conf
    [program:messenger-consume]
    command=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
    user=ubuntu
    numprocs=2
    startsecs=0
    autostart=true
    autorestart=true
    process_name=%(program_name)s_%(process_num)02d

Change the ``async`` argument to use the name of your transport (or transports)
and ``user`` to the Unix user on your server. Next, tell Supervisor to read your
config and start your workers:

.. code-block:: terminal

    $ sudo supervisorctl reread

    $ sudo supervisorctl update

    $ sudo supervisorctl start messenger-consume:*

See the `Supervisor docs`_ for more details.

.. _messenger-retries-failures:

Retries & Failures
------------------

If an exception is thrown while consuming a message from a transport it will
automatically be re-sent to the transport to be tried again. By default, a message
will be retried 3 times before being discarded or
:ref:`sent to the failure transport <messenger-failure-transport>`. Each retry
will also be delayed, in case the failure was due to a temporary issue. All of
this is configurable for each transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async_priority_high:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'

                        # default configuration
                        retry_strategy:
                            max_retries: 3
                            # milliseconds delay
                            delay: 1000
                            # causes the delay to be higher before each retry
                            # e.g. 1 second delay, 2 seconds, 4 seconds
                            multiplier: 2
                            max_delay: 0
                            # override all of this with a service that
                            # implements Symfony\Component\Messenger\Retry\RetryStrategyInterface
                            # service: null

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async_priority_high" dsn="%env(MESSENGER_TRANSPORT_DSN)%?queue_name=high_priority">
                        <framework:retry-strategy max-retries="3" delay="1000" multiplier="2" max-delay="0"/>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async_priority_high' => [
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',

                        // default configuration
                        'retry_strategy' => [
                            'max_retries' => 3,
                            // milliseconds delay
                            'delay' => 1000,
                            // causes the delay to be higher before each retry
                            // e.g. 1 second delay, 2 seconds, 4 seconds
                            'multiplier' => 2,
                            'max_delay' => 0,
                            // override all of this with a service that
                            // implements Symfony\Component\Messenger\Retry\RetryStrategyInterface
                            // 'service' => null,
                        ],
                    ],
                ],
            ],
        ]);

Avoiding Retrying
~~~~~~~~~~~~~~~~~

Sometimes handling a message might fail in a way that you *know* is permanent
and should not be retried. If you throw
:class:`Symfony\\Component\\Messenger\\Exception\\UnrecoverableMessageHandlingException`,
the message will not be retried.

.. _messenger-failure-transport:

Saving & Retrying Failed Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If a message fails it is retried multiple times (``max_retries``) and then will
be discarded. To avoid this happening, you can instead configure a ``failure_transport``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                # after retrying, messages will be sent to the "failed" transport
                failure_transport: failed

                transports:
                    # ... other transports

                    failed: 'doctrine://default?queue_name=failed'

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- after retrying, messages will be sent to the "failed" transport -->
                <framework:messenger failure-transport="failed">
                    <!-- ... other transports -->

                    <framework:transport name="failed" dsn="doctrine://default?queue_name=failed"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                // after retrying, messages will be sent to the "failed" transport
                'failure_transport' => 'failed',

                'transports' => [
                    // ... other transports

                    'failed' => [
                        'dsn' => 'doctrine://default?queue_name=failed',
                    ],
                ],
            ],
        ]);

In this example, if handling a message fails 3 times (default ``max_retries``),
it will then be sent to the ``failed`` transport. While you *can* use
``messenger:consume failed`` to consume this like a normal transport, you'll
usually want to manually view the messages in the failure transport and choose
to retry them:

.. code-block:: terminal

    # see all messages in the failure transport
    $ php bin/console messenger:failed:show

    # see details about a specific failure
    $ php bin/console messenger:failed:show 20 -vv

    # view and retry messages one-by-one
    $ php bin/console messenger:failed:retry -vv

    # retry specific messages
    $ php bin/console messenger:failed:retry 20 30 --force

    # remove a message without retrying it
    $ php bin/console messenger:failed:remove 20

    # remove messages without retrying them and show each message before removing it
    $ php bin/console messenger:failed:remove 20 30 --show-messages

.. versionadded:: 5.1

    The ``--show-messages`` option was introduced in Symfony 5.1.

If the message fails again, it will be re-sent back to the failure transport
due to the normal :ref:`retry rules <messenger-retries-failures>`. Once the max
retry has been hit, the message will be discarded permanently.

.. _messenger-transports-config:

Transport Configuration
-----------------------

Messenger supports a number of different transport types, each with their own
options.

AMQP Transport
~~~~~~~~~~~~~~

.. versionadded:: 5.1

    Starting from Symfony 5.1, the AMQP transport has moved to a separate package.
    Install it by running:

    .. code-block:: terminal

        $ composer require symfony/amqp-messenger

The ``amqp`` transport configuration looks like this:

.. code-block:: bash

    # .env
    MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages

To use Symfony's built-in AMQP transport, you need the AMQP PHP extension.

.. note::

    By default, the transport will automatically create any exchanges, queues and
    binding keys that are needed. That can be disabled, but some functionality
    may not work correctly (like delayed queues).

The transport has a number of other options, including ways to configure
the exchange, queues binding keys and more. See the documentation on
:class:`Symfony\\Component\\Messenger\\Bridge\\Amqp\\Transport\\Connection`.

You can also configure AMQP-specific settings on your message by adding
:class:`Symfony\\Component\\Messenger\\Bridge\\Amqp\\Transport\\AmqpStamp` to
your Envelope::

    use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
    // ...

    $attributes = [];
    $bus->dispatch(new SmsNotification(), [
        new AmqpStamp('custom-routing-key', AMQP_NOPARAM, $attributes)
    ]);

.. caution::

    The consumers do not show up in an admin panel as this transport does not rely on
    ``\AmqpQueue::consume()`` which is blocking. Having a blocking receiver makes
    the ``--time-limit/--memory-limit`` options of the ``messenger:consume`` command as well as
    the ``messenger:stop-workers`` command inefficient, as they all rely on the fact that
    the receiver returns immediately no matter if it finds a message or not. The consume
    worker is responsible for iterating until it receives a message to handle and/or until one
    of the stop conditions is reached. Thus, the worker's stop logic cannot be reached if it
    is stuck in a blocking call.

Doctrine Transport
~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    Starting from Symfony 5.1, the Doctrine transport has moved to a separate package.
    Install it by running:

    .. code-block:: terminal

        $ composer require symfony/doctrine-messenger

The Doctrine transport can be used to store messages in a database table.

.. code-block:: bash

    # .env
    MESSENGER_TRANSPORT_DSN=doctrine://default

The format is ``doctrine://<connection_name>``, in case you have multiple connections
and want to use one other than the "default". The transport will automatically create
a table named ``messenger_messages`` (this is configurable) when the transport is
first used. You can disable that with the ``auto_setup`` option and set the table
up manually by calling the ``messenger:setup-transports`` command.

.. tip::

    To avoid tools like Doctrine Migrations from trying to remove this table because
    it's not part of your normal schema, you can set the ``schema_filter`` option:

    .. code-block:: yaml

        # config/packages/doctrine.yaml
        doctrine:
            dbal:
                schema_filter: '~^(?!messenger_messages)~'

The transport has a number of options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async_priority_high: "%env(MESSENGER_TRANSPORT_DSN)%?queue_name=high_priority"
                    async_normal:
                        dsn: "%env(MESSENGER_TRANSPORT_DSN)%"
                        options:
                            queue_name: normal_priority

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async_priority_high" dsn="%env(MESSENGER_TRANSPORT_DSN)%?queue_name=high_priority"/>
                    <framework:transport name="async_priority_low" dsn="%env(MESSENGER_TRANSPORT_DSN)%">
                        <framework:options>
                            <framework:queue>
                                <framework:name>normal_priority</framework:name>
                            </framework:queue>
                        </framework:options>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async_priority_high' => 'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%?queue_name=high_priority',
                    'async_priority_low' => [
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                        'options' => [
                            'queue_name' => 'normal_priority'
                        ]
                    ],
                ],
            ],
        ]);

Options defined under ``options`` take precedence over ones defined in the DSN.

==================  ===================================  ======================
     Option         Description                          Default
==================  ===================================  ======================
table_name          Name of the table                    messenger_messages
queue_name          Name of the queue (a column in the   default
                    table, to use one table for
                    multiple transports)
redeliver_timeout   Timeout before retrying a message    3600
                    that's in the queue but in the
                    "handling" state (if a worker died
                    for some reason, this will occur,
                    eventually you should retry the
                    message) - in seconds.
auto_setup          Whether the table should be created
                    automatically during send / get.     true
==================  ===================================  ======================

Redis Transport
~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    Starting from Symfony 5.1, the Redis transport has moved to a separate package.
    Install it by running:

    .. code-block:: terminal

        $ composer require symfony/redis-messenger

The Redis transport uses `streams`_ to queue messages.

.. code-block:: bash

    # .env
    MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
    # Full DSN Example
    MESSENGER_TRANSPORT_DSN=redis://password@localhost:6379/messages/symfony/consumer?auto_setup=true&serializer=1&stream_max_entries=0&dbindex=0
    # Unix Socket Example
    MESSENGER_TRANSPORT_DSN=redis:///var/run/redis.sock

.. versionadded:: 5.1

    The Unix socket DSN was introduced in Symfony 5.1.

To use the Redis transport, you will need the Redis PHP extension (>=4.3) and
a running Redis server (^5.0).

A number of options can be configured via the DSN or via the ``options`` key
under the transport in ``messenger.yaml``:

==================  =====================================  =========================
     Option               Description                      Default
==================  =====================================  =========================
stream              The Redis stream name                  messages
group               The Redis consumer group name          symfony
consumer            Consumer name used in Redis            consumer
auto_setup          Create the Redis group automatically?  true
auth                The Redis password
serializer          How to serialize the final payload     ``Redis::SERIALIZER_PHP``
                    in Redis (the
                    ``Redis::OPT_SERIALIZER`` option)
stream_max_entries  The maximum number of entries which    ``0`` (which means "no trimming")
                    the stream will be trimmed to. Set
                    it to a large enough number to
                    avoid losing pending messages
tls                 Enable TLS support for the connection  false
==================  =====================================  =========================

In Memory Transport
~~~~~~~~~~~~~~~~~~~

The ``in-memory`` transport does not actually delivery messages. Instead, it
holds them in memory during the request, which can be useful for testing.
For example, if you have an ``async_priority_normal`` transport, you could
override it in the ``test`` environment to use this transport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/test/messenger.yaml
        framework:
            messenger:
                transports:
                    async_priority_normal: 'in-memory://'

    .. code-block:: xml

        <!-- config/packages/test/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async_priority_normal" dsn="in-memory://"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/test/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async_priority_normal' => [
                        'dsn' => 'in-memory://',
                    ],
                ],
            ],
        ]);

Then, while testing, messages will *not* be delivered to the real transport.
Even better, in a test, you can check that exactly one message was sent
during a request::

    // tests/DefaultControllerTest.php
    namespace App\Tests;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Messenger\Transport\InMemoryTransport;

    class DefaultControllerTest extends WebTestCase
    {
        public function testSomething()
        {
            $client = static::createClient();
            // ...

            $this->assertSame(200, $client->getResponse()->getStatusCode());

            /* @var InMemoryTransport $transport */
            $transport = self::$container->get('messenger.transport.async_priority_normal');
            $this->assertCount(1, $transport->getSent());
        }
    }

.. note::

        All ``in-memory`` transports will be reset automatically after each test **in**
        test classes extending
        :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`
        or :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase`.

Serializing Messages
~~~~~~~~~~~~~~~~~~~~

When messages are sent to (and received from) a transport, they're serialized
using PHP's native ``serialize()`` & ``unserialize()`` functions. You can change
this globally (or for each transport) to a service that implements
:class:`Symfony\\Component\\Messenger\\Transport\\Serialization\\SerializerInterface`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                serializer:
                    default_serializer: messenger.transport.symfony_serializer
                    symfony_serializer:
                        format: json
                        context: { }

                transports:
                    async_priority_normal:
                        dsn: # ...
                        serializer: messenger.transport.symfony_serializer

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:serializer default-serializer="messenger.transport.symfony_serializer">
                        <framework:symfony-serializer format="json">
                            <framework:context/>
                        </framework:symfony-serializer>
                    </framework:serializer>

                    <framework:transport name="async_priority_normal" dsn="..." serializer="messenger.transport.symfony_serializer"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'serializer' => [
                    'default_serializer' => 'messenger.transport.symfony_serializer',
                    'symfony_serializer' => [
                        'format' => 'json',
                        'context' => [],
                    ],
                ],
                'transports' => [
                    'async_priority_normal' => [
                        'dsn' => // ...
                        'serializer' => 'messenger.transport.symfony_serializer',
                    ],
                ],
            ],
        ]);

The ``messenger.transport.symfony_serializer`` is a built-in service that uses
the :doc:`Serializer component </serializer>` and can be configured in a few ways.
If you *do* choose to use the Symfony serializer, you can control the context
on a case-by-case basis via the :class:`Symfony\\Component\\Messenger\\Stamp\\SerializerStamp`
(see `Envelopes & Stamps`_).

.. tip::

    When sending/receiving messages to/from another application, you may need
    more control over the serialization process. Using a custom serializer
    provides that control. See `SymfonyCasts' message serializer tutorial`_ for
    details.

Customizing Handlers
--------------------

.. _messenger-handler-config:

Manually Configuring Handlers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony will normally :ref:`find and register your handler automatically <messenger-handler>`.
But, you can also configure a handler manually - and pass it some extra config -
by tagging the handler service with ``messenger.message_handler``

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\MessageHandler\SmsNotificationHandler:
                tags: [messenger.message_handler]

                # or configure with options
                tags:
                    -
                        name: messenger.message_handler
                        # only needed if can't be guessed by type-hint
                        handles: App\Message\SmsNotification

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\MessageHandler\SmsNotificationHandler">
                     <!-- handles is only needed if it can't be guessed by type-hint -->
                     <tag name="messenger.message_handler"
                          handles="App\Message\SmsNotification"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Message\SmsNotification;
        use App\MessageHandler\SmsNotificationHandler;

        $container->register(SmsNotificationHandler::class)
            ->addTag('messenger.message_handler', [
                // only needed if can't be guessed by type-hint
                'handles' => SmsNotification::class,
            ]);


Possible options to configure with tags are:

* ``bus``
* ``from_transport``
* ``handles``
* ``method``
* ``priority``

Handler Subscriber & Options
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A handler class can handle multiple messages or configure itself by implementing
:class:`Symfony\\Component\\Messenger\\Handler\\MessageSubscriberInterface`::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\OtherSmsNotification;
    use App\Message\SmsNotification;
    use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

    class SmsNotificationHandler implements MessageSubscriberInterface
    {
        public function __invoke(SmsNotification $message)
        {
            // ...
        }

        public function handleOtherSmsNotification(OtherSmsNotification $message)
        {
            // ...
        }

        public static function getHandledMessages(): iterable
        {
            // handle this message on __invoke
            yield SmsNotification::class;

            // also handle this message on handleOtherSmsNotification
            yield OtherSmsNotification::class => [
                'method' => 'handleOtherSmsNotification',
                //'priority' => 0,
                //'bus' => 'messenger.bus.default',
            ];
        }
    }

Binding Handlers to Different Transports
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Each message can have multiple handlers, and when a message is consumed
*all* of its handlers are called. But you can also configure a handler to only
be called when it's received from a *specific* transport. This allows you to
have a single message where each handler is called by a different "worker"
that's consuming a different transport.

Suppose you have an ``UploadedImage`` message with two handlers:

* ``ThumbnailUploadedImageHandler``: you want this to be handled by
  a transport called ``image_transport``

* ``NotifyAboutNewUploadedImageHandler``: you want this to be handled
  by a transport called ``async_priority_normal``

To do this, add the ``from_transport`` option to each handler. For example::

    // src/MessageHandler/ThumbnailUploadedImageHandler.php
    namespace App\MessageHandler;

    use App\Message\UploadedImage;
    use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

    class ThumbnailUploadedImageHandler implements MessageSubscriberInterface
    {
        public function __invoke(UploadedImage $uploadedImage)
        {
            // do some thumbnailing
        }

        public static function getHandledMessages(): iterable
        {
            yield UploadedImage::class => [
                'from_transport' => 'image_transport',
            ];
        }
    }

And similarly::

    // src/MessageHandler/NotifyAboutNewUploadedImageHandler.php
    // ...

    class NotifyAboutNewUploadedImageHandler implements MessageSubscriberInterface
    {
        // ...

        public static function getHandledMessages(): iterable
        {
            yield UploadedImage::class => [
                'from_transport' => 'async_priority_normal',
            ];
        }
    }

Then, make sure to "route" your message to *both* transports:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async_priority_normal: # ...
                    image_transport: # ...

                routing:
                    # ...
                    'App\Message\UploadedImage': [image_transport, async_priority_normal]

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:transport name="async_priority_normal" dsn="..."/>
                    <framework:transport name="image_transport" dsn="..."/>

                    <framework:routing message-class="App\Message\UploadedImage">
                        <framework:sender service="image_transport"/>
                        <framework:sender service="async_priority_normal"/>
                    </framework:routing>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'transports' => [
                    'async_priority_normal' => '...',
                    'image_transport' => '...',
                ],
                'routing' => [
                    'App\Message\UploadedImage' => ['image_transport', 'async_priority_normal']
                ]
            ],
        ]);

That's it! You can now consume each transport:

.. code-block:: terminal

    # will only call ThumbnailUploadedImageHandler when handling the message
    $ php bin/console messenger:consume image_transport -vv

    $ php bin/console messenger:consume async_priority_normal -vv

.. caution::

    If a handler does *not* have ``from_transport`` config, it will be executed
    on *every* transport that the message is received from.

Extending Messenger
-------------------

Envelopes & Stamps
~~~~~~~~~~~~~~~~~~

A message can be any PHP object. Sometimes, you may need to configure something
extra about the message - like the way it should be handled inside AMQP or adding
a delay before the message should be handled. You can do that by adding a "stamp"
to your message::

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Messenger\Stamp\DelayStamp;

    public function index(MessageBusInterface $bus)
    {
        $bus->dispatch(new SmsNotification('...'), [
            // wait 5 seconds before processing
            new DelayStamp(5000)
        ]);

        // or explicitly create an Envelope
        $bus->dispatch(new Envelope(new SmsNotification('...'), [
            new DelayStamp(5000)
        ]));

        // ...
    }

Internally, each message is wrapped in an ``Envelope``, which holds the message
and stamps. You can create this manually or allow the message bus to do it. There
are a variety of different stamps for different purposes and they're used internally
to track information about a message - like the message bus that's handling it
or if it's being retried after failure.

Middleware
~~~~~~~~~~

What happens when you dispatch a message to a message bus depends on its
collection of middleware and their order. By default, the middleware configured
for each bus looks like this:

#. ``add_bus_name_stamp_middleware`` - adds a stamp to record which bus this
   message was dispatched into;

#. ``dispatch_after_current_bus``- see :doc:`/messenger/message-recorder`;

#. ``failed_message_processing_middleware`` - processes messages that are being
   retried via the :ref:`failure transport <messenger-failure-transport>` to make
   them properly function as if they were being received from their original transport;

#. Your own collection of middleware_;

#. ``send_message`` - if routing is configured for the transport, this sends
   messages to that transport and stops the middleware chain;

#. ``handle_message`` - calls the message handler(s) for the given message.

.. note::

    These middleware names are actually shortcut names. The real service ids
    are prefixed with ``messenger.middleware.`` (e.g. ``messenger.middleware.handle_message``).

The middleware are executed when the message is dispatched but *also* again when
a message is received via the worker (for messages that were sent to a transport
to be handled asynchronously). Keep this in mind if you create your own middleware.

You can add your own middleware to this list, or completely disable the default
middleware and *only* include your own:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    messenger.bus.default:
                        middleware:
                            # service ids that implement Symfony\Component\Messenger\Middleware\MiddlewareInterface
                            - 'App\Middleware\MyMiddleware'
                            - 'App\Middleware\AnotherMiddleware'

                        #default_middleware: false

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:middleware id="App\Middleware\MyMiddleware"/>
                    <framework:middleware id="App\Middleware\AnotherMiddleware"/>
                    <framework:bus name="messenger.bus.default" default-middleware="false"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'messenger.bus.default' => [
                        'middleware' => [
                            'App\Middleware\MyMiddleware',
                            'App\Middleware\AnotherMiddleware',
                        ],
                        'default_middleware' => false,
                    ],
                ],
            ],
        ]);


.. note::

    If a middleware service is abstract, a different instance of the service will
    be created per bus.

Middleware for Doctrine
~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 1.11

    The following Doctrine middleware were introduced in DoctrineBundle 1.11.

If you use Doctrine in your app, a number of optional middleware exist that you
may want to use:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    command_bus:
                        middleware:
                            # each time a message is handled, the Doctrine connection
                            # is "pinged" and reconnected if it's closed. Useful
                            # if your workers run for a long time and the database
                            # connection is sometimes lost
                            - doctrine_ping_connection

                            # After handling, the Doctrine connection is closed,
                            # which can free up database connections in a worker,
                            # instead of keeping them open forever
                            - doctrine_close_connection

                            # wraps all handlers in a single Doctrine transaction
                            # handlers do not need to call flush() and an error
                            # in any handler will cause a rollback
                            - doctrine_transaction

                            # or pass a different entity manager to any
                            #- doctrine_transaction: ['custom']

    .. code-block:: xml

        <!-- config/packages/messenger.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:messenger>
                    <framework:bus name="command_bus">
                        <framework:middleware id="doctrine_transaction"/>
                        <framework:middleware id="doctrine_ping_connection"/>
                        <framework:middleware id="doctrine_close_connection"/>

                        <!-- or pass a different entity manager to any -->
                        <!--
                        <framework:middleware id="doctrine_transaction">
                            <framework:argument>custom</framework:argument>
                        </framework:middleware>
                        -->
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'buses' => [
                    'command_bus' => [
                        'middleware' => [
                            'doctrine_transaction',
                            'doctrine_ping_connection',
                            'doctrine_close_connection',
                            // Using another entity manager
                            ['id' => 'doctrine_transaction', 'arguments' => ['custom']],
                        ],
                    ],
                ],
            ],
        ]);

Messenger Events
~~~~~~~~~~~~~~~~

In addition to middleware, Messenger also dispatches several events. You can
:doc:`create an event listener </event_dispatcher>` to hook into various parts
of the process. For each, the event class is the event name:

* :class:`Symfony\\Component\\Messenger\\Event\\WorkerStartedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageReceivedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\SendMessageToTransportsEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageFailedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageHandledEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerRunningEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerStoppedEvent`

Multiple Buses, Command & Event Buses
-------------------------------------

Messenger gives you a single message bus service by default. But, you can configure
as many as you want, creating "command", "query" or "event" buses and controlling
their middleware. See :doc:`/messenger/multiple_buses`.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /messenger/*

.. _`Enqueue's transport`: https://github.com/sroze/messenger-enqueue-transport
.. _`streams`: https://redis.io/topics/streams-intro
.. _`Supervisor docs`: http://supervisord.org/
.. _`SymfonyCasts' message serializer tutorial`: https://symfonycasts.com/screencast/messenger/transport-serializer
