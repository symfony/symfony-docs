Scheduler
=========

The scheduler component manages task scheduling within your PHP application, like
running a task each night at 3 AM, every two weeks except for holidays or any
other custom schedule you might need.

This component is useful to schedule tasks like maintenance (database cleanup,
cache clearing, etc.), background processing (queue handling, data synchronization,
etc.), periodic data updates, scheduled notifications (emails, alerts), and more.

This document focuses on using the Scheduler component in the context of a full
stack Symfony application.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the scheduler component:

.. code-block:: terminal

    $ composer require symfony/scheduler

Symfony Scheduler Basics
------------------------

The main benefit of using this component is that automation is managed by your
application, which gives you a lot of flexibility that is not possible with cron
jobs (e.g. dynamic schedules based on certain conditions).

At its core, the Scheduler component allows you to create a task (called a message)
that is executed by a service and repeated on some schedule. It has some similarities
with the :doc:`Symfony Messenger </components/messenger>` component (such as message,
handler, bus, transport, etc.), but the main difference is that Messenger can't
deal with repetitive tasks at regular intervals.

Consider the following example of an application that sends some reports to
customers on a scheduled basis. First, create a Scheduler message that represents
the task of creating a report::

    // src/Scheduler/Message/SendDailySalesReports.php
    namespace App\Scheduler\Message;

    class SendDailySalesReports
    {
        public function __construct(private int $id) {}

        public function getId(): int
        {
            return $this->id;
        }
    }

Next, create the handler that processes that kind of message::

    // src/Scheduler/Handler/SendDailySalesReportsHandler.php
    namespace App\Scheduler\Handler;

    use App\Scheduler\Message\SendDailySalesReports;
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;

    #[AsMessageHandler]
    class SendDailySalesReportsHandler
    {
        public function __invoke(SendDailySalesReports $message)
        {
            // ... do some work to send the report to the customers
        }
    }

Instead of sending these messages immediately (as in the Messenger component),
the goal is to create them based on a predefined frequency. This is possible
thanks to :class:`Symfony\\Component\\Scheduler\\Messenger\\SchedulerTransport`,
a special transport for Scheduler messages.

The transport generates, autonomously, various messages according to the assigned
frequencies. The following images illustrate the differences between the
processing of messages in Messenger and Scheduler components:

In Messenger:

.. image:: /_images/components/messenger/basic_cycle.png
    :alt: Symfony Messenger basic cycle

In Scheduler:

.. image:: /_images/components/scheduler/scheduler_cycle.png
    :alt: Symfony Scheduler basic cycle

Another important difference is that messages in the Scheduler component are
recurring. They are represented via the :class:`Symfony\\Component\\Scheduler\\RecurringMessage`
class.

.. _scheduler_attaching-recurring-messages:

Attaching Recurring Messages to a Schedule
------------------------------------------

The configuration of the message frequency is stored in a class that implements
:class:`Symfony\\Component\\Scheduler\\ScheduleProviderInterface`. This provider
uses the method :method:`Symfony\\Component\\Scheduler\\ScheduleProviderInterface::getSchedule`
to return a schedule containing the different recurring messages.

The :class:`Symfony\\Component\\Scheduler\\Attribute\\AsSchedule` attribute,
which by default references the schedule named ``default``, allows you to register
on a particular schedule::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    use Symfony\Component\Scheduler\Attribute\AsSchedule;
    use Symfony\Component\Scheduler\Schedule;
    use Symfony\Component\Scheduler\ScheduleProviderInterface;

    #[AsSchedule]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            // ...
        }
    }

.. tip::

    By default, the schedule name is ``default`` and the transport name follows
    the syntax: ``scheduler_nameofyourschedule`` (e.g. ``scheduler_default``).

.. tip::

    `Memoizing`_ your schedule is a good practice to prevent unnecessary reconstruction
    if the ``getSchedule()`` method is checked by another service.

Scheduling Recurring Messages
-----------------------------

A ``RecurringMessage`` is a message associated with a trigger, which configures
the frequency of the message. Symfony provides different types of triggers:

:class:`Symfony\\Component\\Scheduler\\Trigger\\CronExpressionTrigger`
    A trigger that uses the same syntax as the `cron command-line utility`_.

:class:`Symfony\\Component\\Scheduler\\Trigger\\CallbackTrigger`
    A trigger that uses a callback to determine the next run date.

:class:`Symfony\\Component\\Scheduler\\Trigger\\ExcludeTimeTrigger`
    A trigger that excludes certain times from a given trigger.

:class:`Symfony\\Component\\Scheduler\\Trigger\\JitterTrigger`
    A trigger that adds a random jitter to a given trigger. The jitter is some
    time that is added to the original triggering date/time. This
    allows to distribute the load of the scheduled tasks instead of running them
    all at the exact same time.

:class:`Symfony\\Component\\Scheduler\\Trigger\\PeriodicalTrigger`
    A trigger that uses a ``DateInterval`` to determine the next run date.

The :class:`Symfony\\Component\\Scheduler\\Trigger\\JitterTrigger` and
:class:`Symfony\\Component\\Scheduler\\Trigger\\ExcludeTimeTrigger` are decorators
and modify the behavior of the trigger they wrap. You can get the decorated
trigger as well as the decorators by calling the
:method:`Symfony\\Component\\Scheduler\\Trigger\\AbstractDecoratedTrigger::inner`
and :method:`Symfony\\Component\\Scheduler\\Trigger\\AbstractDecoratedTrigger::decorators`
methods::

    $trigger = new ExcludeTimeTrigger(new JitterTrigger(CronExpressionTrigger::fromSpec('#midnight', new MyMessage()));

    $trigger->inner(); // CronExpressionTrigger
    $trigger->decorators(); // [ExcludeTimeTrigger, JitterTrigger]

Most of them can be created via the :class:`Symfony\\Component\\Scheduler\\RecurringMessage`
class, as shown in the following examples.

Cron Expression Triggers
~~~~~~~~~~~~~~~~~~~~~~~~

Before using cron triggers, you have to install the following dependency:

.. code-block:: terminal

    $ composer require dragonmantank/cron-expression

Then, define the trigger date/time using the same syntax as the
`cron command-line utility`_::

    RecurringMessage::cron('* * * * *', new Message());

    // optionally you can define the timezone used by the cron expression
    RecurringMessage::cron('* * * * *', new Message(), new \DateTimeZone('Africa/Malabo'));

.. tip::

    Check out the `crontab.guru website`_ if you need help to construct/understand
    cron expressions.

You can also use some special values that represent common cron expressions:

* ``@yearly``, ``@annually`` - Run once a year, midnight, Jan. 1 - ``0 0 1 1 *``
* ``@monthly`` - Run once a month, midnight, first of month - ``0 0 1 * *``
* ``@weekly`` - Run once a week, midnight on Sun - ``0 0 * * 0``
* ``@daily``, ``@midnight`` - Run once a day, midnight - ``0 0 * * *``
* ``@hourly`` - Run once an hour, first minute - ``0 * * * *``

For example::

    RecurringMessage::cron('@daily', new Message());

.. tip::

    You can also define cron tasks using :ref:`the AsCronTask attribute <scheduler-attributes-cron-task>`.

Hashed Cron Expressions
.......................

If you have many triggers scheduled at same time (for example, at midnight, ``0 0 * * *``)
this will create a very long running list of schedules at that exact time.
This may cause an issue if a task has a memory leak.

You can add a hash symbol (``#``) in expressions to generate random values.
Athough the values are random, they are predictable and consistent because they
are generated based on the message. A message with string representation ``my task``
and a defined frequency of ``# # * * *`` will have an idempotent frequency
of ``56 20 * * *`` (every day at 8:56pm).

You can also use hash ranges (``#(x-y)``) to define the list of possible values
for that random part. For example, ``# #(0-7) * * *`` means daily, some time
between midnight and 7am. Using the ``#`` without a range creates a range of any
valid value for the field. ``# # # # #`` is short for ``#(0-59) #(0-23) #(1-28)
#(1-12) #(0-6)``.

You can also use some special values that represent common hashed cron expressions:

======================  ========================================================================
Alias                   Converts to
======================  ========================================================================
``#hourly``             ``# * * * *`` (at some minute every hour)
``#daily``              ``# # * * *`` (at some time every day)
``#weekly``             ``# # * * #`` (at some time every week)
``#weekly@midnight``    ``# #(0-2) * * #`` (at ``#midnight`` one day every week)
``#monthly``            ``# # # * *`` (at some time on some day, once per month)
``#monthly@midnight``   ``# #(0-2) # * *`` (at ``#midnight`` on some day, once per month)
``#annually``           ``# # # # *`` (at some time on some day, once per year)
``#annually@midnight``  ``# #(0-2) # # *``  (at ``#midnight`` on some day, once per year)
``#yearly``             ``# # # # *`` alias for ``#annually``
``#yearly@midnight``    ``# #(0-2) # # *`` alias for ``#annually@midnight``
``#midnight``           ``# #(0-2) * * *`` (at some time between midnight and 2:59am, every day)
======================  ========================================================================

For example::

    RecurringMessage::cron('#midnight', new Message());

.. note::

    The day of month range is ``1-28``, this is to account for February
    which has a minimum of 28 days.

Periodical Triggers
~~~~~~~~~~~~~~~~~~~

These triggers allows to configure the frequency using different data types
(``string``, ``integer``, ``DateInterval``). They also support the `relative formats`_
defined by PHP datetime functions::

    RecurringMessage::every('10 seconds', new Message());
    RecurringMessage::every('3 weeks', new Message());
    RecurringMessage::every('first Monday of next month', new Message());

    $from = new \DateTimeImmutable('13:47', new \DateTimeZone('Europe/Paris'));
    $until = '2023-06-12';
    RecurringMessage::every('first Monday of next month', new Message(), $from, $until);

.. tip::

    You can also define periodic tasks using :ref:`the AsPeriodicTask attribute <scheduler-attributes-periodic-task>`.

Custom Triggers
~~~~~~~~~~~~~~~

Custom triggers allow to configure any frequency dynamically. They are created
as services that implement :class:`Symfony\\Component\\Scheduler\\Trigger\\TriggerInterface`.

For example, if you want to send customer reports daily except for holiday periods::

    // src/Scheduler/Trigger/NewUserWelcomeEmailHandler.php
    namespace App\Scheduler\Trigger;

    class ExcludeHolidaysTrigger implements TriggerInterface
    {
        public function __construct(private TriggerInterface $inner)
        {
        }

        // use this method to give a nice displayable name to
        // identify your trigger (it eases debugging)
        public function __toString(): string
        {
            return $this->inner.' (except holidays)';
        }

        public function getNextRunDate(\DateTimeImmutable $run): ?\DateTimeImmutable
        {
            if (!$nextRun = $this->inner->getNextRunDate($run)) {
                return null;
            }

            // loop until you get the next run date that is not a holiday
            while (!$this->isHoliday($nextRun) {
                $nextRun = $this->inner->getNextRunDate($nextRun);
            }

            return $nextRun;
        }

        private function isHoliday(\DateTimeImmutable $timestamp): bool
        {
            // add some logic to determine if the given $timestamp is a holiday
            // return true if holiday, false otherwise
        }
    }

Then, define your recurring message::

    RecurringMessage::trigger(
        new ExcludeHolidaysTrigger(
            CronExpressionTrigger::fromSpec('@daily'),
        ),
        new SendDailySalesReports('...'),
    );

Finally, the recurring messages has to be attached to a schedule::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            return $this->schedule ??= (new Schedule())
                ->with(
                    RecurringMessage::trigger(
                        new ExcludeHolidaysTrigger(
                            CronExpressionTrigger::fromSpec('@daily'),
                        ),
                        new SendDailySalesReports()
                    ),
                    RecurringMessage::cron('3 8 * * 1', new CleanUpOldSalesReport())
                );
        }
    }

So, this ``RecurringMessage`` will encompass both the trigger, defining the
generation frequency of the message, and the message itself, the one to be
processed by a specific handler.

But what is interesting to know is that it also provides you with the ability to
generate your message(s) dynamically.

A Dynamic Vision for the Messages Generated
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This proves particularly useful when the message depends on data stored in
databases or third-party services.

Following the previous example of reports generation: they depend on customer requests.
Depending on the specific demands, any number of reports may need to be generated
at a defined frequency. For these dynamic scenarios, it gives you the capability
to dynamically define our message(s) instead of statically. This is achieved by
defining a :class:`Symfony\\Component\\Scheduler\\Trigger\\CallbackMessageProvider`.

Essentially, this means you can dynamically, at runtime, define your message(s)
through a callback that gets executed each time the scheduler transport
checks for messages to be generated::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            return $this->schedule ??= (new Schedule())
                ->with(
                    RecurringMessage::trigger(
                        new ExcludeHolidaysTrigger(
                            CronExpressionTrigger::fromSpec('@daily'),
                        ),
                    // instead of being static as in the previous example
                    new CallbackMessageProvider([$this, 'generateReports'], 'foo')),
                    RecurringMessage::cron(‘3 8 * * 1’, new CleanUpOldSalesReport())
                );
        }

        public function generateReports(MessageContext $context)
        {
            // ...
            yield new SendDailySalesReports();
            yield new ReportSomethingReportSomethingElse();
        }
    }

Exploring Alternatives for Crafting your Recurring Messages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There is also another way to build a ``RecurringMessage``, and this can be done
by adding one of these attributes to a service or a command:
:class:`Symfony\\Component\\Scheduler\\Attribute\\AsPeriodicTask` and
:class:`Symfony\\Component\\Scheduler\\Attribute\\AsCronTask`.

For both of these attributes, you have the ability to define the schedule to
use via the ``schedule``option. By default, the ``default`` named schedule will
be used. Also, by default, the ``__invoke`` method of your service will be called
but, it's also possible to specify the method to call via the ``method``option
and you can define arguments via ``arguments``option if necessary.

.. _scheduler-attributes-cron-task:

``AsCronTask`` Example
......................

This is the most basic way to define a cron trigger with this attribute::

    // src/Scheduler/Task/SendDailySalesReports.php
    namespace App\Scheduler\Task;

    use Symfony\Component\Scheduler\Attribute\AsCronTask;

    #[AsCronTask('0 0 * * *')]
    class SendDailySalesReports
    {
        public function __invoke()
        {
            // ...
        }
    }

The attribute takes more parameters to customize the trigger::

    // adds randomly up to 6 seconds to the trigger time to avoid load spikes
    #[AsCronTask('0 0 * * *', jitter: 6)]

    // defines the method name to call instead as well as the arguments to pass to it
    #[AsCronTask('0 0 * * *', method: 'sendEmail', arguments: ['email' => 'admin@example.com'])]

    // defines the timezone to use
    #[AsCronTask('0 0 * * *', timezone: 'Africa/Malabo')]

.. _scheduler-attributes-periodic-task:

``AsPeriodicTask`` Example
..........................

This is the most basic way to define a periodic trigger with this attribute::

    // src/Scheduler/Task/SendDailySalesReports.php
    namespace App\Scheduler\Task;

    use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

    #[AsPeriodicTask(frequency: '1 day', from: '2022-01-01', until: '2023-06-12')]
    class SendDailySalesReports
    {
        public function __invoke()
        {
            // ...
        }
    }

.. note::

    The ``from`` and ``until`` options are optional. If not defined, the task
    will be executed indefinitely.

The ``#[AsPeriodicTask]`` attribute takes many parameters to customize the trigger::

    // the frequency can be defined as an integer representing the number of seconds
    #[AsPeriodicTask(frequency: 86400)]

    // adds randomly up to 6 seconds to the trigger time to avoid load spikes
    #[AsPeriodicTask(frequency: '1 day', jitter: 6)]

    // defines the method name to call instead as well as the arguments to pass to it
    #[AsPeriodicTask(frequency: '1 day', method: 'sendEmail', arguments: ['email' => 'admin@symfony.com'])]
    class SendDailySalesReports
    {
        public function sendEmail(string $email): void
        {
            // ...
        }
    }

    // defines the timezone to use
    #[AsPeriodicTask(frequency: '1 day', timezone: 'Africa/Malabo')]

Managing Scheduled Messages
---------------------------

Modifying Scheduled Messages in Real-Time
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While planning a schedule in advance is beneficial, it is rare for a schedule to
remain static over time. After a certain period, some ``RecurringMessages`` may
become obsolete, while others may need to be integrated into the planning.

As a general practice, to alleviate a heavy workload, the recurring messages in
the schedules are stored in memory to avoid recalculation each time the scheduler
transport generates messages. However, this approach can have a flip side.

Following the same report generation example as above, the company might do some
promotions during specific periods (and they need to be communicated repetitively
throughout a given timeframe) or the deletion of old reports needs to be halted
under certain circumstances.

This is why the ``Scheduler`` incorporates a mechanism to dynamically modify the
schedule and consider all changes in real-time.

Strategies for Adding, Removing, and Modifying Entries within the Schedule
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The schedule provides you with the ability to :method:`Symfony\\Component\\Scheduler\Schedule::add`,
:method:`Symfony\\Component\\Scheduler\Schedule::remove`, or :method:`Symfony\\Component\\Scheduler\Schedule::clear`
all associated recurring messages, resulting in the reset and recalculation of
the in-memory stack of recurring messages.

For instance, for various reasons, if there's no need to generate a report, a
callback can be employed to conditionally skip generating of some or all reports.

However, if the intention is to completely remove a recurring message and its recurrence,
the :class:`Symfony\\Component\\Scheduler\Schedule` offers a :method:`Symfony\\Component\\Scheduler\Schedule::remove`
or a :method:`Symfony\\Component\\Scheduler\Schedule::removeById` method. This can
be particularly useful in your case, especially if you need to halt the generation
of the recurring message, which involves deleting old reports.

In your handler, you can check a condition and, if affirmative, access the
:class:`Symfony\\Component\\Scheduler\Schedule` and invoke this method::

    // src/Scheduler/SaleTaskProvider.php
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
                    $this->removeOldReports;
                );
        }

        // ...

        public function removeCleanUpMessage()
        {
            $this->getSchedule()->getSchedule()->remove($this->removeOldReports);
        }
    }

    // src/Scheduler/Handler/.php
    namespace App\Scheduler\Handler;

    #[AsMessageHandler]
    class CleanUpOldSalesReportHandler
    {
        public function __invoke(CleanUpOldSalesReport $cleanUpOldSalesReport): void
        {
            // do some work here...

            if ($isFinished) {
                $this->mySchedule->removeCleanUpMessage();
            }
        }
    }

Nevertheless, this system may not be the most suitable for all scenarios. Also,
the handler should ideally be designed to process the type of message it is
intended for, without making decisions about adding or removing a new recurring
message.

For instance, if, due to an external event, there is a need to add a recurrent
message aimed at deleting reports, it can be challenging to achieve within the
handler. This is because the handler will no longer be called or executed once
there are no more messages of that type.

However, the Scheduler also features an event system that is integrated into a
Symfony full-stack application by grafting onto Symfony Messenger events. These
events are dispatched through a listener, providing a convenient means to respond.

Managing Scheduled Messages via Events
--------------------------------------

A Strategic Event Handling
~~~~~~~~~~~~~~~~~~~~~~~~~~

The goal is to provide flexibility in deciding when to take action while
preserving decoupling. Three primary event types have been introduced types

* ``PRE_RUN_EVENT``
* ``POST_RUN_EVENT``
* ``FAILURE_EVENT``

Access to the schedule is a crucial feature, allowing effortless addition or
removal of message types. Additionally, it will be possible to access the
currently processed message and its message context.

In consideration of our scenario, you can listen to the ``PRE_RUN_EVENT`` and
check if a certain condition is met. For instance, you might decide to add a
recurring message for cleaning old reports again, with the same or different
configurations, or add any other recurring message(s).

If you had chosen to handle the deletion of the recurring message, you could
have done so in a listener for this event. Importantly, it reveals a specific
feature :method:`Symfony\\Component\\Scheduler\\Event\\PreRunEvent::shouldCancel`
that allows you to prevent the message of the deleted recurring message from
being transferred and processed by its handler::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $this->removeOldReports = RecurringMessage::cron('3 8 * * 1', new CleanUpOldSalesReport());

            return $this->schedule ??= (new Schedule())
                ->with(
                    // ...
                );
                ->before(function(PreRunEvent $event) {
                    $message = $event->getMessage();
                    $messageContext = $event->getMessageContext();

                    // can access the schedule
                    $schedule = $event->getSchedule()->getSchedule();

                    // can target directly the RecurringMessage being processed
                    $schedule->removeById($messageContext->id);

                    // allow to call the ShouldCancel() and avoid the message to be handled
                        $event->shouldCancel(true);
                }
                ->after(function(PostRunEvent $event) {
                    // Do what you want
                }
                ->onFailure(function(FailureEvent $event) {
                    // Do what you want
                }
        }
    }

Scheduler Events
~~~~~~~~~~~~~~~~

PreRunEvent
...........

**Event Class**: :class:`Symfony\\Component\\Scheduler\\Event\\PreRunEvent`

``PreRunEvent`` allows to modify the :class:`Symfony\\Component\\Scheduler\\Schedule`
or cancel a message before it's consumed::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Scheduler\Event\PreRunEvent;

    public function onMessage(PreRunEvent $event): void
    {
        $schedule = $event->getSchedule();
        $context = $event->getMessageContext();
        $message = $event->getMessage();

        // do something with the schedule, context or message

        // and/or cancel message
        $event->shouldCancel(true);
    }

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Scheduler\Event\PreRunEvent"

PostRunEvent
............

**Event Class**: :class:`Symfony\\Component\\Scheduler\\Event\\PostRunEvent`

``PostRunEvent`` allows to modify the :class:`Symfony\\Component\\Scheduler\\Schedule`
after a message is consumed::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Scheduler\Event\PostRunEvent;

    public function onMessage(PostRunEvent $event): void
    {
        $schedule = $event->getSchedule();
        $context = $event->getMessageContext();
        $message = $event->getMessage();

        // do something with the schedule, context or message
    }

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Scheduler\Event\PostRunEvent"

FailureEvent
............

**Event Class**: :class:`Symfony\\Component\\Scheduler\\Event\\FailureEvent`

``FailureEvent`` allows to modify the :class:`Symfony\\Component\\Scheduler\\Schedule`
when a message consumption throws an exception::

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Scheduler\Event\FailureEvent;

    public function onMessage(FailureEvent $event): void
    {
        $schedule = $event->getSchedule();
        $context = $event->getMessageContext();
        $message = $event->getMessage();

        $error = $event->getError();

        // do something with the schedule, context, message or error (logging, ...)

        // and/or ignore failure event
        $event->shouldIgnore(true);
    }

Execute this command to find out which listeners are registered for this event
and their priorities:

.. code-block:: terminal

    $ php bin/console debug:event-dispatcher "Symfony\Component\Scheduler\Event\FailureEvent"

.. _consuming-messages-running-the-worker:

Consuming Messages
------------------

The Scheduler component offers two ways to consume messages, depending on your
needs: using the ``messenger:consume`` command or creating a worker programmatically.
The first solution is the recommended one when using the Scheduler component in
the context of a full stack Symfony application, the second one is more suitable
when using the Scheduler component as a standalone component.

Running a Worker
~~~~~~~~~~~~~~~~

After defining and attaching your recurring messages to a schedule, you'll need
a mechanism to generate and consume the messages according to their defined frequencies.
To do that, the Scheduler component uses the ``messenger:consume`` command from
the Messenger component:

.. code-block:: terminal

    $ php bin/console messenger:consume scheduler_nameofyourschedule

    # use -vv if you need details about what's happening
    $ php bin/console messenger:consume scheduler_nameofyourschedule -vv

.. image:: /_images/components/scheduler/generate_consume.png
    :alt: Symfony Scheduler - generate and consume

Creating a Consumer Programmatically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An alternative to the previous solution is to create and call a worker that
will consume the messages. The component comes with a ready-to-use worker
named :class:`Symfony\\Component\\Scheduler\\Scheduler` that you can use in your
code::

    use Symfony\Component\Scheduler\Scheduler;

    $schedule = (new Schedule())
        ->with(
            RecurringMessage::trigger(
                new ExcludeHolidaysTrigger(
                    CronExpressionTrigger::fromSpec('@daily'),
                ),
                new SendDailySalesReports()
            ),
        );

    $scheduler = new Scheduler(handlers: [
        SendDailySalesReports::class => new SendDailySalesReportsHandler(),
        // add more handlers if you have more message types
    ], schedules: [
        $schedule,
        // the scheduler can take as many schedules as you need
    ]);

    // finally, run the scheduler once it's ready
    $scheduler->run();

.. note::

    The :class:`Symfony\\Component\\Scheduler\\Scheduler` may be used
    when using the Scheduler component as a standalone component. If
    you are using it in the framework context, it is highly recommended to
    use the ``messenger:consume`` command as explained in the previous
    section.

Debugging the Schedule
----------------------

The ``debug:scheduler`` command provides a list of schedules along with their
recurring messages. You can narrow down the list to a specific schedule:

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

    # you can also specify a date to use for the next run date:
    $ php bin/console debug:scheduler --date=2025-10-18

    # you can also specify a date to use for the next run date for a schedule:
    $ php bin/console debug:scheduler name_of_schedule --date=2025-10-18

    # use the --all option to also display the terminated recurring messages
    $ php bin/console debug:scheduler --all

Efficient management with Symfony Scheduler
-------------------------------------------

When a worker is restarted or undergoes shutdown for a period, the Scheduler transport won't be able to generate the messages (because they are created on-the-fly by the scheduler transport).
This implies that any messages scheduled to be sent during the worker's inactive period are not sent, and the Scheduler will lose track of the last processed message.
Upon restart, it will recalculate the messages to be generated from that point onward.

To illustrate, consider a recurring message set to be sent every 3 days.
If a worker is restarted on day 2, the message will be sent 3 days from the restart, on day 5.

While this behavior may not necessarily pose a problem, there is a possibility that it may not align with what you are seeking.

That's why the scheduler allows to remember the last execution date of a message
via the ``stateful`` option (and the :doc:`Cache component </components/cache>`).
This allows the system to retain the state of the schedule, ensuring that when a worker is restarted, it resumes from the point it left off.::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $this->removeOldReports = RecurringMessage::cron('3 8 * * 1', new CleanUpOldSalesReport());

            return $this->schedule ??= (new Schedule())
                ->with(
                    // ...
                )
                ->stateful($this->cache)
        }
    }

To scale your schedules more effectively, you can use multiple workers. In such
cases, a good practice is to add a :doc:`lock </components/lock>` to prevent the
same task more than once::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $this->removeOldReports = RecurringMessage::cron('3 8 * * 1', new CleanUpOldSalesReport());

            return $this->schedule ??= (new Schedule())
                ->with(
                    // ...
                )
                ->lock($this->lockFactory->createLock('my-lock')
        }
    }

.. tip::

    The processing time of a message matters. If it takes a long time, all subsequent
    message processing may be delayed. So, it's a good practice to anticipate this
    and plan for frequencies greater than the processing time of a message.

Additionally, for better scaling of your schedules, you have the option to wrap
your message in a :class:`Symfony\\Component\\Messenger\\Message\\RedispatchMessage`.
This allows you to specify a transport on which your message will be redispatched
before being further redispatched to its corresponding handler::

    // src/Scheduler/SaleTaskProvider.php
    namespace App\Scheduler;

    #[AsSchedule('uptoyou')]
    class SaleTaskProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            return $this->schedule ??= (new Schedule())
                ->with(
                    RecurringMessage::every('5 seconds', new RedispatchMessage(new Message(), 'async'))
                );
        }
    }

When using the ``RedispatchMessage``, Symfony will attach a
:class:`Symfony\\Component\\Messenger\\Stamp\\ScheduledStamp` to the message,
helping you identify those messages when needed.

.. _`Memoizing`: https://en.wikipedia.org/wiki/Memoization
.. _`cron command-line utility`: https://en.wikipedia.org/wiki/Cron
.. _`crontab.guru website`: https://crontab.guru/
.. _`relative formats`: https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
