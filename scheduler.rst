Scheduler
=========

.. versionadded:: 6.3

    The Scheduler component was introduced in Symfony 6.3 and is marked
    as experimental.

The Scheduler component provides a way to schedule periodical tasks or handling
recurring messages without using an external tool.

The component shares somes keys concepts with :ref:`Messenger <messenger>`.

Installation
------------

You can install the Scheduler component with:

.. code-block:: terminal

    $ composer require symfony/scheduler

.. include:: /components/require_autoload.rst.inc

.. _register-schedule-provider:

Register a Schedule provider
----------------------------

A Schedule provider is a class defining your schedule : which messages should 
be processed depending a trigger you choose::

    namespace App\Schedule;

    use App\Message\EndofTrialMessage;
    use Symfony\Component\Scheduler\Attribute\AsSchedule;
    use Symfony\Component\Scheduler\RecurringMessage;
    use Symfony\Component\Scheduler\Schedule;
    use Symfony\Component\Scheduler\ScheduleProviderInterface;

    #[AsSchedule('trial')]
    final class EndOfTrialScheduleProvider implements ScheduleProviderInterface
    {
        public function getSchedule(string $id): Schedule
        {
            return (new Schedule())
                ->add(new RecurringMessage::every('1 week', new EndofTrialMessage()))
            ;
        }
    }

Run the Messenger consumer
--------------------------

Scheduler uses the same :ref:`Messenger worker <messenger-worker>` to consume
messages dispatched by your schedule class.

You can do this with the ``messenger:consume`` command:

.. code-block:: terminal

    $ php bin/console messenger:consume scheduler_trial

Trigger your schedule
---------------------

Symfony provides by default two triggers for your schedules : `every` and
`cron`. By extending the provided TriggerInterface you can also create
your own trigger for your app.

Every
~~~~~

This trigger allows you to define you schedule with frequency as text, using
intervals or relative date format allowed by PHP::

    RecurringMessage::every('10 seconds', $msg);
    RecurringMessage::every('1 day', $msg);
    RecurringMessage::every('2 weeks', $msg);

    RecurringMessage::every('first day of month', $msg);
    RecurringMessage::every('next tuesday', $msg);

It is possible to specify an exact hour to trigger the schedule with `from`
option::

    RecurringMessage::every('first day of month', $msg, from: '13:47');

    // using timezone
    RecurringMessage::every('first day of month', $msg, from: '13:47+0400');

    // using a DateTimeImmutable object
    $from = new \DateTimeImmutable('13:47', new \DateTimeZone('Europe/Paris'));
    RecurringMessage::every('first day of month', $msg, from: $from);

If your schedule do not need to run indefinitely, you can use `until` option
to let the Component know when the trigger should stop::

    RecurringMessage::every('monday', $msg, until: '2023-06-12');

Cron
~~~~

This trigger can be configured following same rules as the eponymous Unix
utility::

    // trigger every hour
    RecurringMessage::cron('0 * * * *', $msg);

    // trigger every monday at 12:00
    RecurringMessage::cron('0 12 * * 1', $msg);

.. note::

    The minimal interval allowed with cron is 1 minute.

Define your own trigger
~~~~~~~~~~~~~~~~~~~~~~~

By implementing the TriggerInterface your can create your own trigger using
``getNextRunDate`` method::

    final class CustomTrigger implements TriggerInterface
    {
        public function getNextRunDate(\DateTimeImmutable $run): ?\DateTimeImmutable
        {
            // your logic
        }
    }
