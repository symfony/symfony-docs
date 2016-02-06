Securely Comparing Strings and Generating Random Values
=======================================================

The Symfony Security component comes with a collection of nice utilities
related to security. These utilities are used by Symfony, but you should
also use them if you want to solve the problem they address.

Comparing Strings
~~~~~~~~~~~~~~~~~

The time it takes to compare two strings depends on their differences. This
can be used by an attacker when the two strings represent a password for
instance; it is known as a `Timing attack`_.

Internally, when comparing two passwords, Symfony uses a constant-time
algorithm; you can use the same strategy in your own code thanks to the
:class:`Symfony\\Component\\Security\\Core\\Util\\StringUtils` class::

    use Symfony\Component\Security\Core\Util\StringUtils;

    // is some known string (e.g. password) equal to some user input?
    $bool = StringUtils::equals($knownString, $userInput);

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
    URL. The solution is to encode or hash the value returned by
    ``random_bytes()`` (to do that, you can use a simple ``base64_encode()``
    PHP function).

Generating a Secure Random Number
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to generate a cryptographically secure random integer, you should
use the :phpfunction:`random_int` function::

    $random = random_int(1, 10);

.. note::

    PHP 7 and up provide the ``random_bytes()`` and ``random_int()`` functions
    natively, for older versions of PHP a polyfill is provided by the
    `Symfony Polyfill Component`_ and the `paragonie/random_compat package`_.

.. _`Timing attack`: https://en.wikipedia.org/wiki/Timing_attack
.. _`Symfony Polyfill Component`: https://github.com/symfony/polyfill
.. _`paragonie/random_compat package`: https://github.com/paragonie/random_compat
