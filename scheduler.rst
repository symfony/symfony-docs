Scheduler
=========

.. versionadded:: 6.3

    The Scheduler component was introduced in Symfony 6.3

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
    time that it's added/subtracted to the original triggering date/time. This
    allows to distribute the load of the scheduled tasks instead of running them
    all at the exact same time.

:class:`Symfony\\Component\\Scheduler\\Trigger\\PeriodicalTrigger`
    A trigger that uses a ``DateInterval`` to determine the next run date.

Most of them can be created via the :class:`Symfony\\Component\\Scheduler\\RecurringMessage`
class, as shown in the following examples.

Cron Expression Triggers
~~~~~~~~~~~~~~~~~~~~~~~~

Before using cron triggers, you have to install the following dependency:

.. code-block:: terminal

    composer require dragonmantank/cron-expression

Then, define the trigger date/time using the same syntax as the
`cron command-line utility`_::

    RecurringMessage::cron('* * * * *', new Message());

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

Consuming Messages (Running the Worker)
---------------------------------------

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

Efficient management with Symfony Scheduler
-------------------------------------------

When a worker is restarted or undergoes shutdown for a period, the Scheduler
transport won't be able to generate the messages (because they are created
on-the-fly by the scheduler transport). This implies that any messages
scheduled to be sent during the worker's inactive period are not sent, and the
Scheduler will lose track of the last processed message. Upon restart, it will
recalculate the messages to be generated from that point onward.

To illustrate, consider a recurring message set to be sent every 3 days. If a
worker is restarted on day 2, the message will be sent 3 days from the restart,
on day 5.

While this behavior may not necessarily pose a problem, there is a possibility
that it may not align with what you are seeking.

That's why the scheduler allows to remember the last execution date of a message
via the ``stateful`` option (and the :doc:`Cache component </components/cache>`).
This allows the system to retain the state of the schedule, ensuring that when
a worker is restarted, it resumes from the point it left off::

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
                    RecurringMessage::every('5 seconds'),
                    new RedispatchMessage(new Message(), 'async')
                );
        }
    }

.. _`Memoizing`: https://en.wikipedia.org/wiki/Memoization
.. _`cron command-line utility`: https://en.wikipedia.org/wiki/Cron
.. _`crontab.guru website`: https://crontab.guru/
.. _`relative formats`: https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
