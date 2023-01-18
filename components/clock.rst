.. index::
   single: Clock
   single: Components; Clock

The Clock Component
===================

.. versionadded:: 6.2

    The Clock component was introduced in Symfony 6.2

The Clock component decouples applications from the system clock. This allows
you to fix time to improve testability of time-sensitive logic.

The component provides a ``ClockInterface`` with the following implementations
for different use cases:

:class:`Symfony\\Component\\Clock\\NativeClock`
    Provides a way to interact with the system clock, this is the same as doing
    ``new \DateTimeImmutable()``.
:class:`Symfony\\Component\\Clock\\MockClock`
    Commonly used in tests as a replacement for the ``NativeClock`` to be able
    to freeze and change the current time using either ``sleep()`` or ``modify()``.
:class:`Symfony\\Component\\Clock\\MonotonicClock``
    Relies on ``hrtime()`` and provides a high resolution, monotonic clock,
    when you need a precise stopwatch.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/clock

.. include:: /components/require_autoload.rst.inc

NativeClock
-----------

A clock service replaces creating a new ``DateTime`` or
``DateTimeImmutable`` object for the current time. Instead, you inject the
``ClockInterface`` and call ``now()``. By default, your application will likely
use a ``NativeClock``, which always returns the current system time. In tests it is replaced with a ``MockClock``.

The following example introduces a service utilizing the Clock component to
determine the current time::

    use Symfony\Component\Clock\ClockInterface;

    class ExpirationChecker
    {
        public function __construct(
            private readonly ClockInterface $clock
        ) {}

        public function isExpired(DateTimeInterface $validUntil): bool
        {
            return $this->clock->now() > $validUntil;
        }
    }

MockClock
---------

The ``MockClock`` is instantiated with a time and does not move forward on its own. The time is
fixed until ``sleep()`` or ``modify()`` are called. This gives you full control over what your code
assumes is the current time.

When writing a test for this service, you can check both cases where something
is expired or not, by modifying the clock's time::

    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Clock\MockClock;

    class ExpirationCheckerTest extends TestCase
    {
        public function testIsExpired(): void
        {
            $clock = new MockClock('2022-11-16 15:20:00');
            $expirationChecker = new ExpirationChecker($clock);
            $validUntil = new DateTimeImmutable('2022-11-16 15:25:00');

            // $validUntil is in the future, so it is not expired
            static::assertFalse($expirationChecker->isExpired($validUntil));

            // Clock sleeps for 10 minutes, so now is '2022-11-16 15:30:00'
            $clock->sleep(600); // Instantly changes time as if we waited for 10 minutes (600 seconds)

            // modify the clock, accepts all formats supported by DateTimeImmutable::modify()
            static::assertTrue($expirationChecker->isExpired($validUntil));

            $clock->modify('2022-11-16 15:00:00');

            // $validUntil is in the future again, so it is no longer expired
            static::assertFalse($expirationChecker->isExpired($validUntil));
        }
    }

Monotonic Clock
---------------

The ``MonotonicClock`` allows you to implement a precise stopwatch; depending on
the system up to nanosecond precision. It can be used to measure the elapsed
time between two calls without being affected by inconsistencies sometimes introduced
by the system clock, e.g. by updating it. Instead, it consistently increases time,
making it especially useful for measuring performance.

Using a Clock inside a Service
------------------------------

Using the Clock component in your services to retrieve the current time makes
them easier to test. For example, by using the ``MockClock`` implementation as
the default one during tests, you will have full control to set the "current time"
to any arbitrary date/time.

In order to use this component in your services, make their classes use the
:class:`Symfony\\Component\\Clock\\ClockAwareTrait` and add a ``ClockInterface``
typed-property ``$clock`` to their constructors.

If your application uses :ref:`service autoconfiguration <services-autoconfigure>`,
your services can now call the ``$this->now()`` method to get the current time::

    namespace App\TimeUtils;

    use Symfony\Component\Clock\ClockAwareTrait;

    class MonthSensitive
    {
        use ClockAwareTrait;

        public function isWinterMonth(): bool
        {
            $now = $this->now();

            return match ($now->format('F')) {
                'December', 'January', 'February', 'March' => true,
                default => false,
            };
        }
    }

Thanks to the ``ClockAwareTrait``, and by using the ``MockClock`` implementation,
you can set the current time arbitrarily without having to change your service code.
This will help you test every case of your method without the need of actually
being in a month or another.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\Clock\\ClockAwareTrait` was introduced in Symfony 6.3.
