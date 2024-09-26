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
one or more tasks.

There are no specific requirements for a message class, except that it can be
serialized::

    // src/Message/SmsNotification.php
    namespace App\Message;

    class SmsNotification
    {
        public function __construct(
            private string $content,
        ) {
        }

        public function getContent(): string
        {
            return $this->content;
        }
    }

.. _messenger-handler:

A message handler is a PHP callable, the recommended way to create it is to
create a class that has the :class:`Symfony\\Component\\Messenger\\Attribute\\AsMessageHandler`
attribute and has an ``__invoke()`` method that's type-hinted with the
message class (or a message interface)::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\SmsNotification;
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;

    #[AsMessageHandler]
    class SmsNotificationHandler
    {
        public function __invoke(SmsNotification $message)
        {
            // ... do some work - like sending an SMS message!
        }
    }

.. tip::

    You can also use the ``#[AsMessageHandler]`` attribute on individual class
    methods. You may use the attribute on as many methods in a single class as you
    like, allowing you to group the handling of multiple related types of messages.

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
``messenger.default_bus`` service (via the ``MessageBusInterface``), like in a controller::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    use App\Message\SmsNotification;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Messenger\MessageBusInterface;

    class DefaultController extends AbstractController
    {
        public function index(MessageBusInterface $bus): Response
        {
            // will cause the SmsNotificationHandler to be called
            $bus->dispatch(new SmsNotification('Look! I created a message!'));

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
    `Enqueue's transport`_, which backs services like Kafka and Google
    Pub/Sub.

A transport is registered using a "DSN". Thanks to Messenger's Flex recipe, your
``.env`` file already has a few examples.

.. code-block:: env

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->messenger()
                ->transport('async')
                    ->dsn(env('MESSENGER_TRANSPORT_DSN'))
            ;

            $framework->messenger()
                ->transport('async')
                    ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                    ->options([])
            ;
        };

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
                    'App\Message\SmsNotification': async

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->messenger()
                // async is whatever name you gave your transport above
                ->routing('App\Message\SmsNotification')->senders(['async'])
            ;
        };

Thanks to this, the ``App\Message\SmsNotification`` will be sent to the ``async``
transport and its handler(s) will *not* be called immediately. Any messages not
matched under ``routing`` will still be handled immediately, i.e. synchronously.

.. note::

    You may use a partial PHP namespace like ``'App\Message\*'`` to match all
    the messages within the matching namespace. The only requirement is that the
    ``'*'`` wildcard has to be placed at the end of the namespace.

    You may use ``'*'`` as the message class. This will act as a default routing
    rule for any message not matched under ``routing``. This is useful to ensure
    no message is handled synchronously by default.

    The only drawback is that ``'*'`` will also apply to the emails sent with the
    Symfony Mailer (which uses ``SendEmailMessage`` when Messenger is available).
    This could cause issues if your emails are not serializable (e.g. if they include
    file attachments as PHP resources/streams).

You can also route classes by their parent class or interface. Or send messages
to multiple transports:

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();
            // route all messages that extend this example base class or interface
            $messenger->routing('App\Message\AbstractAsyncMessage')->senders(['async']);
            $messenger->routing('App\Message\AsyncMessageInterface')->senders(['async']);
            $messenger->routing('My\Message\ToBeSentToTwoSenders')->senders(['async', 'audit']);
        };

.. note::

    If you configure routing for both a child and parent class, both rules
    are used. E.g. if you have an ``SmsNotification`` object that extends
    from ``Notification``, both the routing for ``Notification`` and
    ``SmsNotification`` will be used.

.. tip::

    You can define and override the transport that a message is using at
    runtime by using the
    :class:`Symfony\\Component\\Messenger\\Stamp\\TransportNamesStamp` on
    the envelope of the message. This stamp takes an array of transport
    name as its only argument. For more information about stamps, see
    `Envelopes & Stamps`_.

Doctrine Entities in Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to pass a Doctrine entity in a message, it's better to pass the entity's
primary key (or whatever relevant information the handler actually needs, like ``email``,
etc.) instead of the object (otherwise you might see errors related to the Entity Manager)::

    // src/Message/NewUserWelcomeEmail.php
    namespace App\Message;

    class NewUserWelcomeEmail
    {
        public function __construct(
            private int $userId,
        ) {
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
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;

    #[AsMessageHandler]
    class NewUserWelcomeEmailHandler
    {
        public function __construct(
            private UserRepository $userRepository,
        ) {
        }

        public function __invoke(NewUserWelcomeEmail $welcomeEmail): void
        {
            $user = $this->userRepository->find($welcomeEmail->getUserId());

            // ... send an email!
        }
    }

This guarantees the entity contains fresh data.

.. _messenger-handling-messages-synchronously:

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            // ... other transports

            $messenger->transport('sync')->dsn('sync://');
            $messenger->routing('App\Message\SmsNotification')->senders(['sync']);
        };

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

If you want to consume messages from all available receivers, you can use the
command with the ``--all`` option:

.. code-block:: terminal

    $ php bin/console messenger:consume --all

.. versionadded:: 7.1

    The ``--all`` option was introduced in Symfony 7.1.

.. tip::

    In a development environment and if you're using the Symfony CLI tool,
    you can configure workers to be automatically run along with the webserver.
    You can find more information in the
    :ref:`Symfony CLI Workers <symfony-server_configuring-workers>` documentation.

.. tip::

    To properly stop a worker, throw an instance of
    :class:`Symfony\\Component\\Messenger\\Exception\\StopWorkerException`.

Deploying to Production
~~~~~~~~~~~~~~~~~~~~~~~

On production, there are a few important things to think about:

**Use a Process Manager like Supervisor or systemd to keep your worker(s) running**
    You'll want one or more "workers" running at all times. To do that, use a
    process control system like :ref:`Supervisor <messenger-supervisor>`
    or :ref:`systemd <messenger-systemd>`.

**Don't Let Workers Run Forever**
    Some services (like Doctrine's ``EntityManager``) will consume more memory
    over time. So, instead of allowing your worker to run forever, use a flag
    like ``messenger:consume --limit=10`` to tell your worker to only handle 10
    messages before exiting (then the process manager will create a new process). There
    are also other options like ``--memory-limit=128M`` and ``--time-limit=3600``.

**Stopping Workers That Encounter Errors**
    If a worker dependency like your database server is down, or timeout is reached,
    you can try to add :ref:`reconnect logic <middleware-doctrine>`, or just quit
    the worker if it receives too many errors with the ``--failure-limit`` option of
    the ``messenger:consume`` command.

**Restart Workers on Deploy**
    Each time you deploy, you'll need to restart all your worker processes so
    that they see the newly deployed code. To do this, run ``messenger:stop-workers``
    on deployment. This will signal to each worker that it should finish the message
    it's currently handling and should shut down gracefully. Then, the process manager
    will create new worker processes. The command uses the :ref:`app <cache-configuration-with-frameworkbundle>`
    cache internally - so make sure this is configured to use an adapter you like.

**Use the Same Cache Between Deploys**
    If your deploy strategy involves the creation of new target directories, you
    should set a value for the :ref:`cache.prefix_seed <reference-cache-prefix-seed>`
    configuration option in order to use the same cache namespace between deployments.
    Otherwise, the ``cache.app`` pool will use the value of the ``kernel.project_dir``
    parameter as base for the namespace, which will lead to different namespaces
    each time a new deployment is made.

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
                            # for redis try "group"
                    async_priority_low:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        options:
                            queue_name: low

                routing:
                    'App\Message\SmsNotification': async_priority_low
                    'App\Message\NewUserWelcomeEmail': async_priority_high

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('async_priority_high')
                ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                ->options(['queue_name' => 'high']);

            $messenger->transport('async_priority_low')
                ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                ->options(['queue_name' => 'low']);

            $messenger->routing('App\Message\SmsNotification')->senders(['async_priority_low']);
            $messenger->routing('App\Message\NewUserWelcomeEmail')->senders(['async_priority_high']);
        };

You can then run individual workers for each transport or instruct one worker
to handle messages in a priority order:

.. code-block:: terminal

    $ php bin/console messenger:consume async_priority_high async_priority_low

The worker will always first look for messages waiting on ``async_priority_high``. If
there are none, *then* it will consume messages from ``async_priority_low``.

.. _messenger-limit-queues:

Limit Consuming to Specific Queues
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some transports (notably AMQP) have the concept of exchanges and queues. A Symfony
transport is always bound to an exchange. By default, the worker consumes from all
queues attached to the exchange of the specified transport. However, there are use
cases to want a worker to only consume from specific queues.

You can limit the worker to only process messages from specific queue(s):

.. code-block:: terminal

    $ php bin/console messenger:consume my_transport --queues=fasttrack

    # you can pass the --queues option more than once to process multiple queues
    $ php bin/console messenger:consume my_transport --queues=fasttrack1 --queues=fasttrack2

.. note::

    To allow using the ``queues`` option, the receiver must implement the
    :class:`Symfony\\Component\\Messenger\\Transport\\Receiver\\QueueReceiverInterface`.

.. _messenger-message-count:

Checking the Number of Queued Messages Per Transport
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Run the ``messenger:stats`` command to know how many messages are in the "queues"
of some or all transports:

.. code-block:: terminal

    # displays the number of queued messages in all transports
    $ php bin/console messenger:stats

    # shows stats only for some transports
    $ php bin/console messenger:stats my_transport_name other_transport_name

.. note::

    In order for this command to work, the configured transport's receiver must implement
    :class:`Symfony\\Component\\Messenger\\Transport\\Receiver\\MessageCountAwareInterface`.

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
    startretries=10
    process_name=%(program_name)s_%(process_num)02d

Change the ``async`` argument to use the name of your transport (or transports)
and ``user`` to the Unix user on your server.

.. caution::

    During a deployment, something might be unavailable (e.g. the
    database) causing the consumer to fail to start. In this situation,
    Supervisor will try ``startretries`` number of times to restart the
    command. Make sure to change this setting to avoid getting the command
    in a FATAL state, which will never restart again.

    Each restart, Supervisor increases the delay by 1 second. For instance, if
    the value is ``10``, it will wait 1 sec, 2 sec, 3 sec, etc. This gives the
    service a total of 55 seconds to become available again. Increase the
    ``startretries`` setting to cover the maximum expected downtime.

If you use the Redis Transport, note that each worker needs a unique consumer
name to avoid the same message being handled by multiple workers. One way to
achieve this is to set an environment variable in the Supervisor configuration
file, which you can then refer to in ``messenger.yaml``
(see the :ref:`Redis section <messenger-redis-transport>` below):

.. code-block:: ini

    environment=MESSENGER_CONSUMER_NAME=%(program_name)s_%(process_num)02d

Next, tell Supervisor to read your config and start your workers:

.. code-block:: terminal

    $ sudo supervisorctl reread

    $ sudo supervisorctl update

    $ sudo supervisorctl start messenger-consume:*

    # If you deploy an update of your code, don't forget to restart your workers
    # to run the new code
    $ sudo supervisorctl restart messenger-consume:*

See the `Supervisor docs`_ for more details.

Graceful Shutdown
.................

If you install the `PCNTL`_ PHP extension in your project, workers will handle
the ``SIGTERM`` or ``SIGINT`` POSIX signals to finish processing their current
message before terminating.

However, you might prefer to use different POSIX signals for graceful shutdown.
You can override default ones by setting the ``framework.messenger.stop_worker_on_signals``
configuration option.

In some cases the ``SIGTERM`` signal is sent by Supervisor itself (e.g. stopping
a Docker container having Supervisor as its entrypoint). In these cases you
need to add a ``stopwaitsecs`` key to the program configuration (with a value
of the desired grace period in seconds) in order to perform a graceful shutdown:

.. code-block:: ini

    [program:x]
    stopwaitsecs=20

.. _messenger-systemd:

Systemd Configuration
~~~~~~~~~~~~~~~~~~~~~

While Supervisor is a great tool, it has the disadvantage that you need system
access to run it. Systemd has become the standard on most Linux distributions,
and has a good alternative called *user services*.

Systemd user service configuration files typically live in a ``~/.config/systemd/user``
directory. For example, you can create a new ``messenger-worker.service`` file. Or a
``messenger-worker@.service`` file if you want more instances running at the same time:

.. code-block:: ini

    [Unit]
    Description=Symfony messenger-consume %i

    [Service]
    ExecStart=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
    Restart=always
    RestartSec=30

    [Install]
    WantedBy=default.target

Now, tell systemd to enable and start one worker:

.. code-block:: terminal

    $ systemctl --user enable messenger-worker@1.service
    $ systemctl --user start messenger-worker@1.service

    # to enable and start 20 workers
    $ systemctl --user enable messenger-worker@{1..20}.service
    $ systemctl --user start messenger-worker@{1..20}.service

If you change your service config file, you need to reload the daemon:

.. code-block:: terminal

    $ systemctl --user daemon-reload

To restart all your consumers:

.. code-block:: terminal

    $ systemctl --user restart messenger-consume@*.service

The systemd user instance is only started after the first login of the
particular user. Consumer often need to start on system boot instead.
Enable lingering on the user to activate that behavior:

.. code-block:: terminal

    $ loginctl enable-linger <your-username>

Logs are managed by journald and can be worked with using the journalctl
command:

.. code-block:: terminal

    # follow logs of consumer nr 11
    $ journalctl -f --user-unit messenger-consume@11.service

    # follow logs of all consumers
    $ journalctl -f --user-unit messenger-consume@*

    # follow all logs from your user services
    $ journalctl -f _UID=$UID

See the `systemd docs`_ for more details.

.. note::

    You either need elevated privileges for the ``journalctl`` command, or add
    your user to the systemd-journal group:

    .. code-block:: terminal

        $ sudo usermod -a -G systemd-journal <your-username>

Stateless Worker
~~~~~~~~~~~~~~~~

PHP is designed to be stateless, there are no shared resources across different
requests. In HTTP context PHP cleans everything after sending the response, so
you can decide to not take care of services that may leak memory.

On the other hand, it's common for workers to process messages sequentially in
long-running CLI processes which don't finish after processing a single message.
Beware about service states to prevent information and/or memory leakage as
Symfony will inject the same instance of a service in all messages, preserving
the internal state of the services.

However, certain Symfony services, such as the Monolog
:ref:`fingers crossed handler <logging-handler-fingers_crossed>`, leak by design.
Symfony provides a **service reset** feature to solve this problem. When resetting
the container automatically between two messages, Symfony looks for any services
implementing :class:`Symfony\\Contracts\\Service\\ResetInterface` (including your
own services) and calls their ``reset()`` method so they can clean their internal state.

If a service is not stateless and you want to reset its properties after each message, then
the service must implement :class:`Symfony\\Contracts\\Service\\ResetInterface` where you can reset the
properties in the ``reset()`` method.

If you don't want to reset the container, add the ``--no-reset`` option when
running the ``messenger:consume`` command.

.. _messenger-retries-failures:

Rate Limited Transport
~~~~~~~~~~~~~~~~~~~~~~

Sometimes you might need to rate limit your message worker. You can configure a
rate limiter on a transport (requires the :doc:`RateLimiter component </rate-limiter>`)
by setting its ``rate_limiter`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    async:
                        rate_limiter: your_rate_limiter_name

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
                    <framework:transport name="async">
                        <option key="rate_limiter">your_rate_limiter_name</option>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->messenger()
                ->transport('async')
                    ->options(['rate_limiter' => 'your_rate_limiter_name'])
            ;
        };

.. caution::

    When a rate limiter is configured on a transport, it will block the whole
    worker when the limit is hit. You should make sure you configure a dedicated
    worker for a rate limited transport to avoid other transports to be blocked.

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
                            # applies randomness to the delay that can prevent the thundering herd effect
                            # the value (between 0 and 1.0) is the percentage of 'delay' that will be added/subtracted
                            jitter: 0.1
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
                        <framework:retry-strategy max-retries="3" delay="1000" multiplier="2" max-delay="0" jitter="0.1"/>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('async_priority_high')
                ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                // default configuration
                ->retryStrategy()
                    ->maxRetries(3)
                    // milliseconds delay
                    ->delay(1000)
                    // causes the delay to be higher before each retry
                    // e.g. 1 second delay, 2 seconds, 4 seconds
                    ->multiplier(2)
                    ->maxDelay(0)
                    // applies randomness to the delay that can prevent the thundering herd effect
                    // the value (between 0 and 1.0) is the percentage of 'delay' that will be added/subtracted
                    ->jitter(0.1)
                    // override all of this with a service that
                    // implements Symfony\Component\Messenger\Retry\RetryStrategyInterface
                    ->service(null)
            ;
        };

.. versionadded:: 7.1

    The ``jitter`` option was introduced in Symfony 7.1.

.. tip::

    Symfony triggers a :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageRetriedEvent`
    when a message is retried so you can run your own logic.

.. note::

    Thanks to :class:`Symfony\\Component\\Messenger\\Stamp\\SerializedMessageStamp`,
    the serialized form of the message is saved, which prevents to serialize it
    again if the message is later retried.

Avoiding Retrying
~~~~~~~~~~~~~~~~~

Sometimes handling a message might fail in a way that you *know* is permanent
and should not be retried. If you throw
:class:`Symfony\\Component\\Messenger\\Exception\\UnrecoverableMessageHandlingException`,
the message will not be retried.

.. note::

    Messages that will not be retried, will still show up in the configured failure transport.
    If you want to avoid that, consider handling the error yourself and let the handler
    successfully end.

Forcing Retrying
~~~~~~~~~~~~~~~~

Sometimes handling a message must fail in a way that you *know* is temporary
and must be retried. If you throw
:class:`Symfony\\Component\\Messenger\\Exception\\RecoverableMessageHandlingException`,
the message will always be retried infinitely and ``max_retries`` setting will be ignored.

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            // after retrying, messages will be sent to the "failed" transport
            $messenger->failureTransport('failed');

            // ... other transports

            $messenger->transport('failed')
                ->dsn('doctrine://default?queue_name=failed');
        };

In this example, if handling a message fails 3 times (default ``max_retries``),
it will then be sent to the ``failed`` transport. While you *can* use
``messenger:consume failed`` to consume this like a normal transport, you'll
usually want to manually view the messages in the failure transport and choose
to retry them:

.. code-block:: terminal

    # see all messages in the failure transport with a default limit of 50
    $ php bin/console messenger:failed:show

    # see the 10 first messages
    $ php bin/console messenger:failed:show --max=10

    # see only MyClass messages
    $ php bin/console messenger:failed:show --class-filter='MyClass'

    # see the number of messages by message class
    $ php bin/console messenger:failed:show --stats

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

    # remove all messages in the failure transport
    $ php bin/console messenger:failed:remove --all

If the message fails again, it will be re-sent back to the failure transport
due to the normal :ref:`retry rules <messenger-retries-failures>`. Once the max
retry has been hit, the message will be discarded permanently.

Multiple Failed Transports
~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes it is not enough to have a single, global ``failed transport`` configured
because some messages are more important than others. In those cases, you can
override the failure transport for only specific transports:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                # after retrying, messages will be sent to the "failed" transport
                # by default if no "failed_transport" is configured inside a transport
                failure_transport: failed_default

                transports:
                    async_priority_high:
                        dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                        failure_transport: failed_high_priority

                    # since no failed transport is configured, the one used will be
                    # the global "failure_transport" set
                    async_priority_low:
                        dsn: 'doctrine://default?queue_name=async_priority_low'

                    failed_default: 'doctrine://default?queue_name=failed_default'
                    failed_high_priority: 'doctrine://default?queue_name=failed_high_priority'

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
                <!-- after retrying, messages will be sent to the "failed" transport
                by default if no "failed-transport" is configured inside a transport -->
                <framework:messenger failure-transport="failed_default">
                    <framework:transport name="async_priority_high" dsn="%env(MESSENGER_TRANSPORT_DSN)%" failure-transport="failed_high_priority"/>
                    <!-- since no "failed_transport" is configured, the one used will be
                    the global "failed_transport" set -->
                    <framework:transport name="async_priority_low" dsn="doctrine://default?queue_name=async_priority_low"/>

                    <framework:transport name="failed_default" dsn="doctrine://default?queue_name=failed_default"/>
                    <framework:transport name="failed_high_priority" dsn="doctrine://default?queue_name=failed_high_priority"/>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            // after retrying, messages will be sent to the "failed" transport
            // by default if no "failure_transport" is configured inside a transport
            $messenger->failureTransport('failed_default');

            $messenger->transport('async_priority_high')
                ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                ->failureTransport('failed_high_priority');

            // since no failed transport is configured, the one used will be
            // the global failure_transport set
           $messenger->transport('async_priority_low')
                ->dsn('doctrine://default?queue_name=async_priority_low');

           $messenger->transport('failed_default')
                ->dsn('doctrine://default?queue_name=failed_default');

           $messenger->transport('failed_high_priority')
                ->dsn('doctrine://default?queue_name=failed_high_priority');
        };

If there is no ``failure_transport`` defined globally or on the transport level,
the messages will be discarded after the number of retries.

The failed commands have an optional option ``--transport`` to specify
the ``failure_transport`` configured at the transport level.

.. code-block:: terminal

    # see all messages in "failure_transport" transport
    $ php bin/console messenger:failed:show --transport=failure_transport

    # retry specific messages from "failure_transport"
    $ php bin/console messenger:failed:retry 20 30 --transport=failure_transport --force

    # remove a message without retrying it from "failure_transport"
    $ php bin/console messenger:failed:remove 20 --transport=failure_transport

.. _messenger-transports-config:

Transport Configuration
-----------------------

Messenger supports a number of different transport types, each with their own
options. Options can be passed to the transport via a DSN string or configuration.

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=amqp://localhost/%2f/messages?auto_setup=false

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    my_transport:
                        dsn: "%env(MESSENGER_TRANSPORT_DSN)%"
                        options:
                            auto_setup: false

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
                    <framework:transport name="my_transport" dsn="%env(MESSENGER_TRANSPORT_DSN)%">
                        <framework:options auto-setup="false"/>
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('my_transport')
                ->dsn(env('MESSENGER_TRANSPORT_DSN'))
                ->options(['auto_setup' => false]);
        };

Options defined under ``options`` take precedence over ones defined in the DSN.

AMQP Transport
~~~~~~~~~~~~~~

The AMQP transport uses the AMQP PHP extension to send messages to queues like
RabbitMQ. Install it by running:

.. code-block:: terminal

    $ composer require symfony/amqp-messenger

The AMQP transport DSN may looks like this:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages

    # or use the AMQPS protocol
    MESSENGER_TRANSPORT_DSN=amqps://guest:guest@localhost/%2f/messages

If you want to use TLS/SSL encrypted AMQP, you must also provide a CA certificate.
Define the certificate path in the ``amqp.cacert`` PHP.ini setting
(e.g. ``amqp.cacert = /etc/ssl/certs``) or in the ``cacert`` parameter of the
DSN (e.g ``amqps://localhost?cacert=/etc/ssl/certs/``).

The default port used by TLS/SSL encrypted AMQP is 5671, but you can overwrite
it in the ``port`` parameter of the DSN (e.g. ``amqps://localhost?cacert=/etc/ssl/certs/&port=12345``).

.. note::

    By default, the transport will automatically create any exchanges, queues and
    binding keys that are needed. That can be disabled, but some functionality
    may not work correctly (like delayed queues).
    To not autocreate any queues, you can configure a transport with ``queues: []``.

.. note::

    You can limit the consumer of an AMQP transport to only process messages
    from some queues of an exchange. See :ref:`messenger-limit-queues`.

The transport has a number of other options, including ways to configure
the exchange, queues binding keys and more. See the documentation on
:class:`Symfony\\Component\\Messenger\\Bridge\\Amqp\\Transport\\Connection`.

The transport has a number of options:

============================================  =================================================  ===================================
     Option                                   Description                                        Default
============================================  =================================================  ===================================
``auto_setup``                                Whether the exchanges and queues should be         ``true``
                                              created automatically during send / get.
``cacert``                                    Path to the CA cert file in PEM format.
``cert``                                      Path to the client certificate in PEM format.
``channel_max``                               Specifies highest channel number that the server
                                              permits. 0 means standard extension limit
``confirm_timeout``                           Timeout in seconds for confirmation; if none
                                              specified, transport will not wait for message
                                              confirmation. Note: 0 or greater seconds. May be
                                              fractional.
``connect_timeout``                           Connection timeout. Note: 0 or greater seconds.
                                              May be fractional.
``frame_max``                                 The largest frame size that the server proposes
                                              for the connection, including frame header and
                                              end-byte. 0 means standard extension limit
                                              (depends on librabbimq default frame size limit)
``heartbeat``                                 The delay, in seconds, of the connection
                                              heartbeat that the server wants. 0 means the
                                              server does not want a heartbeat. Note,
                                              librabbitmq has limited heartbeat support, which
                                              means heartbeats checked only during blocking
                                              calls.
``host``                                      Hostname of the AMQP service
``key``                                       Path to the client key in PEM format.
``login``                                     Username to use to connect the AMQP service
``password``                                  Password to use to connect to the AMQP service
``persistent``                                                                                   ``'false'``
``port``                                      Port of the AMQP service
``read_timeout``                              Timeout in for income activity. Note: 0 or
                                              greater seconds. May be fractional.
``retry``
``sasl_method``
``connection_name``                           For custom connection names (requires at least
                                              version 1.10 of the PHP AMQP extension)
``verify``                                    Enable or disable peer verification. If peer
                                              verification is enabled then the common name in
                                              the server certificate must match the server
                                              name. Peer verification is enabled by default.
``vhost``                                     Virtual Host to use with the AMQP service
``write_timeout``                             Timeout in for outcome activity. Note: 0 or
                                              greater seconds. May be fractional.
``delay[queue_name_pattern]``                 Pattern to use to create the queues                ``delay_%exchange_name%_%routing_key%_%delay%``
``delay[exchange_name]``                      Name of the exchange to be used for the            ``delays``
                                              delayed/retried messages
``queues[name][arguments]``                   Extra arguments
``queues[name][binding_arguments]``           Arguments to be used while binding the queue.
``queues[name][binding_keys]``                The binding keys (if any) to bind to this queue
``queues[name][flags]``                       Queue flags                                        ``AMQP_DURABLE``
``exchange[arguments]``                       Extra arguments for the exchange (e.g.
                                              ``alternate-exchange``)
``exchange[default_publish_routing_key]``     Routing key to use when publishing, if none is
                                              specified on the message
``exchange[flags]``                           Exchange flags                                     ``AMQP_DURABLE``
``exchange[name]``                            Name of the exchange
``exchange[type]``                            Type of exchange                                   ``fanout``
============================================  =================================================  ===================================

You can also configure AMQP-specific settings on your message by adding
:class:`Symfony\\Component\\Messenger\\Bridge\\Amqp\\Transport\\AmqpStamp` to
your Envelope::

    use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
    // ...

    $attributes = [];
    $bus->dispatch(new SmsNotification(), [
        new AmqpStamp('custom-routing-key', AMQP_NOPARAM, $attributes),
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

.. tip::

    If your application faces socket exceptions or `high connection churn`_
    (shown by the rapid creation and deletion of connections), consider using
    `AMQProxy`_. This tool works as a gateway between Symfony Messenger and AMQP server,
    maintaining stable connections and minimizing overheads (which also improves
    the overall performance).

Doctrine Transport
~~~~~~~~~~~~~~~~~~

The Doctrine transport can be used to store messages in a database table.
Install it by running:

.. code-block:: terminal

    $ composer require symfony/doctrine-messenger

The Doctrine transport DSN may looks like this:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=doctrine://default

The format is ``doctrine://<connection_name>``, in case you have multiple connections
and want to use one other than the "default". The transport will automatically create
a table named ``messenger_messages``.

If you want to change the default table name, pass a custom table name in the
DSN by using the ``table_name`` option:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=doctrine://default?table_name=your_custom_table_name

Or, to create the table yourself, set the ``auto_setup`` option to ``false`` and
:ref:`generate a migration <doctrine-creating-the-database-tables-schema>`.

.. caution::

    The datetime property of the messages stored in the database uses the
    timezone of the current system. This may cause issues if multiple machines
    with different timezone configuration use the same storage.

The transport has a number of options:

==================  =====================================  ======================
Option              Description                            Default
==================  =====================================  ======================
table_name          Name of the table                      messenger_messages
queue_name          Name of the queue (a column in the     default
                    table, to use one table for
                    multiple transports)
redeliver_timeout   Timeout before retrying a message      3600
                    that's in the queue but in the
                    "handling" state (if a worker stopped
                    for some reason, this will occur,
                    eventually you should retry the
                    message) - in seconds.
auto_setup          Whether the table should be created
                    automatically during send / get.       true
==================  =====================================  ======================

.. note::

    Set ``redeliver_timeout`` to a greater value than your slowest message
    duration. Otherwise, some messages will start a second time while the
    first one is still being handled.

When using PostgreSQL, you have access to the following options to leverage
the `LISTEN/NOTIFY`_ feature. This allow for a more performant approach
than the default polling behavior of the Doctrine transport because
PostgreSQL will directly notify the workers when a new message is inserted
in the table.

=======================  ==========================================  ======================
Option                   Description                                 Default
=======================  ==========================================  ======================
use_notify               Whether to use LISTEN/NOTIFY.               true
check_delayed_interval   The interval to check for delayed           60000
                         messages, in milliseconds.
                         Set to 0 to disable checks.
get_notify_timeout       The length of time to wait for a            0
                         response when calling
                         ``PDO::pgsqlGetNotify``, in milliseconds.
=======================  ==========================================  ======================

Beanstalkd Transport
~~~~~~~~~~~~~~~~~~~~

The Beanstalkd transport sends messages directly to a Beanstalkd work queue. Install
it by running:

.. code-block:: terminal

    $ composer require symfony/beanstalkd-messenger

The Beanstalkd transport DSN may looks like this:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=beanstalkd://localhost:11300?tube_name=foo&timeout=4&ttr=120

    # If no port, it will default to 11300
    MESSENGER_TRANSPORT_DSN=beanstalkd://localhost

The transport has a number of options:

==================  ===================================  ======================
     Option         Description                          Default
==================  ===================================  ======================
tube_name           Name of the queue                    default
timeout             Message reservation timeout          0 (will cause the
                    - in seconds.                        server to immediately
                                                         return either a
                                                         response or a
                                                         TransportException
                                                         will be thrown)
ttr                 The message time to run before it
                    is put back in the ready queue
                    - in seconds.                        90
==================  ===================================  ======================

.. _messenger-redis-transport:

Redis Transport
~~~~~~~~~~~~~~~

The Redis transport uses `streams`_ to queue messages. This transport requires
the Redis PHP extension (>=4.3) and a running Redis server (^5.0). Install it by
running:

.. code-block:: terminal

    $ composer require symfony/redis-messenger

The Redis transport DSN may looks like this:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
    # Full DSN Example
    MESSENGER_TRANSPORT_DSN=redis://password@localhost:6379/messages/symfony/consumer?auto_setup=true&serializer=1&stream_max_entries=0&dbindex=0
    # Redis Cluster Example
    MESSENGER_TRANSPORT_DSN=redis://host-01:6379,redis://host-02:6379,redis://host-03:6379,redis://host-04:6379
    # Unix Socket Example
    MESSENGER_TRANSPORT_DSN=redis:///var/run/redis.sock
    # TLS Example
    MESSENGER_TRANSPORT_DSN=rediss://localhost:6379/messages
    # Multiple Redis Sentinel Hosts Example
    MESSENGER_TRANSPORT_DSN=redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&sentinel_master=db

A number of options can be configured via the DSN or via the ``options`` key
under the transport in ``messenger.yaml``:

=======================  =====================================  =================================
Option                   Description                            Default
=======================  =====================================  =================================
stream                   The Redis stream name                  messages
group                    The Redis consumer group name          symfony
consumer                 Consumer name used in Redis            consumer
auto_setup               Create the Redis group automatically?  true
auth                     The Redis password
delete_after_ack         If ``true``, messages are deleted      true
                         automatically after processing them
delete_after_reject      If ``true``, messages are deleted      true
                         automatically if they are rejected
lazy                     Connect only when a connection is      false
                         really needed
serializer               How to serialize the final payload     ``Redis::SERIALIZER_PHP``
                         in Redis (the
                         ``Redis::OPT_SERIALIZER`` option)
stream_max_entries       The maximum number of entries which    ``0`` (which means "no trimming")
                         the stream will be trimmed to. Set
                         it to a large enough number to
                         avoid losing pending messages
redeliver_timeout        Timeout before retrying a pending      ``3600``
                         message which is owned by an
                         abandoned consumer (if a worker died
                         for some reason, this will occur,
                         eventually you should retry the
                         message) - in seconds.
claim_interval           Interval on which pending/abandoned    ``60000`` (1 Minute)
                         messages should be checked for to
                         claim - in milliseconds
persistent_id            String, if null connection is          null
                         non-persistent.
retry_interval           Int, value in milliseconds             ``0``
read_timeout             Float, value in seconds                ``0``
                         default indicates unlimited
timeout                  Connection timeout. Float, value in    ``0``
                         seconds default indicates unlimited
sentinel_master          String, if null or empty Sentinel      null
redis_sentinel           support is disabled
=======================  =====================================  =================================

.. versionadded:: 7.1

    The option ``redis_sentinel`` as an alias for ``sentinel_master`` was introduced
    in Symfony 7.1.

.. caution::

    There should never be more than one ``messenger:consume`` command running with the same
    combination of ``stream``, ``group`` and ``consumer``, or messages could end up being
    handled more than once. If you run multiple queue workers, ``consumer`` can be set to an
    environment variable, like ``%env(MESSENGER_CONSUMER_NAME)%``, set by Supervisor
    (example below) or any other service used to manage the worker processes.
    In a container environment, the ``HOSTNAME`` can be used as the consumer name, since
    there is only one worker per container/host. If using Kubernetes to orchestrate the
    containers, consider using a ``StatefulSet`` to have stable names.

.. tip::

    Set ``delete_after_ack`` to ``true`` (if you use a single group) or define
    ``stream_max_entries`` (if you can estimate how many max entries is acceptable
    in your case) to avoid memory leaks. Otherwise, all messages will remain
    forever in Redis.

In Memory Transport
~~~~~~~~~~~~~~~~~~~

The ``in-memory`` transport does not actually deliver messages. Instead, it
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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('async_priority_normal')
                ->dsn('in-memory://');
        };

Then, while testing, messages will *not* be delivered to the real transport.
Even better, in a test, you can check that exactly one message was sent
during a request::

    // tests/Controller/DefaultControllerTest.php
    namespace App\Tests\Controller;

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

    class DefaultControllerTest extends WebTestCase
    {
        public function testSomething(): void
        {
            $client = static::createClient();
            // ...

            $this->assertSame(200, $client->getResponse()->getStatusCode());

            /** @var InMemoryTransport $transport */
            $transport = $this->getContainer()->get('messenger.transport.async_priority_normal');
            $this->assertCount(1, $transport->getSent());
        }
    }

The transport has a number of options:

``serialize`` (boolean, default: ``false``)
    Whether to serialize messages or not. This is useful to test an additional
    layer, especially when you use your own message serializer.

.. note::

    All ``in-memory`` transports will be reset automatically after each test **in**
    test classes extending
    :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\KernelTestCase`
    or :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase`.

Amazon SQS
~~~~~~~~~~

The Amazon SQS transport is perfect for applications hosted on AWS. Install it by
running:

.. code-block:: terminal

    $ composer require symfony/amazon-sqs-messenger

The SQS transport DSN may looks like this:

.. code-block:: env

    # .env
    MESSENGER_TRANSPORT_DSN=https://sqs.eu-west-3.amazonaws.com/123456789012/messages?access_key=AKIAIOSFODNN7EXAMPLE&secret_key=j17M97ffSVoKI0briFoo9a
    MESSENGER_TRANSPORT_DSN=sqs://localhost:9494/messages?sslmode=disable

.. note::

    The transport will automatically create queues that are needed. This
    can be disabled by setting the ``auto_setup`` option to ``false``.

.. tip::

    Before sending or receiving a message, Symfony needs to convert the queue
    name into an AWS queue URL by calling the ``GetQueueUrl`` API in AWS. This
    extra API call can be avoided by providing a DSN which is the queue URL.

The transport has a number of options:

======================  ======================================  ===================================
     Option             Description                             Default
======================  ======================================  ===================================
``access_key``          AWS access key                          must be urlencoded
``account``             Identifier of the AWS account           The owner of the credentials
``auto_setup``          Whether the queue should be created     ``true``
                        automatically during send / get.
``buffer_size``         Number of messages to prefetch          9
``debug``               If ``true`` it logs all HTTP requests   ``false``
                        and responses (it impacts performance)
``endpoint``            Absolute URL to the SQS service         https://sqs.eu-west-1.amazonaws.com
``poll_timeout``        Wait for new message duration in        0.1
                        seconds
``queue_name``          Name of the queue                       messages
``region``              Name of the AWS region                  eu-west-1
``secret_key``          AWS secret key                          must be urlencoded
``session_token``       AWS session token
``visibility_timeout``  Amount of seconds the message will      Queue's configuration
                        not be visible (`Visibility Timeout`_)
``wait_time``           `Long polling`_ duration in seconds     20
======================  ======================================  ===================================

.. note::

    The ``wait_time`` parameter defines the maximum duration Amazon SQS should
    wait until a message is available in a queue before sending a response.
    It helps reducing the cost of using Amazon SQS by eliminating the number
    of empty responses.

    The ``poll_timeout`` parameter defines the duration the receiver should wait
    before returning null. It avoids blocking other receivers from being called.

.. note::

    If the queue name is suffixed by ``.fifo``, AWS will create a `FIFO queue`_.
    Use the stamp :class:`Symfony\\Component\\Messenger\\Bridge\\AmazonSqs\\Transport\\AmazonSqsFifoStamp`
    to define the ``Message group ID`` and the ``Message deduplication ID``.

    Another possibility is to enable the
    :class:`Symfony\\Component\\Messenger\\Bridge\\AmazonSqs\\Middleware\\AddFifoStampMiddleware`.
    If your message implements
    :class:`Symfony\\Component\\Messenger\\Bridge\\AmazonSqs\\MessageDeduplicationAwareInterface`,
    the middleware will automatically add the
    :class:`Symfony\\Component\\Messenger\\Bridge\\AmazonSqs\\Transport\\AmazonSqsFifoStamp`
    and set the ``Message deduplication ID``. Additionally, if your message implements the
    :class:`Symfony\\Component\\Messenger\\Bridge\\AmazonSqs\\MessageGroupAwareInterface`,
    the middleware will automatically set the ``Message group ID`` of the stamp.

    You can learn more about middlewares in
    :ref:`the dedicated section <messenger_middleware>`.

    FIFO queues don't support setting a delay per message, a value of ``delay: 0``
    is required in the retry strategy settings.

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->serializer()
                ->defaultSerializer('messenger.transport.symfony_serializer')
                ->symfonySerializer()
                    ->format('json')
                    ->context('foo', 'bar');

            $messenger->transport('async_priority_normal')
                ->dsn('...')
                ->serializer('messenger.transport.symfony_serializer');
        };

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

Running Commands And External Processes
---------------------------------------

Trigger a Command
~~~~~~~~~~~~~~~~~

It is possible to trigger any command by dispatching a
:class:`Symfony\\Component\\Console\\Messenger\\RunCommandMessage`. Symfony
will take care of handling this message and execute the command passed
to the message parameter::

    use Symfony\Component\Console\Messenger\RunCommandMessage;
    use Symfony\Component\Messenger\MessageBusInterface;

    class CleanUpService
    {
        public function __construct(private readonly MessageBusInterface $bus)
        {
        }

        public function cleanUp(): void
        {
            // Long task with some caching...

            // Once finished, dispatch some clean up commands
            $this->bus->dispatch(new RunCommandMessage('app:my-cache:clean-up --dir=var/temp'));
            $this->bus->dispatch(new RunCommandMessage('cache:clear'));
        }
    }

You can configure the behavior in the case of something going wrong during command
execution. To do so, you can use the ``throwOnFailure`` and ``catchExceptions``
parameters when creating your instance of
:class:`Symfony\\Component\\Console\\Messenger\\RunCommandMessage`.

Once handled, the handler will return a
:class:`Symfony\\Component\\Console\\Messenger\\RunCommandContext` which
contains many useful information such as the exit code or the output of the
process. You can refer to the page dedicated on
:ref:`handler results <messenger-getting-handler-results>` for more information.

Trigger An External Process
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Messenger comes with a handy helper to run external processes by
dispatching a message. This takes advantages of the
:doc:`Process component </components/process>`. By dispatching a
:class:`Symfony\\Component\\Process\\Messenger\\RunProcessMessage`, Messenger
will take care of creating a new process with the parameters you passed::

    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Process\Messenger\RunProcessMessage;

    class CleanUpService
    {
        public function __construct(private readonly MessageBusInterface $bus)
        {
        }

        public function cleanUp(): void
        {
            $this->bus->dispatch(new RunProcessMessage(['rm', '-rf', 'var/log/temp/*'], cwd: '/my/custom/working-dir'));

            // ...
        }
    }

Once handled, the handler will return a
:class:`Symfony\\Component\\Process\\Messenger\\RunProcessContext` which
contains many useful information such as the exit code or the output of the
process. You can refer to the page dedicated on
:ref:`handler results <messenger-getting-handler-results>` for more information.

Pinging A Webservice
--------------------

Sometimes, you may need to regularly ping a webservice to get its status, e.g.
is it up or down. It is possible to do so by dispatching a
:class:`Symfony\\Component\\HttpClient\\Messenger\\PingWebhookMessage`::

    use Symfony\Component\HttpClient\Messenger\PingWebhookMessage;
    use Symfony\Component\Messenger\MessageBusInterface;

    class LivenessService
    {
        public function __construct(private readonly MessageBusInterface $bus)
        {
        }

        public function ping(): void
        {
            // An HttpExceptionInterface is thrown on 3xx/4xx/5xx
            $this->bus->dispatch(new PingWebhookMessage('GET', 'https://example.com/status'));

            // Ping, but does not throw on 3xx/4xx/5xx
            $this->bus->dispatch(new PingWebhookMessage('GET', 'https://example.com/status', throw: false));

            // Any valid HttpClientInterface option can be used
            $this->bus->dispatch(new PingWebhookMessage('POST', 'https://example.com/status', [
                'headers' => [
                    'Authorization' => 'Bearer ...'
                ],
                'json' => [
                    'data' => 'some-data',
                ],
            ]));
        }
    }

The handler will return a
:class:`Symfony\\Contracts\\HttpClient\\ResponseInterface`, allowing you to
gather and process information returned by the HTTP request.

Getting Results from your Handlers
----------------------------------

When a message is handled, the :class:`Symfony\\Component\\Messenger\\Middleware\\HandleMessageMiddleware`
adds a :class:`Symfony\\Component\\Messenger\\Stamp\\HandledStamp` for each object that handled the message.
You can use this to get the value returned by the handler(s)::

    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Messenger\Stamp\HandledStamp;

    $envelope = $messageBus->dispatch(new SomeMessage());

    // get the value that was returned by the last message handler
    $handledStamp = $envelope->last(HandledStamp::class);
    $handledStamp->getResult();

    // or get info about all of handlers
    $handledStamps = $envelope->all(HandledStamp::class);

.. _messenger-getting-handler-results:

Getting Results when Working with Command & Query Buses
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Messenger component can be used in CQRS architectures where command & query
buses are central pieces of the application. Read Martin Fowler's
`article about CQRS`_ to learn more and
:ref:`how to configure multiple buses <messenger-multiple-buses>`.

As queries are usually synchronous and expected to be handled once,
getting the result from the handler is a common need.

A :class:`Symfony\\Component\\Messenger\\HandleTrait` exists to get the result
of the handler when processing synchronously. It also ensures that exactly one
handler is registered. The ``HandleTrait`` can be used in any class that has a
``$messageBus`` property::

    // src/Action/ListItems.php
    namespace App\Action;

    use App\Message\ListItemsQuery;
    use App\MessageHandler\ListItemsQueryResult;
    use Symfony\Component\Messenger\HandleTrait;
    use Symfony\Component\Messenger\MessageBusInterface;

    class ListItems
    {
        use HandleTrait;

        public function __construct(
            private MessageBusInterface $messageBus,
        ) {
        }

        public function __invoke(): void
        {
            $result = $this->query(new ListItemsQuery(/* ... */));

            // Do something with the result
            // ...
        }

        // Creating such a method is optional, but allows type-hinting the result
        private function query(ListItemsQuery $query): ListItemsQueryResult
        {
            return $this->handle($query);
        }
    }

Hence, you can use the trait to create command & query bus classes.
For example, you could create a special ``QueryBus`` class and inject it
wherever you need a query bus behavior instead of the ``MessageBusInterface``::

    // src/MessageBus/QueryBus.php
    namespace App\MessageBus;

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\HandleTrait;
    use Symfony\Component\Messenger\MessageBusInterface;

    class QueryBus
    {
        use HandleTrait;

        public function __construct(
            private MessageBusInterface $messageBus,
        ) {
        }

        /**
         * @param object|Envelope $query
         *
         * @return mixed The handler returned value
         */
        public function query($query): mixed
        {
            return $this->handle($query);
        }
    }

Customizing Handlers
--------------------

.. _messenger-handler-config:

Manually Configuring Handlers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Symfony will normally :ref:`find and register your handler automatically <messenger-handler>`.
But, you can also configure a handler manually - and pass it some extra config -
while using ``#AsMessageHandler`` attribute or tagging the handler service
with ``messenger.message_handler``.

.. configuration-block::

    .. code-block:: php-attributes

        // src/MessageHandler/SmsNotificationHandler.php
        namespace App\MessageHandler;

        use App\Message\OtherSmsNotification;
        use App\Message\SmsNotification;
        use Symfony\Component\Messenger\Attribute\AsMessageHandler;

        #[AsMessageHandler(fromTransport: 'async', priority: 10)]
        class SmsNotificationHandler
        {
            public function __invoke(SmsNotification $message): void
            {
                // ...
            }
        }

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

============================  ====================================================================================================
Option                        Description
============================  ====================================================================================================
``bus``                       Name of the bus from which the handler can receive messages, by default all buses.
``from_transport``            Name of the transport from which the handler can receive messages, by default all transports.
``handles``                   Type of messages (FQCN) that can be processed by the handler, only needed if can't be guessed by
                              type-hint.
``method``                    Name of the method that will process the message.
``priority``                  Priority of the handler when multiple handlers can process the same message.
============================  ====================================================================================================

.. _handler-subscriber-options:

Handling Multiple Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~

A single handler class can handle multiple messages. For that add the
``#AsMessageHandler`` attribute to all the handling methods::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\OtherSmsNotification;
    use App\Message\SmsNotification;

    class SmsNotificationHandler
    {
        #[AsMessageHandler]
        public function handleSmsNotification(SmsNotification $message): void
        {
            // ...
        }

        #[AsMessageHandler]
        public function handleOtherSmsNotification(OtherSmsNotification $message): void
        {
            // ...
        }
    }

.. _messenger-transactional-messages:

Transactional Messages: Handle New Messages After Handling is Done
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A message handler can ``dispatch`` new messages while handling others, to either
the same or a different bus (if the application has
:ref:`multiple buses <messenger-multiple-buses>`). Any errors or exceptions that
occur during this process can have unintended consequences, such as:

#. If using the ``DoctrineTransactionMiddleware`` and a dispatched message throws
   an exception, then any database transactions in the original handler will be
   rolled back.
#. If the message is dispatched to a different bus, then the dispatched message
   will be handled even if some code later in the current handler throws an exception.

An Example ``RegisterUser`` Process
...................................

Consider an application with both a *command* and an *event* bus. The application
dispatches a command named ``RegisterUser`` to the command bus. The command is
handled by the ``RegisterUserHandler`` which creates a ``User`` object, stores
that object to a database and dispatches a ``UserRegistered`` message to the event bus.

There are many handlers to the ``UserRegistered`` message, one handler may send
a welcome email to the new user. We are using the ``DoctrineTransactionMiddleware``
to wrap all database queries in one database transaction.

**Problem 1:** If an exception is thrown when sending the welcome email, then
the user will not be created because the ``DoctrineTransactionMiddleware`` will
rollback the Doctrine transaction, in which the user has been created.

**Problem 2:** If an exception is thrown when saving the user to the database,
the welcome email is still sent because it is handled asynchronously.

DispatchAfterCurrentBusMiddleware Middleware
............................................

For many applications, the desired behavior is to *only* handle messages that
are dispatched by a handler once that handler has fully finished. This can be done by
using the ``DispatchAfterCurrentBusMiddleware`` and adding a
``DispatchAfterCurrentBusStamp`` stamp to :ref:`the message Envelope <messenger-envelopes>`::

    // src/Messenger/CommandHandler/RegisterUserHandler.php
    namespace App\Messenger\CommandHandler;

    use App\Entity\User;
    use App\Messenger\Command\RegisterUser;
    use App\Messenger\Event\UserRegistered;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\MessageBusInterface;
    use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

    class RegisterUserHandler
    {
        public function __construct(
            private MessageBusInterface $eventBus,
            private EntityManagerInterface $em,
        ) {
        }

        public function __invoke(RegisterUser $command): void
        {
            $user = new User($command->getUuid(), $command->getName(), $command->getEmail());
            $this->em->persist($user);

            // The DispatchAfterCurrentBusStamp marks the event message to be handled
            // only if this handler does not throw an exception.

            $event = new UserRegistered($command->getUuid());
            $this->eventBus->dispatch(
                (new Envelope($event))
                    ->with(new DispatchAfterCurrentBusStamp())
            );

            // ...
        }
    }

.. code-block:: php

    // src/Messenger/EventSubscriber/WhenUserRegisteredThenSendWelcomeEmail.php
    namespace App\Messenger\EventSubscriber;

    use App\Entity\User;
    use App\Messenger\Event\UserRegistered;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\RawMessage;

    class WhenUserRegisteredThenSendWelcomeEmail
    {
        public function __construct(
            private MailerInterface $mailer,
            EntityManagerInterface $em,
        ) {
        }

        public function __invoke(UserRegistered $event): void
        {
            $user = $this->em->getRepository(User::class)->find($event->getUuid());

            $this->mailer->send(new RawMessage('Welcome '.$user->getFirstName()));
        }
    }

This means that the ``UserRegistered`` message would not be handled until
*after* the ``RegisterUserHandler`` had completed and the new ``User`` was
persisted to the database. If the ``RegisterUserHandler`` encounters an
exception, the ``UserRegistered`` event will never be handled. And if an
exception is thrown while sending the welcome email, the Doctrine transaction
will not be rolled back.

.. note::

    If ``WhenUserRegisteredThenSendWelcomeEmail`` throws an exception, that
    exception will be wrapped into a ``DelayedMessageHandlingException``. Using
    ``DelayedMessageHandlingException::getWrappedExceptions`` will give you all
    exceptions that are thrown while handling a message with the
    ``DispatchAfterCurrentBusStamp``.

The ``dispatch_after_current_bus`` middleware is enabled by default. If you're
configuring your middleware manually, be sure to register
``dispatch_after_current_bus`` before ``doctrine_transaction`` in the middleware
chain. Also, the ``dispatch_after_current_bus`` middleware must be loaded for
*all* of the buses being used.

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

    #[AsMessageHandler(fromTransport: 'image_transport')]
    class ThumbnailUploadedImageHandler
    {
        public function __invoke(UploadedImage $uploadedImage): void
        {
            // do some thumbnailing
        }
    }

And similarly::

    // src/MessageHandler/NotifyAboutNewUploadedImageHandler.php
    // ...

    #[AsMessageHandler(fromTransport: 'async_priority_normal')]
    class NotifyAboutNewUploadedImageHandler
    {
        // ...
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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('async_priority_normal')->dsn('...');
            $messenger->transport('image_transport')->dsn('...');

            $messenger->routing('App\Message\UploadedImage')
                ->senders(['image_transport', 'async_priority_normal']);
        };

That's it! You can now consume each transport:

.. code-block:: terminal

    # will only call ThumbnailUploadedImageHandler when handling the message
    $ php bin/console messenger:consume image_transport -vv

    $ php bin/console messenger:consume async_priority_normal -vv

.. caution::

    If a handler does *not* have ``from_transport`` config, it will be executed
    on *every* transport that the message is received from.

Process Messages by Batches
~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can declare "special" handlers which will process messages by batch.
By doing so, the handler will wait for a certain amount of messages to be
pending before processing them. The declaration of a batch handler is done
by implementing
:class:`Symfony\\Component\\Messenger\\Handler\\BatchHandlerInterface`. The
:class:`Symfony\\Component\\Messenger\\Handler\\BatchHandlerTrait` is also
provided in order to ease the declaration of these special handlers::

    use Symfony\Component\Messenger\Handler\Acknowledger;
    use Symfony\Component\Messenger\Handler\BatchHandlerInterface;
    use Symfony\Component\Messenger\Handler\BatchHandlerTrait;

    class MyBatchHandler implements BatchHandlerInterface
    {
        use BatchHandlerTrait;

        public function __invoke(MyMessage $message, ?Acknowledger $ack = null): mixed
        {
            return $this->handle($message, $ack);
        }

        private function process(array $jobs): void
        {
            foreach ($jobs as [$message, $ack]) {
                try {
                    // Compute $result from $message...

                    // Acknowledge the processing of the message
                    $ack->ack($result);
                } catch (\Throwable $e) {
                    $ack->nack($e);
                }
            }
        }

        // Optionally, you can override some of the trait methods, such as the
        // `getBatchSize()` method, to specify your own batch size...
        private function getBatchSize(): int
        {
            return 100;
        }
    }

.. note::

    When the ``$ack`` argument of ``__invoke()`` is ``null``, the message is
    expected to be handled synchronously. Otherwise, ``__invoke()`` is
    expected to return the number of pending messages. The
    :class:`Symfony\\Component\\Messenger\\Handler\\BatchHandlerTrait` handles
    this for you.

.. note::

    By default, pending batches are flushed when the worker is idle as well
    as when it is stopped.

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

    public function index(MessageBusInterface $bus): void
    {
        // wait 5 seconds before processing
        $bus->dispatch(new SmsNotification('...'), [
            new DelayStamp(5000),
        ]);

        // or explicitly create an Envelope
        $bus->dispatch(new Envelope(new SmsNotification('...'), [
            new DelayStamp(5000),
        ]));

        // ...
    }

Internally, each message is wrapped in an ``Envelope``, which holds the message
and stamps. You can create this manually or allow the message bus to do it. There
are a variety of different stamps for different purposes and they're used internally
to track information about a message - like the message bus that's handling it
or if it's being retried after failure.

.. _messenger_middleware:

Middleware
~~~~~~~~~~

What happens when you dispatch a message to a message bus depends on its
collection of middleware and their order. By default, the middleware configured
for each bus looks like this:

#. ``add_bus_name_stamp_middleware`` - adds a stamp to record which bus this
   message was dispatched into;

#. ``dispatch_after_current_bus``- see :ref:`messenger-transactional-messages`;

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
middleware and *only* include your own.

If a middleware service is abstract, you can configure its constructor's arguments
and a different instance will be created per bus.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    messenger.bus.default:
                        # disable the default middleware
                        default_middleware: false

                        middleware:
                            # use and configure parts of the default middleware you want
                            - 'add_bus_name_stamp_middleware': ['messenger.bus.default']

                            # add your own services that implement Symfony\Component\Messenger\Middleware\MiddlewareInterface
                            - 'App\Middleware\MyMiddleware'
                            - 'App\Middleware\AnotherMiddleware'

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
                    <!-- default-middleware: disable the default middleware -->
                    <framework:bus name="messenger.bus.default" default-middleware="false">

                        <!-- use and configure parts of the default middleware you want -->
                        <framework:middleware id="add_bus_name_stamp_middleware">
                            <framework:argument>messenger.bus.default</framework:argument>
                        </framework:middleware>

                        <!-- add your own services that implement Symfony\Component\Messenger\Middleware\MiddlewareInterface -->
                        <framework:middleware id="App\Middleware\MyMiddleware"/>
                        <framework:middleware id="App\Middleware\AnotherMiddleware"/>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $bus = $messenger->bus('messenger.bus.default')
                ->defaultMiddleware(false); // disable the default middleware

            // use and configure parts of the default middleware you want
            $bus->middleware()->id('add_bus_name_stamp_middleware')->arguments(['messenger.bus.default']);

            // add your own services that implement Symfony\Component\Messenger\Middleware\MiddlewareInterface
            $bus->middleware()->id('App\Middleware\MyMiddleware');
            $bus->middleware()->id('App\Middleware\AnotherMiddleware');
        };

.. _middleware-doctrine:

Middleware for Doctrine
~~~~~~~~~~~~~~~~~~~~~~~

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

                            # logs an error when a Doctrine transaction was opened but not closed
                            - doctrine_open_transaction_logger

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
                        <framework:middleware id="doctrine_open_transaction_logger"/>

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
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $bus = $messenger->bus('command_bus');
            $bus->middleware()->id('doctrine_transaction');
            $bus->middleware()->id('doctrine_ping_connection');
            $bus->middleware()->id('doctrine_close_connection');
            $bus->middleware()->id('doctrine_open_transaction_logger');
            // Using another entity manager
            $bus->middleware()->id('doctrine_transaction')
                ->arguments(['custom']);
        };

Other Middlewares
~~~~~~~~~~~~~~~~~

Add the ``router_context`` middleware if you need to generate absolute URLs in
the consumer (e.g. render a template with links). This middleware stores the
original request context (i.e. the host, the HTTP port, etc.) which is needed
when building absolute URLs.

Add the ``validation`` middleware if you need to validate the message
object using the :doc:`Validator component </components/validator>` before handling it.
If validation fails, a ``ValidationFailedException`` will be thrown. The
:class:`Symfony\\Component\\Messenger\\Stamp\\ValidationStamp` can be used
to configure the validation groups.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                buses:
                    command_bus:
                        middleware:
                            - router_context
                            - validation

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
                        <framework:middleware id="router_context"/>
                        <framework:middleware id="validation"/>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $bus = $messenger->bus('command_bus');
            $bus->middleware()->id('router_context');
            $bus->middleware()->id('validation');
        };

Messenger Events
~~~~~~~~~~~~~~~~

In addition to middleware, Messenger also dispatches several events. You can
:doc:`create an event listener </event_dispatcher>` to hook into various parts
of the process. For each, the event class is the event name:

* :class:`Symfony\\Component\\Messenger\\Event\\SendMessageToTransportsEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageFailedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageHandledEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageReceivedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerMessageRetriedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerRateLimitedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerRunningEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerStartedEvent`
* :class:`Symfony\\Component\\Messenger\\Event\\WorkerStoppedEvent`

Additional Handler Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It's possible to have messenger pass additional data to the message handler
using the :class:`Symfony\\Component\\Messenger\\Stamp\\HandlerArgumentsStamp`.
Add this stamp to the envelope in a middleware and fill it with any additional
data you want to have available in the handler::

    // src/Messenger/AdditionalArgumentMiddleware.php
    namespace App\Messenger;

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
    use Symfony\Component\Messenger\Middleware\StackInterface;
    use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

    final class AdditionalArgumentMiddleware implements MiddlewareInterface
    {
        public function handle(Envelope $envelope, StackInterface $stack): Envelope
        {
            $envelope = $envelope->with(new HandlerArgumentsStamp([
                $this->resolveAdditionalArgument($envelope->getMessage()),
            ]));

            return $stack->next()->handle($envelope, $stack);
        }

        private function resolveAdditionalArgument(object $message): mixed
        {
            // ...
        }
    }

Then your handler will look like this::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\SmsNotification;

    final class SmsNotificationHandler
    {
        public function __invoke(SmsNotification $message, mixed $additionalArgument)
        {
            // ...
        }
    }

Message Serializer For Custom Data Formats
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you receive messages from other applications, it's possible that they are not
exactly in the format you need. Not all applications will return a JSON message
with ``body`` and ``headers`` fields. In those cases, you'll need to create a
new message serializer implementing the
:class:`Symfony\\Component\\Messenger\\Transport\\Serialization\\SerializerInterface`.
Let's say you want to create a message decoder::

    namespace App\Messenger\Serializer;

    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

    class MessageWithTokenDecoder implements SerializerInterface
    {
        public function decode(array $encodedEnvelope): Envelope
        {
            try {
                // parse the data you received with your custom fields
                $data = $encodedEnvelope['data'];
                $data['token'] = $encodedEnvelope['token'];

                // other operations like getting information from stamps
            } catch (\Throwable $throwable) {
                // wrap any exception that may occur in the envelope to send it to the failure transport
                return new Envelope($throwable);
            }

            return new Envelope($data);
        }

        public function encode(Envelope $envelope): array
        {
            // this decoder does not encode messages, but you can implement it by returning
            // an array with serialized stamps if you need to send messages in a custom format
            throw new \LogicException('This serializer is only used for decoding messages.');
        }
    }

The next step is to tell Symfony to use this serializer in one or more of your
transports:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                transports:
                    my_transport:
                        dsn: '%env(MY_TRANSPORT_DSN)%'
                        serializer: 'App\Messenger\Serializer\MessageWithTokenDecoder'

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
                    <framework:transport name="my_transport" dsn="%env(MY_TRANSPORT_DSN)%" serializer="App\Messenger\Serializer\MessageWithTokenDecoder">
                        <!-- ... -->
                    </framework:transport>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use App\Messenger\Serializer\MessageWithTokenDecoder;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $messenger = $framework->messenger();

            $messenger->transport('my_transport')
                ->dsn('%env(MY_TRANSPORT_DSN)%')
                ->serializer(MessageWithTokenDecoder::class);
        };

.. _messenger-multiple-buses:

Multiple Buses, Command & Event Buses
-------------------------------------

Messenger gives you a single message bus service by default. But, you can configure
as many as you want, creating "command", "query" or "event" buses and controlling
their middleware.

A common architecture when building applications is to separate commands from
queries. Commands are actions that do something and queries fetch data. This
is called CQRS (Command Query Responsibility Segregation). See Martin Fowler's
`article about CQRS`_ to learn more. This architecture could be used together
with the Messenger component by defining multiple buses.

A **command bus** is a little different from a **query bus**. For example, command
buses usually don't provide any results and query buses are rarely asynchronous.
You can configure these buses and their rules by using middleware.

It might also be a good idea to separate actions from reactions by introducing
an **event bus**. The event bus could have zero or more subscribers.

.. configuration-block::

    .. code-block:: yaml

        framework:
            messenger:
                # The bus that is going to be injected when injecting MessageBusInterface
                default_bus: command.bus
                buses:
                    command.bus:
                        middleware:
                            - validation
                            - doctrine_transaction
                    query.bus:
                        middleware:
                            - validation
                    event.bus:
                        default_middleware:
                            enabled: true
                            # set "allow_no_handlers" to true (default is false) to allow having
                            # no handler configured for this bus without throwing an exception
                            allow_no_handlers: false
                            # set "allow_no_senders" to false (default is true) to throw an exception
                            # if no sender is configured for this bus
                            allow_no_senders: true
                        middleware:
                            - validation

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
                <!-- The bus that is going to be injected when injecting MessageBusInterface -->
                <framework:messenger default-bus="command.bus">
                    <framework:bus name="command.bus">
                        <framework:middleware id="validation"/>
                        <framework:middleware id="doctrine_transaction"/>
                    </framework:bus>
                    <framework:bus name="query.bus">
                        <framework:middleware id="validation"/>
                    </framework:bus>
                    <framework:bus name="event.bus">
                        <!-- set "allow-no-handlers" to true (default is false) to allow having
                              no handler configured for this bus without throwing an exception -->
                        <!-- set "allow-no-senders" to false (default is true) to throw an exception
                             if no sender is configured for this bus -->
                        <framework:default-middleware enabled="true" allow-no-handlers="false" allow-no-senders="true"/>
                        <framework:middleware id="validation"/>
                    </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/messenger.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // The bus that is going to be injected when injecting MessageBusInterface
            $framework->messenger()->defaultBus('command.bus');

            $commandBus = $framework->messenger()->bus('command.bus');
            $commandBus->middleware()->id('validation');
            $commandBus->middleware()->id('doctrine_transaction');

            $queryBus = $framework->messenger()->bus('query.bus');
            $queryBus->middleware()->id('validation');

            $eventBus = $framework->messenger()->bus('event.bus');
            $eventBus->defaultMiddleware()
                ->enabled(true)
                // set "allowNoHandlers" to true (default is false) to allow having
                // no handler configured for this bus without throwing an exception
                ->allowNoHandlers(false)
                // set "allowNoSenders" to false (default is true) to throw an exception
                // if no sender is configured for this bus
                ->allowNoSenders(true)
            ;
            $eventBus->middleware()->id('validation');
        };

This will create three new services:

* ``command.bus``: autowireable with the :class:`Symfony\\Component\\Messenger\\MessageBusInterface`
  type-hint (because this is the ``default_bus``);

* ``query.bus``: autowireable with ``MessageBusInterface $queryBus``;

* ``event.bus``: autowireable with ``MessageBusInterface $eventBus``.

Restrict Handlers per Bus
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, each handler will be available to handle messages on *all*
of your buses. To prevent dispatching a message to the wrong bus without an error,
you can restrict each handler to a specific bus using the ``messenger.message_handler`` tag:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\MessageHandler\SomeCommandHandler:
                tags: [{ name: messenger.message_handler, bus: command.bus }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\MessageHandler\SomeCommandHandler">
                    <tag name="messenger.message_handler" bus="command.bus"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        $container->services()
            ->set(App\MessageHandler\SomeCommandHandler::class)
            ->tag('messenger.message_handler', ['bus' => 'command.bus']);

This way, the ``App\MessageHandler\SomeCommandHandler`` handler will only be
known by the ``command.bus`` bus.

You can also automatically add this tag to a number of classes by using
the :ref:`_instanceof service configuration <di-instanceof>`. Using this,
you can determine the message bus based on an implemented interface:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            _instanceof:
                # all services implementing the CommandHandlerInterface
                # will be registered on the command.bus bus
                App\MessageHandler\CommandHandlerInterface:
                    tags:
                        - { name: messenger.message_handler, bus: command.bus }

                # while those implementing QueryHandlerInterface will be
                # registered on the query.bus bus
                App\MessageHandler\QueryHandlerInterface:
                    tags:
                        - { name: messenger.message_handler, bus: query.bus }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- all services implementing the CommandHandlerInterface
                     will be registered on the command.bus bus -->
                <instanceof id="App\MessageHandler\CommandHandlerInterface">
                    <tag name="messenger.message_handler" bus="command.bus"/>
                </instanceof>

                <!-- while those implementing QueryHandlerInterface will be
                     registered on the query.bus bus -->
                <instanceof id="App\MessageHandler\QueryHandlerInterface">
                    <tag name="messenger.message_handler" bus="query.bus"/>
                </instanceof>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\MessageHandler\CommandHandlerInterface;
        use App\MessageHandler\QueryHandlerInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // ...

            // all services implementing the CommandHandlerInterface
            // will be registered on the command.bus bus
            $services->instanceof(CommandHandlerInterface::class)
                ->tag('messenger.message_handler', ['bus' => 'command.bus']);

            // while those implementing QueryHandlerInterface will be
            // registered on the query.bus bus
            $services->instanceof(QueryHandlerInterface::class)
                ->tag('messenger.message_handler', ['bus' => 'query.bus']);
        };

Debugging the Buses
~~~~~~~~~~~~~~~~~~~

The ``debug:messenger`` command lists available messages & handlers per bus.
You can also restrict the list to a specific bus by providing its name as an argument.

.. code-block:: terminal

    $ php bin/console debug:messenger

      Messenger
      =========

      command.bus
      -----------

       The following messages can be dispatched:

       ---------------------------------------------------------------------------------------
        App\Message\DummyCommand
            handled by App\MessageHandler\DummyCommandHandler
        App\Message\MultipleBusesMessage
            handled by App\MessageHandler\MultipleBusesMessageHandler
       ---------------------------------------------------------------------------------------

      query.bus
      ---------

       The following messages can be dispatched:

       ---------------------------------------------------------------------------------------
        App\Message\DummyQuery
            handled by App\MessageHandler\DummyQueryHandler
        App\Message\MultipleBusesMessage
            handled by App\MessageHandler\MultipleBusesMessageHandler
       ---------------------------------------------------------------------------------------

.. tip::

    The command will also show the PHPDoc description of the message and handler classes.

Redispatching a Message
-----------------------

If you want to redispatch a message (using the same transport and envelope), create
a new :class:`Symfony\\Component\\Messenger\\Message\\RedispatchMessage` and dispatch
it through your bus. Reusing the same ``SmsNotification`` example shown earlier::

    // src/MessageHandler/SmsNotificationHandler.php
    namespace App\MessageHandler;

    use App\Message\SmsNotification;
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;
    use Symfony\Component\Messenger\Message\RedispatchMessage;
    use Symfony\Component\Messenger\MessageBusInterface;

    #[AsMessageHandler]
    class SmsNotificationHandler
    {
        public function __construct(private MessageBusInterface $bus)
        {
        }

        public function __invoke(SmsNotification $message): void
        {
            // do something with the message
            // then redispatch it based on your own logic

            if ($needsRedispatch) {
                $this->bus->dispatch(new RedispatchMessage($message));
            }
        }
    }

The built-in :class:`Symfony\\Component\\Messenger\\Handler\\RedispatchMessageHandler`
will take care of this message to redispatch it through the same bus it was
dispatched at first. You can also use the second argument of the ``RedispatchMessage``
constructor to provide transports to use when redispatching the message.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /messenger/*

.. _`Enqueue's transport`: https://github.com/sroze/messenger-enqueue-transport
.. _`streams`: https://redis.io/topics/streams-intro
.. _`Supervisor docs`: http://supervisord.org/
.. _`PCNTL`: https://www.php.net/manual/book.pcntl.php
.. _`systemd docs`: https://www.freedesktop.org/wiki/Software/systemd/
.. _`SymfonyCasts' message serializer tutorial`: https://symfonycasts.com/screencast/messenger/transport-serializer
.. _`Long polling`: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-short-and-long-polling.html
.. _`Visibility Timeout`: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-visibility-timeout.html
.. _`FIFO queue`: https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/FIFO-queues.html
.. _`LISTEN/NOTIFY`: https://www.postgresql.org/docs/current/sql-notify.html
.. _`AMQProxy`: https://github.com/cloudamqp/amqproxy
.. _`high connection churn`: https://www.rabbitmq.com/connections.html#high-connection-churn
.. _`article about CQRS`: https://martinfowler.com/bliki/CQRS.html
