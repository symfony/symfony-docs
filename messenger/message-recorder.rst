.. index::
    single: Messenger; Record messages

Record Events Produced by a Handler
===================================

In an example application there is a command (a CQRS message) named ``CreateUser``.
That command is handled by the ``CreateUserHandler`` which creates
a ``User`` object, stores that object to a database and dispatches an ``UserCreatedEvent``.
That event is also a normal message but is handled by an *event* bus.

There are many subscribers to the ``UserCreatedEvent``, one subscriber may send
a welcome email to the new user. Since we are using the ``DoctrineTransactionMiddleware``
we wrap all database queries in one database transaction and rollback that transaction
if an exception is thrown. That means that if an exception is thrown when sending
the welcome email, then the user will not be created.

The solution to this issue is to not dispatch the ``UserCreatedEvent`` in the
``CreateUserHandler`` but to just "record" the events. The recorded events will
be dispatched after ``DoctrineTransactionMiddleware`` has committed the transaction.

To enable this, you simply just add the ``messenger.middleware.handles_recorded_messages``
middleware. Make sure it is registered before ``DoctrineTransactionMiddleware``
in the middleware chain.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/messenger.yaml
        framework:
            messenger:
                default_bus: messenger.bus.command
                buses:
                    messenger.bus.command:
                        middleware:
                            - messenger.middleware.validation
                            - messenger.middleware.handles_recorded_messages: ['@messenger.bus.event']
                              # Doctrine transaction must be after handles_recorded_messages middleware
                            - app.doctrine_transaction_middleware: ['default']
                    messenger.bus.event:
                        middleware:
                            - messenger.middleware.allow_no_handler
                            - messenger.middleware.validation

.. code-block:: php

    namespace App\Messenger\CommandHandler;

    use App\Entity\User;
    use App\Messenger\Command\CreateUser;
    use App\Messenger\Event\UserCreatedEvent;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Messenger\MessageRecorderInterface;

    class CreateUserHandler
    {
        private $em;
        private $eventRecorder;

        public function __construct(MessageRecorderInterface $eventRecorder, EntityManagerInterface $em)
        {
            $this->eventRecorder = $eventRecorder;
            $this->em = $em;
        }

        public function __invoke(CreateUser $command)
        {
            $user = new User($command->getUuid(), $command->getName(), $command->getEmail());
            $this->em->persist($user);

            $this->eventRecorder->record(new UserCreatedEvent($command->getUuid());
        }
    }
