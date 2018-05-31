Securely Generating Random Values
=================================

The Symfony Security component comes with a collection of nice utilities
related to security. These utilities are used by Symfony, but you should
also use them if you want to solve the problem they address.

.. note::

    The functions described in this article were introduced in PHP 5.6 or 7.
    For older PHP versions, a polyfill is provided by the
    `Symfony Polyfill Component`_.

Comparing Strings
~~~~~~~~~~~~~~~~~

The time it takes to compare two strings depends on their differences. This
can be used by an attacker when the two strings represent a password for
instance; it is known as a `Timing attack`_.

When comparing two passwords, you should use the :phpfunction:`hash_equals`
function::

    if (hash_equals($knownString, $userInput)) {
        // ...
    }

Generating a Secure Random String
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Whenever you need to generate a secure random string, you are highly
encouraged to use the :phpfunction:`random_bytes` function::

    $random = random_bytes(10);

The function returns a random string, suitable for cryptographic use, of
the number bytes passed as an argument (10 in the above example).

.. tip::

    The ``random_bytes()`` function returns a binary string which may contain
    the ``\0`` character. This can cause trouble in several common scenarios,
    such as storing this value in a database or including it as part of the
    URL. The solution is to hash the value returned by ``random_bytes()`` with
    a hashing function such as :phpfunction:`md5` or :phpfunction:`sha1`.

Generating a Secure Random Number
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to generate a cryptographically secure random integer, you should
use the :phpfunction:`random_int` function::

    $random = random_int(1, 10);

.. _`Timing attack`: https://en.wikipedia.org/wiki/Timing_attack
.. _`Symfony Polyfill Component`: https://github.com/symfony/polyfill
