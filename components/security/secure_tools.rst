Securely Generating Random Numbers
==================================

The Symfony Security component comes with a collection of nice utilities
related to security. These utilities are used by Symfony, but you should
also use them if you want to solve the problem they address.

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
