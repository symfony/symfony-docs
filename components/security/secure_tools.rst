Securely Comparing Strings and Generating Random Numbers
========================================================

The Symfony Security component comes with a collection of nice utilities
related to security. These utilities are used by Symfony, but you should
also use them if you want to solve the problem they address.

Comparing Strings of the same length
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The time it takes to compare two strings depends on their differences. This
can be used by an attacker when the two strings represent a hash of a password
for instance; it is known as a `Timing attack`_.

Internally, when comparing two passwords, Symfony uses a constant-time
algorithm; you can use the same strategy in your own code thanks to the
:class:`Symfony\\Component\\Security\\Core\\Util\\StringUtils` class::

    use Symfony\Component\Security\Core\Util\StringUtils;

    // is some known string (e.g. password hash) equal to another string (e.g. hash of some user input)?
    $bool = StringUtils::equals($knownString, $anotherString);

.. caution::

    To avoid timing attacks, both strings must be of the same length, the
    known string must be the first argument and the string based on the user-entered
    content the second.
    This method is more reliable with PHP 5.6 and superior because it internally uses
    the native `hash_equals`_ PHP function.

Generating a Secure random Number
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Whenever you need to generate a secure random number, you are highly
encouraged to use the Symfony
:class:`Symfony\\Component\\Security\\Core\\Util\\SecureRandom` class::

    use Symfony\Component\Security\Core\Util\SecureRandom;

    $generator = new SecureRandom();
    $random = $generator->nextBytes(10);

The
:method:`Symfony\\Component\\Security\\Core\\Util\\SecureRandom::nextBytes`
method returns a random string composed of the number of characters passed as
an argument (10 in the above example).

The SecureRandom class works better when OpenSSL is installed. But when it's
not available, it falls back to an internal algorithm, which needs a seed file
to work correctly. Just pass a file name to enable it::

    use Symfony\Component\Security\Core\Util\SecureRandom;

    $generator = new SecureRandom('/some/path/to/store/the/seed.txt');
    $random = $generator->nextBytes(10);

.. note::

    If you're using the Symfony Framework, you can access a secure random
    instance directly from the container: its name is ``security.secure_random``.

.. _`Timing attack`: http://en.wikipedia.org/wiki/Timing_attack
   _`hash_equals`: http://php.net/manual/en/function.hash-equals.php
