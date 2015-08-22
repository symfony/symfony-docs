Securely Comparing Strings and Generating Random Numbers
========================================================

The Symfony Security component comes with a collection of nice utilities
related to security. These utilities are used by Symfony, but you should
also use them if you want to solve the problem they address.

Comparing Strings
~~~~~~~~~~~~~~~~~

The time it takes to compare two strings depends on their differences. This
can be used by an attacker when the two strings represent a hash of a password
for instance; it is known as a `Timing attack`_.

Internally, when comparing two passwords, Symfony uses a constant-time
algorithm; you can use the same strategy in your own code thanks to the
:class:`Symfony\\Component\\Security\\Core\\Util\\StringUtils` class::

    use Symfony\Component\Security\Core\Util\StringUtils;

    // is some known string (e.g. password hash) equal to another string
    // (e.g. hash ofsome user input)?
    $bool = StringUtils::equals($knownString, $anotherString);

.. caution::

    Both arguments must be of the same length to be compared successfully. When
    arguments of differing length are supplied, `false` can be returned immediately and
    the length of the known string may be leaked in case of a timing attack.
    The known string must be the first argument and the string based on the user-entered
    content the second.
    This method is more reliable with PHP 5.6 and superior because it internally uses
    the native :phpfunction:`hash_equals <hash_equals>` PHP function.

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
    $hashedRandom = md5($random); // see tip below

.. note::

    If you're using the Symfony Framework, you can get a secure random number
    generator via the ``security.secure_random`` service.

.. tip::

    The ``nextBytes()`` method returns a binary string which may contain the
    ``\0`` character. This can cause trouble in several common scenarios, such
    as storing this value in a database or including it as part of the URL. The
    solution is to hash the value returned by ``nextBytes()`` (to do that, you
    can use a simple ``md5()`` PHP function).

.. _`Timing attack`: http://en.wikipedia.org/wiki/Timing_attack
