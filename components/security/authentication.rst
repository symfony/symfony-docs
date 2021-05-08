.. index::
   single: Security, Authentication

Authentication
==============

When a request points to a secured area, and one of the listeners from the
firewall map is able to extract the user's credentials from the current
:class:`Symfony\\Component\\HttpFoundation\\Request` object, it should create
a token, containing these credentials. The next thing the listener should
do is ask the authentication manager to validate the given token, and return
an *authenticated* token if the supplied credentials were found to be valid.
The listener should then store the authenticated token using
:class:`the token storage <Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface>`::

    use Symfony\Component\HttpKernel\Event\RequestEvent;
    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

    class SomeAuthenticationListener
    {
        /**
         * @var TokenStorageInterface
         */
        private $tokenStorage;

        /**
         * @var AuthenticationManagerInterface
         */
        private $authenticationManager;

        /**
         * @var string Uniquely identifies the secured area
         */
        private $providerKey;

        // ...

        public function __invoke(RequestEvent $event)
        {
            $request = $event->getRequest();

            $username = ...;
            $password = ...;

            $unauthenticatedToken = new UsernamePasswordToken(
                $username,
                $password,
                $this->providerKey
            );

            $authenticatedToken = $this
                ->authenticationManager
                ->authenticate($unauthenticatedToken);

            $this->tokenStorage->setToken($authenticatedToken);
        }
    }

.. note::

    A token can be of any class, as long as it implements
    :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface`.

The Authentication Manager
--------------------------

The default authentication manager is an instance of
:class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationProviderManager`::

    use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;

    // instances of Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface
    $providers = [...];

    $authenticationManager = new AuthenticationProviderManager($providers);

    try {
        $authenticatedToken = $authenticationManager
            ->authenticate($unauthenticatedToken);
    } catch (AuthenticationException $exception) {
        // authentication failed
    }

The ``AuthenticationProviderManager``, when instantiated, receives several
authentication providers, each supporting a different type of token.

.. note::

    You may write your own authentication manager, the only requirement is that
    it implements :class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface`.

.. _authentication_providers:

Authentication Providers
------------------------

Each provider (since it implements
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface`)
has a :method:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::supports` method
by which the ``AuthenticationProviderManager``
can determine if it supports the given token. If this is the case, the
manager then calls the provider's :method:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::authenticate` method.
This method should return an authenticated token or throw an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`
(or any other exception extending it).

Authenticating Users by their Username and Password
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An authentication provider will attempt to authenticate a user based on
the credentials they provided. Usually these are a username and a password.
Most web applications store their user's username and a hash of the user's
password combined with a randomly generated salt. This means that the average
authentication would consist of fetching the salt and the hashed password
from the user data storage, hash the password the user has just provided
(e.g. using a login form) with the salt and compare both to determine if
the given password is valid.

This functionality is offered by the :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\DaoAuthenticationProvider`.
It fetches the user's data from a :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`,
uses a :class:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface`
to create a hash of the password and returns an authenticated token if the
password was valid::

    use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
    use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
    use Symfony\Component\Security\Core\User\InMemoryUserProvider;
    use Symfony\Component\Security\Core\User\UserChecker;

    // The 'InMemoryUser' class was introduced in Symfony 5.3.
    // In previous versions it was called 'User'
    $userProvider = new InMemoryUserProvider(
        [
            'admin' => [
                // password is "foo"
                'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
                'roles'    => ['ROLE_ADMIN'],
            ],
        ]
    );

    // for some extra checks: is account enabled, locked, expired, etc.
    $userChecker = new UserChecker();

    // an array of password hashers (see below)
    $hasherFactory = new PasswordHasherFactoryInterface(...);

    $daoProvider = new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'secured_area',
        $hasherFactory
    );

    $daoProvider->authenticate($unauthenticatedToken);

.. note::

    The example above demonstrates the use of the "in-memory" user provider,
    but you may use any user provider, as long as it implements
    :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`.
    It is also possible to let multiple user providers try to find the user's
    data, using the :class:`Symfony\\Component\\Security\\Core\\User\\ChainUserProvider`.

.. _the-password-encoder-factory:

The Password Hasher Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\DaoAuthenticationProvider`
uses a factory to create a password hasher for a given type of user. This allows
you to use different hashing strategies for different types of users.
The default :class:`Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherFactory`
receives an array of hashers::

    use Acme\Entity\LegacyUser;
    use Symfony\Component\PasswordHasher\Hasher\MessageDigestPasswordHasher;
    use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
    use Symfony\Component\Security\Core\User\InMemoryUser;

    $defaultHasher = new MessageDigestPasswordHasher('sha512', true, 5000);
    $weakHasher = new MessageDigestPasswordHasher('md5', true, 1);

    $hashers = [
        InMemoryUser::class => $defaultHasher,
        LegacyUser::class   => $weakHasher,
        // ...
    ];
    $hasherFactory = new PasswordHasherFactory($hashers);

Each hasher should implement :class:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface`
or be an array with a ``class`` and an ``arguments`` key, which allows the
hasher factory to construct the hasher only when it is needed.

.. _creating-a-custom-password-encoder:

Creating a custom Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are many built-in password hasher. But if you need to create your
own, it needs to follow these rules:

#. The class must implement :class:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface`
   (you can also extend :class:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasher`);

#. The implementations of
   :method:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface::hashPassword`
   and
   :method:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface::isPasswordValid`
   must first of all make sure the password is not too long, i.e. the password length is no longer
   than 4096 characters. This is for security reasons (see `CVE-2013-5750`_), and you can use the
   :method:`Symfony\\Component\\PasswordHasher\\Hasher\\CheckPasswordLengthTrait::isPasswordTooLong`
   method for this check::

       use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
       use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
       use Symfony\Component\Security\Core\Exception\BadCredentialsException;

       class FoobarHasher extends UserPasswordHasher
       {
           use CheckPasswordLengthTrait;

           public function hashPassword(UserInterface $user, string $plainPassword): string
           {
               if ($this->isPasswordTooLong($user->getPassword())) {
                   throw new BadCredentialsException('Invalid password.');
               }

               // ...
           }

           public function isPasswordValid(UserInterface $user, string $plainPassword)
           {
               if ($this->isPasswordTooLong($user->getPassword())) {
                   return false;
               }

               // ...
           }
       }

.. _using-password-encoders:

Using Password Hashers
~~~~~~~~~~~~~~~~~~~~~~

When the :method:`Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherFactory::getPasswordHasher`
method of the password hasher factory is called with the user object as
its first argument, it will return a hasher of type :class:`Symfony\\Component\\PasswordHasher\\PasswordHasherInterface`
which should be used to hash this user's password::

    // a Acme\Entity\LegacyUser instance
    $user = ...;

    // the password that was submitted, e.g. when registering
    $plainPassword = ...;

    $hasher = $hasherFactory->getPasswordHasher($user);

    // returns $weakHasher (see above)
    $hashedPassword = $hasher->hashPassword($user, $plainPassword);

    $user->setPassword($hashedPassword);

    // ... save the user

Now, when you want to check if the submitted password (e.g. when trying to log
in) is correct, you can use::

    // fetch the Acme\Entity\LegacyUser
    $user = ...;

    // the submitted password, e.g. from the login form
    $plainPassword = ...;

    $validPassword = $hasher->isPasswordValid($user, $plainPassword);

Authentication Events
---------------------

The security component provides the following authentication events:

===============================  ======================================================================== ==============================================================================
Name                             Event Constant                                                           Argument Passed to the Listener
===============================  ======================================================================== ==============================================================================
security.authentication.success  ``AuthenticationEvents::AUTHENTICATION_SUCCESS``                         :class:`Symfony\\Component\\Security\\Core\\Event\\AuthenticationSuccessEvent`
security.authentication.failure  ``AuthenticationEvents::AUTHENTICATION_FAILURE``                         :class:`Symfony\\Component\\Security\\Core\\Event\\AuthenticationFailureEvent`
security.interactive_login       ``SecurityEvents::INTERACTIVE_LOGIN``                                    :class:`Symfony\\Component\\Security\\Http\\Event\\InteractiveLoginEvent`
security.switch_user             ``SecurityEvents::SWITCH_USER``                                          :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent`
security.logout_on_change        ``Symfony\Component\Security\Http\Event\DeauthenticatedEvent::class``    :class:`Symfony\\Component\\Security\\Http\\Event\\DeauthenticatedEvent`
===============================  ======================================================================== ==============================================================================

Authentication Success and Failure Events
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a provider authenticates the user, a ``security.authentication.success``
event is dispatched. But beware - this event may fire, for example, on *every*
request if you have session-based authentication, if ``always_authenticate_before_granting``
is enabled or if token is not authenticated before AccessListener is invoked.
See ``security.interactive_login`` below if you need to do something when a user *actually* logs in.

When a provider attempts authentication but fails (i.e. throws an ``AuthenticationException``),
a ``security.authentication.failure`` event is dispatched. You could listen on
the ``security.authentication.failure`` event, for example, in order to log
failed login attempts.

Security Events
~~~~~~~~~~~~~~~

The ``security.interactive_login`` event is triggered after a user has actively
logged into your website.  It is important to distinguish this action from
non-interactive authentication methods, such as:

* authentication based on your session.
* authentication using a HTTP basic header.

You could listen on the ``security.interactive_login`` event, for example, in
order to give your user a welcome flash message every time they log in.

The ``security.switch_user`` event is triggered every time you activate
the ``switch_user`` firewall listener.

The ``Symfony\Component\Security\Http\Event\DeauthenticatedEvent`` event is triggered when a token has been deauthenticated
because of a user change, it can help you doing some clean-up task.

.. seealso::

    For more information on switching users, see
    :doc:`/security/impersonating_user`.

.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
