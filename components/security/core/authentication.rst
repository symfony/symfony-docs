.. index::
    single: Security, Core Authentication

Security Core: Authentication
=============================

As you've :ref:`already learned <components-security-terminology>`, the first
step in the Security system is authentication, answering the question: **Who
are you?**

Authentication Manager
----------------------

This question is answered by an
:class:`authentication manager <Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface>`.
This class recieves a token containing the information available about the
visitor of your application. Based on this information, it tries to determine
the visitor and returns an authenticated token. If it can't authenticate the
user, an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`
is thrown. This exception can the be transformed into a redirect to the login
form, for example.

Using the authentication manager, authenticating a user becomes very simple (no
worries, you'll fill the missing variables within a couple of minutes)::

    use Symfony\Component\Security\Core\Exception\AuthenticationException;

    try {
        $authenticatedToken = $authManager->authenticate($unauthenticatedToken);
    } catch (AuthenticationException $e) {
        // ... authentication failed, do something
    }

The Security component comes with one very flexible authentication manager, the
:class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationProviderManager`.
This uses authentication providers to authenticate a token.

.. _component-security-core-authentication-providers:

Authentication Providers
~~~~~~~~~~~~~~~~~~~~~~~~

One ``AuthenticationProviderManager`` can have multiple
:class:`authentication providers <Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface>`.
Using the ``supports()`` method, providers determines if they can authenticate
the passed token.

The most simple authentication provider is the
``AnonymousAuthenticationProvider``. This providers "authenticates" an
``AnonymousToken``. Anonymous users are guests, they aren't registered for the
application, but they are still authenticated.

.. code-block:: php

    // ...
    use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
    use Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider;
    use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

    $anonToken = new AnonymousToken('a_custom_key', 'Guest'.uniqid());

    // 'a_custom_key' has to match the 1st argument of AnonymousToken.
    $anonymousAuthProvider = new AnonymousAuthenticationProvider('a_custom_key');

    $authManager = new AuthenticationProviderManager(array(
        $anonymousAuthProvider,
    ));

    try {
        $authenticatedToken = $authManager->authenticate($unauthenticatedToken);
    } catch (AuthenticationException $e) {
        // ... authentication failed, do something
    }

    // user is authenticated as a guest

In this example, each requests to the application will result in an
authenticated anonymous token with different names starting with ``Guest``.

The DaoAuthenticationProvider (Data Access Object)
..................................................

This authentication provider is one of the most common authentication
providers. It'll authenticate an ``UsernamePasswordToken``, retrieve the user,
check the password and then return an authenticated ``UsernamePasswordToken``
containing the user object. That's quite a lot, the following paragraphs each
explain one of these steps.

The first step is to retrieve the user. The dao provider does this with the
help of a
:class:`user provider <Symfony\\Component\\Security\\Core\\User\\UserProviderInterface>`.
This can provide users from a database for example. The most basic user
provider is the ``InMemoryUserProvider``, which provides users from an array::

    use Symfony\Component\Security\Core\User\InMemoryUserProvider;

    $userProvider = new InMemoryUserProvider(array(
        'wouter' => array('password' => 'pa$$'),
    ));

After the user is retrieved, the dao provider checks if the password from the
token and the user provided by the user provider (based on its username) are
the same. It does this with the help of a
:class:`password encoder <Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface>`.
This encodes the password in the token in the same way as the password was
encoded during registration and then compares the two. As the password was
saved in plain text (for demostration purposes), you're going to need the
``PlaintextPasswordEncoder``.

In order to be able to authenticate different types of users, using different
encoding strategies, you'll pass an ``EncoderFactory`` instance. This factory
is configured with the correct encoder for the correct user type.

.. code-block:: php

    use Symfony\Component\Security\Core\Encoder\EncoderFactory;
    use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

    $encoderFactory = new EncoderFactory(array(
        // this is the FQCN to the user object used by the in memory user provider
        'Symfony\Component\Security\Core\User\User' => new PlaintextPasswordEncoder(),
    ));

Now the user is retrieved and matched against the submitted password, the dao
provider has to do some final checks. For instance, if the user is not banned
or if the account is not expired. Such checks are done by
:class:`user checkers <Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface>`::

    use Symfony\Component\Security\Core\User\UserChecker;

    $userChecker = new UserChecker();

Everything is now set-up and ready to be combined into the dao authentication
provider!

.. code-block::

    use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
    use Symfony\Component\Security\Core\Encoder\EncoderFactory;
    use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
    use Symfony\Component\Security\Core\User\InMemoryUserProvider;
    use Symfony\Component\Security\Core\User\UserChecker;

    $userProvider = new InMemoryUserProvider(array(
        'wouter' => array('password' => 'pa$$'),
    ));

    $encoderFactory = new EncoderFactory(array(
        // this is the FQCN to the user object used by the in memory user provider
        'Symfony\Component\Security\Core\User\User' => new PlaintextPasswordEncoder(),
    ));

    $userChecker = new UserChecker();

    $daoAuthProvider = new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'default',      // used to group multiple providers
        $encoderFactory
    );

You can now inject the dao authentication provider in your authentication
manager::

    // ...
    $anonymousAuthProvider = new AnonymousAuthenticationProvider('a_custom_key');

    $authManager = new AuthenticationProviderManager(array(
        $daoAuthProvider,
        $anonymousAuthProvider,
    ));

Now, you can test this out. As both the dao and anonymous authentication
providers are configured, you can now authenticate an anonymous user as well as
a real member::

    // ...
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

    $token = new UsernamePasswordToken(
        'wouter', // username
        'pa$$',   // password
        'default' // provider key (used to determine which providers are targetted)
    );

    try {
        $authenticatedToken = $authManager->authenticate($token);
    } catch (AuthenticationException $e) {
        // ... bummer, user submitted a wrong username/password or has not been registered
    }

    // ... user is authenticated and a real member!

Other Authentication Providers
..............................

The component contains some other authentication providers:

:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\RememberMeAuthenticationProvider`
    This can authenticate a
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken`,
    which can be used to be automatically logged in again after the current
    session expired.

:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\SimpleAuthenticationProvider`
    This authentication provider provides a simple way to customize the
    authentication process. You use this provider to implement a custom way to
    authenticate.

:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\UserAuthenticationProvider` (abstract)
    This is a base class for authentication providers dealing with the
    ``UsernamePasswordToken`` (like the dao provider).

Wrapping Up
-----------

This chapter, you've implemented a very simple authentication part of the
Security system. As you've probably seen a lot of new stuff, this is a good
moment to recap what you've learned:

#. Authentication is done by an *authentication manager*
#. The authentication manager uses *authentication provider* to transform and
   *unauthenticated token* into an authenticated one.
#. The *dao authentication provider* is commonly used. It's behaviour can be
   customized by implementing custom *user providers*, *user checkers* and
   *password encoders*.
#. Other authentication providers are the *anonymous*, *rembember me* and
   *simple* providers.
