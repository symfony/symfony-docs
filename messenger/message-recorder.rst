.. index::
    single: Messenger; Record messages; Transaction messages

Transactional Messages: Handle Events After CommandHandler Is Done
==================================================================

A message handler can ``dispatch`` new messages during execution, to either the same or
a different bus (if the application has `multiple buses </messenger/multiple_buses>`_).
Any errors or exceptions that occur during this process can have unintended consequences,
such as:

- If using the ``DoctrineTransactionMiddleware`` and a dispatched message throws an exception,
  then any database transactions in the original handler will be rolled back.
- If the message is dispatched to a different bus, then the dispatched message can still
  be handled even if the original handler encounters an exception.

An Example ``SignUpUser`` Process
---------------------------------

Let's take the example of an application with both a *command* and an *event* bus. The application
dispatches a command named ``SignUpUser`` to the command bus. The command is handled by the
``SignUpUserHandler`` which creates a ``User`` object, stores that object to a database and
dispatches a ``UserSignedUp`` event to the event bus.

There are many subscribers to the ``UserSignedUp`` event, one subscriber may send
a welcome email to the new user. We are using the ``DoctrineTransactionMiddleware``
to wrap all database queries in one database transaction.

**Problem 1:** If an exception is thrown when sending the welcome email, then the user
will not be created because the ``DoctrineTransactionMiddleware`` will rollback the
Doctrine transaction, in which the user has been created.

**Problem 2:** If an exception is thrown when saving the user to the database, the welcome
email is still sent because it is handled asynchronously.

``DispatchAfterCurrentBusMiddleware`` Middleware
------------------------------------------------

For many applications, the desired behavior is to have any messages dispatched by the handler
to `only` be handled after the handler finishes. This can be by using the
``DispatchAfterCurrentBusMiddleware`` middleware and adding a ``DispatchAfterCurrentBusStamp``
stamp to `the message Envelope </components/messenger#adding-metadata-to-messages-envelopes>`_.

Referencing the above example, this means that the ``UserSignedUp`` event would not be handled
until *after* the ``SignUpUserHandler`` had completed and the new ``User`` was persisted to the
database. If the ``SignUpUserHandler`` encounters an exception, the ``UserSignedUp`` event will
never be handled and if an exception is thrown while sending the welcome email, the Doctrine
transaction will not be rolled back.

The  ``dispatch_after_current_bus`` middleware is enabled by default. It is configured as the
first middleware on all busses. When doing a highly custom or special configuration, then make
sure ``dispatch_after_current_bus`` is registered before ``doctrine_transaction``
in the middleware chain.

**Note:** The ``dispatch_after_current_bus`` middleware must be loaded for *all* of the
buses. For the example, the middleware must be loaded for both the command and event bus.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                default_bus: messenger.bus.command

                buses:
                    messenger.bus.command:
                        middleware:
                            - validation
                            - doctrine_transaction
                    messenger.bus.event:
                        default_middleware: allow_no_handlers
                        middleware:
                            - validation
                            - doctrine_transaction

    .. code-block:: xml

        <!-- config/packages/cache.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:messenger default_bus="messenger.bus.command">
                  <framework:bus name="messenger.bus.command">
                      <framework:middleware id="validation">
                      <framework:middleware id="doctrine_transaction">
                  </framework:bus>
                  <framework:bus name="messenger.bus.command" default_middleware="allow_no_handlers">
                      <framework:middleware id="validation">
                      <framework:middleware id="doctrine_transaction">
                  </framework:bus>
                </framework:messenger>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/cache.php
        $container->loadFromExtension('framework', [
            'messenger' => [
                'default_bus' => 'messenger.bus.command',
                'buses' => [
                    'messenger.bus.command' => [
                        'middleware' => ['validation', 'doctrine_transaction'],
                    ],
                    'messenger.bus.event' => [
                        'default_middleware' => 'allow_no_handlers',
                        'middleware' => ['validation', 'doctrine_transaction'],
                    ],
                ],
            ],
        ]);

.. code-block:: php

    namespace App\Messenger\CommandHandler;

    use App\Entity\User;
    use App\Messenger\Command\SignUpUser;
    use App\Messenger\Event\UserSignedUp;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Messenger\Envelope;
    use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
    use Symfony\Component\Messenger\MessageBusInterface;

    class SignUpUserHandler
    {
        private $em;
        private $eventBus;

        public function __construct(MessageBusInterface $eventBus, EntityManagerInterface $em)
        {
            $this->eventBus = $eventBus;
            $this->em = $em;
        }

        public function __invoke(SignUpUser $command)
        {
            $user = new User($command->getUuid(), $command->getName(), $command->getEmail());
            $this->em->persist($user);

            // The DispatchAfterCurrentBusStamp marks the event message to be handled
            // only if this handler does not throw an exception.

            $event = new UserSignedUp($command->getUuid());
            $this->eventBus->dispatch(
                (new Envelope($event))
                    ->with(new DispatchAfterCurrentBusStamp())
            );
        }
    }

.. code-block:: php

    namespace App\Messenger\EventSubscriber;

    use App\Entity\User;
    use App\Messenger\Event\UserSignedUp;
    use Doctrine\ORM\EntityManagerInterface;

    class WhenUserSignedUpThenSendWelcomeEmail
    {
        private $em;
        private $mailer;

        public function __construct(MyMailer $mailer, EntityManagerInterface $em)
        {
            $this->mailer = $mailer;
            $this->em = $em;
        }

        public function __invoke(UserSignedUp $eent)
        {
            $user = $this->em->getRepository(User::class)->find(new User($event->getUuid()));

            $this->mailer->sendWelcomeEmail($user);
        }
    }

**Note:** If ``WhenUserSignedUpThenSendWelcomeEmail`` throws an exception, that exception
will be wrapped into a ``DelayedMessageHandlingException``. Using ``DelayedMessageHandlingException::getExceptions``
will give you all exceptions that are thrown while handing a message with the ``DispatchAfterCurrentBusStamp``.
