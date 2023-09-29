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
:class:`Symfony\\Component\\Clock\\MonotonicClock`
    Relies on ``hrtime()`` and provides a high resolution, monotonic clock,
    when you need a precise stopwatch.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/clock

.. include:: /components/require_autoload.rst.inc

.. _clock_usage:

Usage
-----

The :class:`Symfony\\Component\\Clock\\Clock` class returns the current time and
allows to use any `PSR-20`_ compatible implementation as a global clock in your
application::

    use Symfony\Component\Clock\Clock;
    use Symfony\Component\Clock\MockClock;

    // by default, Clock uses the NativeClock implementation, but you can change
    // this by setting any other implementation
    Clock::set(new MockClock());

    // Then, you can get the clock instance
    $clock = Clock::get();

    // Additionally, you can set a timezone
    $clock->withTimeZone('Europe/Paris');

    // From here, you can get the current time
    $now = $clock->now();

    // And sleep for any number of seconds
    $clock->sleep(2.5);

The Clock component also provides the ``now()`` function::

    use function Symfony\Component\Clock\now;

    // Get the current time as a DatePoint instance
    $now = now();

The ``now()`` function takes an optional ``modifier`` argument
which will be applied to the current time::

    $later = now('+3 hours');

    $yesterday = now('-1 day');

You can use any string `accepted by the DateTime constructor`_.

Later on this page you can learn how to use this clock in your services and tests.
When using the Clock component, you manipulate
:class:`Symfony\\Component\\Clock\\DatePoint` instances. You can learn more
about it in :ref:`the dedicated section <clock_date-point>`.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\Clock\\Clock` class and ``now()`` function
    were introduced in Symfony 6.3.

.. versionadded:: 6.4

    The ``modifier`` argument of the ``now()`` function was introduced in
    Symfony 6.4.

Available Clocks Implementations
--------------------------------

The Clock component provides some ready-to-use implementations of the
:class:`Symfony\\Component\\Clock\\ClockInterface`, which you can use
as global clocks in your application depending on your needs.

NativeClock
~~~~~~~~~~~

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
            private ClockInterface $clock
        ) {}

        public function isExpired(DateTimeInterface $validUntil): bool
        {
            return $this->clock->now() > $validUntil;
        }
    }

MockClock
~~~~~~~~~

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
~~~~~~~~~~~~~~~

The ``MonotonicClock`` allows you to implement a precise stopwatch; depending on
the system up to nanosecond precision. It can be used to measure the elapsed
time between two calls without being affected by inconsistencies sometimes introduced
by the system clock, e.g. by updating it. Instead, it consistently increases time,
making it especially useful for measuring performance.

.. _clock_use-inside-a-service:

Using a Clock inside a Service
------------------------------

Using the Clock component in your services to retrieve the current time makes
them easier to test. For example, by using the ``MockClock`` implementation as
the default one during tests, you will have full control to set the "current time"
to any arbitrary date/time.

In order to use this component in your services, make their classes use the
:class:`Symfony\\Component\\Clock\\ClockAwareTrait`. Thanks to
:ref:`service autoconfiguration <services-autoconfigure>`, the ``setClock()`` method
of the trait will automatically be called by the service container.

You can now call the ``$this->now()`` method to get the current time::

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

.. _clock_date-point:

The ``DatePoint`` Class
-----------------------

The Clock component uses a special :class:`Symfony\\Component\\Clock\\DatePoint`
class. This is a small wrapper on top of PHP's :phpclass:`DateTimeImmutable`.
You can use it seamlessly everywhere a :phpclass:`DateTimeImmutable` or
:phpclass:`DateTimeInterface` is expected. The ``DatePoint`` object fetches the
date and time from the :class:`Symfony\\Component\\Clock\\Clock` class. This means
that if you did any changes to the clock as stated in the
:ref:`usage section <clock_usage>`, it will be reflected when creating a new
``DatePoint``. You can also create a new ``DatePoint`` instance directly, for
instance when using it as a default value::

    use Symfony\Component\Clock\DatePoint;

    class Post
    {
        public function __construct(
            // ...
            private \DateTimeImmutable $createdAt = new DatePoint(),
        ) {
        }
    }

The constructor also allows setting a timezone or custom referenced date::

    // you can specify a timezone
    $withTimezone = new DatePoint(timezone: new \DateTimezone('UTC'));

    // you can also create a DatePoint from a reference date
    $referenceDate = new \DateTimeImmutable();
    $relativeDate = new DatePoint('+1month', reference: $referenceDate);

.. note::
    In addition ``DatePoint`` offers stricter return types and provides consistent
    error handling across versions of PHP, thanks to polyfilling `PHP 8.3's behavior`_
    on the topic.

.. versionadded:: 6.4

    The :class:`Symfony\\Component\\Clock\\DatePoint` class was introduced
    in Symfony 6.4.

.. _clock_writing-tests:

Writing Time-Sensitive Tests
----------------------------

The Clock component provides another trait, called :class:`Symfony\\Component\\Clock\\Test\\ClockSensitiveTrait`,
to help you write time-sensitive tests. This trait provides methods to freeze
time and restore the global clock after each test.

Use the ``ClockSensitiveTrait::mockTime()`` method to interact with the mocked
clock in your tests. This method accepts different types as its only argument:

* A string, which can be a date to set the clock at (e.g. ``1996-07-01``) or an
  interval to modify the clock (e.g. ``+2 days``);
* A ``DateTimeImmutable`` to set the clock at;
* A boolean, to freeze or restore the global clock.

Let's say you want to test the method ``MonthSensitive::isWinterMonth()`` of the
above example. This is how you can write that test::

    namespace App\Tests\TimeUtils;

    use App\TimeUtils\MonthSensitive;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\Clock\Test\ClockSensitiveTrait;

    class MonthSensitiveTest extends TestCase
    {
        use ClockSensitiveTrait;

        public function testIsWinterMonth(): void
        {
            $clock = static::mockTime(new \DateTimeImmutable('2022-03-02'));

            $monthSensitive = new MonthSensitive();
            $monthSensitive->setClock($clock);

            $this->assertTrue($monthSensitive->isWinterMonth());
        }

        public function testIsNotWinterMonth(): void
        {
            $clock = static::mockTime(new \DateTimeImmutable('2023-06-02'));

            $monthSensitive = new MonthSensitive();
            $monthSensitive->setClock($clock);

            $this->assertFalse($monthSensitive->isWinterMonth());
        }
    }

This test will behave the same no matter which time of the year you run it.
By combining the :class:`Symfony\\Component\\Clock\\ClockAwareTrait` and
:class:`Symfony\\Component\\Clock\\Test\\ClockSensitiveTrait`, you have full
control on your time-sensitive code's behavior.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\Clock\\Test\\ClockSensitiveTrait` was introduced in Symfony 6.3.

Exceptions Management
---------------------

The Clock component takes full advantage of some `PHP DateTime exceptions`_.
If you pass an invalid string to the clock (e.g. when creating a clock or
modifying a ``MockClock``) you'll get a ``DateMalformedStringException``. If you
pass an invalid timezone, you'll get a ``DateInvalidTimeZoneException``::

    $userInput = 'invalid timezone';

    try {
        $clock = Clock::get()->withTimeZone($userInput);
    } catch (\DateInvalidTimeZoneException $exception) {
        // ...
    }

These exceptions are available starting from PHP 8.3. However, thanks to the
`symfony/polyfill-php83`_ dependency required by the Clock component, you can
use them even if your project doesn't use PHP 8.3 yet.

.. versionadded:: 6.4

    The support for ``DateMalformedStringException`` and
    ``DateInvalidTimeZoneException`` was introduced in Symfony 6.4.

.. _`PSR-20`: https://www.php-fig.org/psr/psr-20/
.. _`accepted by the DateTime constructor`: https://www.php.net/manual/en/datetime.formats.php
.. _`PHP DateTime exceptions`: https://wiki.php.net/rfc/datetime-exceptions
.. _`symfony/polyfill-php83`: https://github.com/symfony/polyfill-php83
.. _`PHP 8.3's behavior`: https://wiki.php.net/rfc/datetime-exceptions
