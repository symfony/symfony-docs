Securely Comparing Strings and Generating Random Numbers
========================================================

.. versionadded:: 2.2
    The ``StringUtils`` and ``SecureRandom`` classes were introduced in Symfony
    2.2

The Symfony Security component comes with a collection of nice utilities related
to security. These utilities are used by Symfony, but you should also use
them if you want to solve the problem they address.

Comparing Strings
~~~~~~~~~~~~~~~~~

The time it takes to compare two strings depends on their differences. This
can be used by an attacker when the two strings represent a password for
instance; it is known as a `Timing attack`_.

Internally, when comparing two passwords, Symfony uses a constant-time
algorithm; you can use the same strategy in your own code thanks to the
:class:`Symfony\\Component\\Security\\Core\\Util\\StringUtils` class::

    use Symfony\Component\Security\Core\Util\StringUtils;

    // is password1 equals to password2?
    $bool = StringUtils::equals($password1, $password2);

Generating a secure random Number
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Whenever you need to generate a secure random number, you are highly
encouraged to use the Symfony
:class:`Symfony\\Component\\Security\\Core\\Util\\SecureRandom` class::

    use Symfony\Component\Security\Core\Util\SecureRandom;

    $generator = new SecureRandom();
    $random = $generator->nextBytes(10);

The
:method:`Symfony\\Component\\Security\\Core\\Util\\SecureRandom::nextBytes`
methods returns a random string composed of the number of characters passed as
an argument (10 in the above example).

The SecureRandom class works better when OpenSSL is installed but when it's
not available, it falls back to an internal algorithm, which needs a seed file
to work correctly. Just pass a file name to enable it::

    $generator = new SecureRandom('/some/path/to/store/the/seed.txt');
    $random = $generator->nextBytes(10);

.. note::

    If you're using the Symfony Framework, you can access a secure random
    instance directly from the container: its name is ``security.secure_random``.

.. _`Timing attack`: http://en.wikipedia.org/wiki/Timing_attack