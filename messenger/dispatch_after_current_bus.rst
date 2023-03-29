Transactional Messages: Handle New Messages After Handling is Done
==================================================================

A message handler can ``dispatch`` new messages while handling others, to either the
same or a different bus (if the application has
:doc:`multiple buses </messenger/multiple_buses>`). Any errors or exceptions that
occur during this process can have unintended consequences, such as:

- If using the ``DoctrineTransactionMiddleware`` and a dispatched message throws
  an exception, then any database transactions in the original handler will be
  rolled back.
- If the message is dispatched to a different bus, then the dispatched message
  will be handled even if some code later in the current handler throws an
  exception.

An Example ``RegisterUser`` Process
-----------------------------------

Let's take the example of an application with both a *command* and an *event*
bus. The application dispatches a command named ``RegisterUser`` to the command
bus. The command is handled by the ``RegisterUserHandler`` which creates a
``User`` object, stores that object to a database and dispatches a
``UserRegistered`` message to the event bus.

There are many handlers to the ``UserRegistered`` message, one handler may send
a welcome email to the new user. We are using the ``DoctrineTransactionMiddleware``
to wrap all database queries in one database transaction.

**Problem 1:** If an exception is thrown when sending the welcome email, then
the user will not be created because the ``DoctrineTransactionMiddleware`` will
rollback the Doctrine transaction, in which the user has been created.

**Problem 2:** If an exception is thrown when saving the user to the database,
the welcome email is still sent because it is handled asynchronously.

DispatchAfterCurrentBusMiddleware Middleware
--------------------------------------------

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

        public function __invoke(RegisterUser $command)
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

        public function __invoke(UserRegistered $event)
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
    ``DelayedMessageHandlingException::getExceptions`` will give you all
    exceptions that are thrown while handling a message with the
    ``DispatchAfterCurrentBusStamp``.

The ``dispatch_after_current_bus`` middleware is enabled by default. If you're
configuring your middleware manually, be sure to register
``dispatch_after_current_bus`` before ``doctrine_transaction`` in the middleware
chain. Also, the ``dispatch_after_current_bus`` middleware must be loaded for
*all* of the buses being used.
