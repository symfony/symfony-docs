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
The listener should then store the authenticated token in the security context::

    use Symfony\Component\Security\Http\Firewall\ListenerInterface;
    use Symfony\Component\Security\Core\SecurityContextInterface;
    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

    class SomeAuthenticationListener implements ListenerInterface
    {
        /**
         * @var SecurityContextInterface
         */
        private $securityContext;

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

            $this->securityContext->setToken($authenticatedToken);
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

    // instances of Symfony\Component\Security\Core\Authentication\AuthenticationProviderInterface
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

Authentication providers
------------------------

Each provider (since it implements
:class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface`)
has a method :method:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::supports`
by which the ``AuthenticationProviderManager``
can determine if it supports the given token. If this is the case, the
manager then calls the provider's method :class:`Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AuthenticationProviderInterface::authenticate`.
This method should return an authenticated token or throw an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException`
(or any other exception extending it).

Authenticating Users by their Username and Password
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

An authentication provider will attempt to authenticate a user based on
the credentials he provided. Usually these are a username and a password.
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

The Password encoder Factory
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

Password Encoders
~~~~~~~~~~~~~~~~~

When the :method:`Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactory::getEncoder`
method of the password encoder factory is called with the user object as
its first argument, it will return an encoder of type :class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`
which should be used to encode this user's password::

    // fetch a user of type Acme\Entity\LegacyUser
    $user = ...

    $encoder = $encoderFactory->getEncoder($user);

    // will return $weakEncoder (see above)

    $encodedPassword = $encoder->encodePassword($password, $user->getSalt());

    // check if the password is valid:

    $validPassword = $encoder->isPasswordValid(
        $user->getPassword(),
        $password,
        $user->getSalt());
