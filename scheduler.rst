Scheduler
=========

.. versionadded:: 6.3

    The Scheduler component was introduced in Symfony 6.3

Scheduler is a component designed to manage task scheduling within your PHP application - like running a task each night at 3 am, every 2 weeks except for holidays or any schedule you can imagine.

This component proves highly beneficial for tasks such as database cleanup, automated maintenance (e.g., cache clearing), background processing (queue handling, data synchronization), periodic data updates, or even scheduled notifications (emails, alerts), and more.

This document focuses on using the Scheduler component in the context of a full stack Symfony application.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the scheduler component:

.. code-block:: terminal

    $ composer require symfony/scheduler


Introduction to the case
------------------------

Embarking on a task is one thing, but often, the need to repeat that task looms large.
While one could resort to issuing commands and scheduling them with cron jobs, this approach involves external tools and additional configuration.

The Scheduler component emerges as a solution, allowing you to retain control, configuration, and maintenance of task scheduling within our PHP application.

At its core, scheduler allows you to create a task (called a message) that is executed by a service and repeated on some schedule.
Does this sound familiar? Think :doc:`Symfony Messenger docs </components/messenger>`.

But while the system of Messenger proves very useful in various scenarios, there are instances where its capabilities
fall short, particularly when dealing with repetitive tasks at regular intervals.

Let's dive into a practical example within the context of a sales company.

Imagine the company's goal is to send diverse sales reports to customers based on the specific reports each customer chooses to receive.
In constructing the schedule for this scenario, the following steps are taken:

#. Iterate over reports stored in the database and create a recurring task for each report, considering its unique properties. This task, however, should not be generated during holiday periods.

#. Furthermore, you encounter another critical task that needs scheduling: the periodic cleanup of outdated files that are no longer relevant.

On the basis of a case study in the context of a full stack Symfony application, let's dive in and explore how you can set up your system.

Symfony Scheduler basics
------------------------

Differences and parallels between Messenger and Scheduler
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The primary goal is to generate and process reports generation while also handling the removal of outdated reports at specified intervals.

As mentioned, this component, even if it's an independent component, it draws its foundation and inspiration from the Messenger component.

On one hand, it adopts well-established concepts from Messenger (such as message, handler, bus, transport, etc.).
For example, the task of creating a report is considered as a message by the Scheduler, that will be directed, and processed by the corresponding handler.::

    // src/Scheduler/Message/SendDailySalesReports.php
    namespace App\Scheduler\Message;

    class SendDailySalesReports
    {
        public function __construct(private string $id) {}

        public function getId(): int
        {
            return $this->id;
        }
    }

    // src/Scheduler/Handler/SendDailySalesReportsHandler.php
    namespace App\Scheduler\Handler;

    #[AsMessageHandler]
    class SendDailySalesReportsHandler
    {
        public function __invoke(SendDailySalesReports $message)
        {
            // ... do some work - Send the report to the relevant individuals. !
        }
    }

However, unlike Messenger, the messages will not be dispatched in the first instance. Instead, the aim is to create them based on a predefined frequency.

This is where the specific transport in Scheduler, known as the :class:`Symfony\\Component\\Scheduler\\Messenger\\SchedulerTransport`, plays a crucial role.
The transport autonomously generates directly various messages according to the assigned frequencies.

From (Messenger cycle):

.. image:: /_images/components/messenger/basic_cycle.png
    :alt: Symfony Messenger basic cycle

To (Scheduler cycle):

.. image:: /_images/components/scheduler/scheduler_cycle.png
    :alt: Symfony Scheduler basic cycle

In Scheduler, the concept of a message takes on a very particular characteristic;
it should be recurrent: It's a :class:`Symfony\\Component\\Scheduler\\RecurringMessage`.

Attach Recurring Messages to a Schedule
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to generate various messages based on their defined frequencies, configuration is necessary.
The heart of the scheduling process and its configuration resides in a class that must extend the :class:`Symfony\\Component\\Scheduler\\ScheduleProviderInterface`.

The purpose of this provider is to return a schedule through the method :method:`Symfony\\Component\\Scheduler\\ScheduleProviderInterface::getSchedule` containing your different recurringMessages.

The :class:`Symfony\\Component\\Scheduler\\Attribute\\AsSchedule` attribute, which by default references the ``default`` named schedule, allows you to register on a particular schedule::

    // src/Scheduler/MyScheduleProvider.php
    namespace App\Scheduler;

    #[AsSchedule]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            // ...
        }
    }

.. tip::

    By default, if not specified, the schedule name will be ``default``.
    In Scheduler, the name of the transport is formed as follows: ``scheduler_nameofyourschedule``.

.. tip::

    It is a good practice to memoize your schedule to prevent unnecessary reconstruction if the ``getSchedule`` method is checked by another service or internally within Symfony


Scheduling Recurring Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

First and foremost, a RecurringMessage is a message that will be associated with a trigger.

The trigger is what allows configuring the recurrence frequency of your message. Several options are available to us:

#. It can be a cron expression trigger:

.. configuration-block::

    .. code-block:: php

        RecurringMessage::cron(‘* * * * *’, new Message());

.. tip::

    `dragonmantank/cron-expression`_ is required to use the cron expression trigger.

    Also, `crontab_helper`_ is a good tool if you need help to construct/understand cron expressions

.. versionadded:: 6.4

    Since version 6.4, it is now possible to add and define a timezone as a 3rd argument

#. It can be a periodical trigger through various frequency formats (string / integer / DateInterval)

.. configuration-block::

    .. code-block:: php

        RecurringMessage::every('10 seconds', new Message());
        RecurringMessage::every('3 weeks', new Message());
        RecurringMessage::every('first Monday of next month', new Message());

        $from = new \DateTimeImmutable('13:47', new \DateTimeZone('Europe/Paris'));
        $until = '2023-06-12';
        RecurringMessage::every('first Monday of next month', new Message(), $from, $until);

#. It can be a custom trigger implementing :class:`Symfony\\Component\\Scheduler\\TriggerInterface`

If you go back to your scenario regarding reports generation based on your customer preferences.
If the basic frequency is set to a daily basis, you will need to implement a custom trigger due to the specific requirement of not generating reports during public holiday periods::

    // src/Scheduler/Trigger/NewUserWelcomeEmailHandler.php
    namespace App\Scheduler\Trigger;

    class ExcludeHolidaysTrigger implements TriggerInterface
    {
        public function __construct(private TriggerInterface $inner)
        {
        }

        public function __toString(): string
        {
            return $this->inner.' (except holidays)';
        }

        public function getNextRunDate(\DateTimeImmutable $run): ?\DateTimeImmutable
        {
            if (!$nextRun = $this->inner->getNextRunDate($run)) {
                return null;
            }

            while (!$this->isHoliday($nextRun) { // loop until you get the next run date that is not a holiday
                $nextRun = $this->inner->getNextRunDate($nextRun);
            }

            return $nextRun;
        }

        private function isHoliday(\DateTimeImmutable $timestamp): bool
        {
            // app specific logic to determine if $timestamp is on a holiday
            // returns true if holiday, false otherwise
        }
    }

Then, you would have to define your RecurringMessage

.. configuration-block::

    .. code-block:: php

        RecurringMessage::trigger(
            new ExcludeHolidaysTrigger( // your custom trigger wrapper
                CronExpressionTrigger::fromSpec('@daily'),
            ),
            new SendDailySalesReports(// ...),
        );

The RecurringMessages must be attached to a Schedule::

    // src/Scheduler/MyScheduleProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            return $this->schedule ??= (new Schedule())
                ->with(
                    RecurringMessage::trigger(
                        new ExcludeHolidaysTrigger( // your custom trigger wrapper
                            CronExpressionTrigger::fromSpec('@daily'),
                        ),
                    new SendDailySalesReports()),
                    RecurringMessage::cron(‘3 8 * * 1’, new CleanUpOldSalesReport())

                );
        }
    }

So, this RecurringMessage will encompass both the trigger, defining the generation frequency of the message, and the message itself, the one to be processed by a specific handler.

Consuming Messages (Running the Worker)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

After defining and attaching your RecurringMessages to a schedule, you'll need a mechanism to generate and 'consume' the messages according to their defined frequencies.
This can be achieved using the ``messenger:consume command`` since the Scheduler reuses the Messenger worker.

.. code-block:: terminal

    php bin/console messenger:consume scheduler_nameofyourschedule

    # use -vv if you need details about what's happening
    php bin/console messenger:consume scheduler_nameofyourschedule -vv

.. image:: /_images/components/scheduler/generate_consume.png
    :alt: Symfony Scheduler - generate and consume

.. versionadded:: 6.4

    Since version 6.4, you can define your message(s) via a ``callback``. This is achieved by defining a :class:`Symfony\\Component\\Scheduler\\Trigger\\CallbackMessageProvider`.


Debugging the Schedule
~~~~~~~~~~~~~~~~~~~~~~

The ``debug:scheduler`` command provides a list of schedules along with their recurring messages.
You can narrow down the list to a specific schedule.

.. versionadded:: 6.4

    Since version 6.4, you can even specify a date to determine the next run date using the ``--date`` option.
    Additionally, you have the option to display terminated recurring messages using the ``--all`` option.

.. code-block:: terminal

    $ php bin/console debug:scheduler

      Scheduler
      =========

      default
      -------

        ------------------- ------------------------- ----------------------
        Trigger             Provider                  Next Run
        ------------------- ------------------------- ----------------------
        every 2 days        App\Messenger\Foo(0:17..)  Sun, 03 Dec 2023 ...
        15 4 */3 * *        App\Messenger\Foo(0:17..)  Mon, 18 Dec 2023 ...
       -------------------- -------------------------- ---------------------

Efficient management with Symfony Scheduler
-------------------------------------------

However, if your worker becomes idle, since the messages from your schedule are generated on-the-fly by the schedulerTransport,
they won't be generated during this idle period.

While this might not pose a problem in certain situations, consider the impact for your sales company if a report is missed.

In this case, the scheduler has a feature that allows you to remember the last execution date of a message.
So, when it wakes up again, it looks at all the dates and can catch up on what it missed.

This is where the ``stateful`` option comes into play. This option helps you remember where you left off, which is super handy for those moments when the worker is idle and you need to catch up (for more details, see :doc:`cache </components/cache>`)::

    // src/Scheduler/MyScheduleProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $this->removeOldReports = RecurringMessage::cron(‘3 8 * * 1’, new CleanUpOldSalesReport());

            return $this->schedule ??= (new Schedule())
                ->with(
                    // ...
                );
                ->stateful($this->cache)
        }
    }

To scale your schedules more effectively, you can use multiple workers.
In such cases, a good practice is to add a :doc:`lock </components/lock>`. for some job concurrency optimization. It helps preventing the processing of a task from being duplicated.::

    // src/Scheduler/MyScheduleProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $this->removeOldReports = RecurringMessage::cron(‘3 8 * * 1’, new CleanUpOldSalesReport());

            return $this->schedule ??= (new Schedule())
                ->with(
                    // ...
                );
                ->lock($this->lockFactory->createLock(‘my-lock’)
        }
    }

.. tip::

    The processing time of a message matters.
    If it takes a long time, all subsequent message processing may be delayed. So, it's a good practice to anticipate this and plan for frequencies greater than the processing time of a message.

Additionally, for better scaling of your schedules, you have the option to wrap your message in a :class:`Symfony\\Component\\Messenger\\Message\\RedispatchMessage`.
This allows you to specify a transport on which your message will be redispatched before being further redispatched to its corresponding handler::

    // src/Scheduler/MyScheduleProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            return $this->schedule ??= (new Schedule())
                ->with(RecurringMessage::every('5 seconds’), new RedispatchMessage(new Message(), ‘async’))
                );
        }
    }

.. _dragonmantank/cron-expression: https://packagist.org/packages/dragonmantank/cron-expression
.. _crontab_helper: https://crontab.guru/
