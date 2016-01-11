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

    use Symfony\Component\Security\Http\Firewall\ListenerInterface;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

    class SomeAuthenticationListener implements ListenerInterface
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

        public function handle(GetResponseEvent $event)
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

    // instances of Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface
    $providers = array(...);

    $authenticationManager = new AuthenticationProviderManager($providers);

    try {
        $authenticatedToken = $authenticationManager
            ->authenticate($unauthenticatedToken);
    } catch (AuthenticationException $failed) {
        // authentication failed
    }

The ``AuthenticationProviderManager``, when instantiated, receives several
authentication providers, each supporting a different type of token.

.. note::

    You may of course write your own authentication manager, it only has
    to implement :class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface`.

.. _authentication_providers:

Authentication Providers
------------------------

Each provider (since it implements
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface`)
has a method :method:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::supports`
by which the ``AuthenticationProviderManager``
can determine if it supports the given token. If this is the case, the
manager then calls the provider's method :method:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::authenticate`.
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
uses a :class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`
to create a hash of the password and returns an authenticated token if the
password was valid::

    use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
    use Symfony\Component\Security\Core\User\UserChecker;
    use Symfony\Component\Security\Core\User\InMemoryUserProvider;
    use Symfony\Component\Security\Core\Encoder\EncoderFactory;

    $userProvider = new InMemoryUserProvider(
        array(
            'admin' => array(
                // password is "foo"
                'password' => '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
                'roles'    => array('ROLE_ADMIN'),
            ),
        )
    );

    // for some extra checks: is account enabled, locked, expired, etc.?
    $userChecker = new UserChecker();

    // an array of password encoders (see below)
    $encoderFactory = new EncoderFactory(...);

    $provider = new DaoAuthenticationProvider(
        $userProvider,
        $userChecker,
        'secured_area',
        $encoderFactory
    );

    $provider->authenticate($unauthenticatedToken);

.. note::

    The example above demonstrates the use of the "in-memory" user provider,
    but you may use any user provider, as long as it implements
    :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`.
    It is also possible to let multiple user providers try to find the user's
    data, using the :class:`Symfony\\Component\\Security\\Core\\User\\ChainUserProvider`.

The Password Encoder Factory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\DaoAuthenticationProvider`
uses an encoder factory to create a password encoder for a given type of
user. This allows you to use different encoding strategies for different
types of users. The default :class:`Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactory`
receives an array of encoders::

    use Symfony\Component\Security\Core\Encoder\EncoderFactory;
    use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

    $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
    $weakEncoder = new MessageDigestPasswordEncoder('md5', true, 1);

    $encoders = array(
        'Symfony\\Component\\Security\\Core\\User\\User' => $defaultEncoder,
        'Acme\\Entity\\LegacyUser'                       => $weakEncoder,

        // ...
    );

    $encoderFactory = new EncoderFactory($encoders);

Each encoder should implement :class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`
or be an array with a ``class`` and an ``arguments`` key, which allows the
encoder factory to construct the encoder only when it is needed.

Creating a custom Password Encoder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are many built-in password encoders. But if you need to create your
own, it just needs to follow these rules:

#. The class must implement :class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`;

#. The implementations of
   :method:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface::encodePassword`
   and
   :method:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface::isPasswordValid`
   must first of all make sure the password is not too long, i.e. the password length is no longer
   than 4096 characters. This is for security reasons (see `CVE-2013-5750`_), and you can use the
   :method:`Symfony\\Component\\Security\\Core\\Encoder\\BasePasswordEncoder::isPasswordTooLong`
   method for this check::

       use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
       use Symfony\Component\Security\Core\Exception\BadCredentialsException;

       class FoobarEncoder extends BasePasswordEncoder
       {
           public function encodePassword($raw, $salt)
           {
               if ($this->isPasswordTooLong($raw)) {
                   throw new BadCredentialsException('Invalid password.');
               }

               // ...
           }

           public function isPasswordValid($encoded, $raw, $salt)
           {
               if ($this->isPasswordTooLong($raw)) {
                   return false;
               }

               // ...
       }

Using Password Encoders
~~~~~~~~~~~~~~~~~~~~~~~

When the :method:`Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactory::getEncoder`
method of the password encoder factory is called with the user object as
its first argument, it will return an encoder of type :class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`
which should be used to encode this user's password::

    // a Acme\Entity\LegacyUser instance
    $user = ...;

    // the password that was submitted, e.g. when registering
    $plainPassword = ...;

    $encoder = $encoderFactory->getEncoder($user);

    // will return $weakEncoder (see above)
    $encodedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());

    $user->setPassword($encodedPassword);

    // ... save the user

Now, when you want to check if the submitted password (e.g. when trying to log
in) is correct, you can use::

    // fetch the Acme\Entity\LegacyUser
    $user = ...;

    // the submitted password, e.g. from the login form
    $plainPassword = ...;

    $validPassword = $encoder->isPasswordValid(
        $user->getPassword(), // the encoded password
        $plainPassword,       // the submitted password
        $user->getSalt()
    );

Authentication Events
---------------------

The security component provides 4 related authentication events:

===============================  ================================================  ==============================================================================
Name                             Event Constant                                    Argument Passed to the Listener
===============================  ================================================  ==============================================================================
security.authentication.success  ``AuthenticationEvents::AUTHENTICATION_SUCCESS``  :class:`Symfony\\Component\\Security\\Core\\Event\\AuthenticationEvent`
security.authentication.failure  ``AuthenticationEvents::AUTHENTICATION_FAILURE``  :class:`Symfony\\Component\\Security\\Core\\Event\\AuthenticationFailureEvent`
security.interactive_login       ``SecurityEvents::INTERACTIVE_LOGIN``             :class:`Symfony\\Component\\Security\\Http\\Event\\InteractiveLoginEvent`
security.switch_user             ``SecurityEvents::SWITCH_USER``                   :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent`
===============================  ================================================  ==============================================================================

Authentication Success and Failure Events
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a provider authenticates the user, a ``security.authentication.success``
event is dispatched. But beware - this event will fire, for example, on *every*
request if you have session-based authentication. See ``security.interactive_login``
below if you need to do something when a user *actually* logs in.

When a provider attempts authentication but fails (i.e. throws an ``AuthenticationException``),
a ``security.authentication.failure`` event is dispatched. You could listen on
the ``security.authentication.failure`` event, for example, in order to log
failed login attempts.

Security Events
~~~~~~~~~~~~~~~

The ``security.interactive_login`` event is triggered after a user has actively
logged into your website.  It is important to distinguish this action from
non-interactive authentication methods, such as:

* authentication based on a "remember me" cookie.
* authentication based on your session.
* authentication using a HTTP basic or HTTP digest header.

You could listen on the ``security.interactive_login`` event, for example, in
order to give your user a welcome flash message every time they log in.

The ``security.switch_user`` event is triggered every time you activate
the ``switch_user`` firewall listener.

.. seealso::

    For more information on switching users, see
    :doc:`/cookbook/security/impersonating_user`.

.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
.. _`BasePasswordEncoder::checkPasswordLength`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Core/Encoder/BasePasswordEncoder.php
