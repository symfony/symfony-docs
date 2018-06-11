.. index::
    single: Security; Custom Authentication

How to Create a Custom Authentication System with Guard
=======================================================

Whether you need to build a traditional login form, an API token authentication system
or you need to integrate with some proprietary single-sign-on system, the Guard
component can make it easy... and fun!

In this example, you'll build an API token authentication system and learn how
to work with Guard.

Create a User and a User Provider
---------------------------------

No matter how you authenticate, you need to create a User class that implements ``UserInterface``
and configure a :doc:`user provider </security/custom_provider>`. In this
example, users are stored in the database via Doctrine, and each user has an ``apiKey``
property they use to access their account via the API::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="`user`")
     */
    class User implements UserInterface
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @ORM\Column(type="string", unique=true)
         */
        private $username;

        /**
         * @ORM\Column(type="string", unique=true)
         */
        private $apiKey;

        public function getUsername()
        {
            return $this->username;
        }

        public function getRoles()
        {
            return array('ROLE_USER');
        }

        public function getPassword()
        {
        }
        public function getSalt()
        {
        }
        public function eraseCredentials()
        {
        }

        // more getters/setters
    }

.. caution::

    In the example above, the table name is ``user``. This is a reserved SQL
    keyword and `must be quoted with backticks`_ in Doctrine to avoid errors.
    You might also change the table name (e.g. with ``app_users``) to solve
    this issue.

.. tip::

    This User doesn't have a password, but you can add a ``password`` property if
    you also want to allow this user to login with a password (e.g. via a login form).

Your ``User`` class doesn't need to be stored in Doctrine: do whatever you need.
Next, make sure you've configured a "user provider" for the user:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            providers:
                your_db_provider:
                    entity:
                        class: AppBundle:User
                        property: apiKey

            # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <provider name="your_db_provider">
                    <entity class="AppBundle:User" />
                </provider>

                <!-- ... -->
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'providers' => array(
                'your_db_provider' => array(
                    'entity' => array(
                        'class' => 'AppBundle:User',
                    ),
                ),
            ),

            // ...
        ));

That's it! Need more information about this step, see:

* :doc:`/security/entity_provider`
* :doc:`/security/custom_provider`

Step 1) Create the Authenticator Class
--------------------------------------

Suppose you have an API where your clients will send an ``X-AUTH-TOKEN`` header
on each request with their API token. Your job is to read this and find the associated
user (if any).

To create a custom authentication system, just create a class and make it implement
:class:`Symfony\\Component\\Security\\Guard\\AuthenticatorInterface`. Or, extend
the simpler :class:`Symfony\\Component\\Security\\Guard\\AbstractGuardAuthenticator`.
This requires you to implement several methods::

    // src/AppBundle/Security/TokenAuthenticator.php
    namespace AppBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class TokenAuthenticator extends AbstractGuardAuthenticator
    {
        /**
         * Called on every request to decide if this authenticator should be
         * used for the request. Returning false will cause this authenticator
         * to be skipped.
         */
        public function supports(Request $request)
        {
            return $request->headers->has('X-AUTH-TOKEN');
        }

        /**
         * Called on every request. Return whatever credentials you want to
         * be passed to getUser() as $credentials.
         */
        public function getCredentials(Request $request)
        {
            return array(
                'token' => $request->headers->get('X-AUTH-TOKEN'),
            );
        }

        public function getUser($credentials, UserProviderInterface $userProvider)
        {
            $apiKey = $credentials['token'];

            if (null === $apiKey) {
                return;
            }

            // if a User object, checkCredentials() is called
            return $userProvider->loadUserByUsername($apiKey);
        }

        public function checkCredentials($credentials, UserInterface $user)
        {
            // check credentials - e.g. make sure the password is valid
            // no credential check is needed in this case

            // return true to cause authentication success
            return true;
        }

        public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
        {
            // on success, let the request continue
            return null;
        }

        public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
        {
            $data = array(
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

                // or to translate this message
                // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
            );

            return new JsonResponse($data, Response::HTTP_FORBIDDEN);
        }

        /**
         * Called when authentication is needed, but it's not sent
         */
        public function start(Request $request, AuthenticationException $authException = null)
        {
            $data = array(
                // you might translate this message
                'message' => 'Authentication Required'
            );

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        public function supportsRememberMe()
        {
            return false;
        }
    }

.. versionadded:: 3.4
    ``AuthenticatorInterface`` was introduced in Symfony 3.4. In previous Symfony
    versions, authenticators needed to implement ``GuardAuthenticatorInterface``.

Nice work! Each method is explained below: :ref:`The Guard Authenticator Methods<guard-auth-methods>`.

Step 2) Configure the Authenticator
-----------------------------------

To finish this, make sure your authenticator is registered as a service. If you're
using the :ref:`default services.yml configuration <service-container-services-load-example>`,
that happens automatically.

Finally, configure your ``firewalls`` key in ``security.yml`` to use this authenticator:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                # ...

                main:
                    anonymous: ~
                    logout: ~

                    guard:
                        authenticators:
                            - AppBundle\Security\TokenAuthenticator

                    # if you want, disable storing the user in the session
                    # stateless: true

                    # maybe other things, like form_login, remember_me, etc
                    # ...

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <!-- ... -->

                <firewall name="main"
                    pattern="^/"
                    anonymous="true"
                >
                    <logout />

                    <guard>
                        <authenticator>AppBundle\Security\TokenAuthenticator</authenticator>
                    </guard>

                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..
        use AppBundle\Security\TokenAuthenticator;

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'       => array(
                    'pattern'        => '^/',
                    'anonymous'      => true,
                    'logout'         => true,
                    'guard'          => array(
                        'authenticators'  => array(
                            TokenAuthenticator::class
                        ),
                    ),
                    // ...
                ),
            ),
        ));

You did it! You now have a fully-working API token authentication system. If your
homepage required ``ROLE_USER``, then you could test it under different conditions:

.. code-block:: bash

    # test with no token
    curl http://localhost:8000/
    # {"message":"Authentication Required"}

    # test with a bad token
    curl -H "X-AUTH-TOKEN: FAKE" http://localhost:8000/
    # {"message":"Username could not be found."}

    # test with a working token
    curl -H "X-AUTH-TOKEN: REAL" http://localhost:8000/
    # the homepage controller is executed: the page loads normally

Now, learn more about what each method does.

.. _guard-auth-methods:

The Guard Authenticator Methods
-------------------------------

Each authenticator needs the following methods:

**supports(Request $request)**
    This will be called on *every* request and your job is to decide if the
    authenticator should be used for this request (return ``true``) or if it
    should be skipped (return ``false``).

    .. versionadded:: 3.4
        The ``supports()`` method was introduced in Symfony 3.4. In previous Symfony
        versions, the authenticator could be skipped returning ``null`` in the
        ``getCredentials()`` method.

**getCredentials(Request $request)**
    This will be called on *every* request and your job is to read the token (or
    whatever your "authentication" information is) from the request and return it.
    These credentials are later passed as the first argument of ``getUser()``.

**getUser($credentials, UserProviderInterface $userProvider)**
    The ``$credentials`` argument is the value returned by ``getCredentials()``.
    Your job is to return an object that implements ``UserInterface``. If you do,
    then ``checkCredentials()`` will be called. If you return ``null`` (or throw
    an :ref:`AuthenticationException <guard-customize-error>`) authentication
    will fail.

**checkCredentials($credentials, UserInterface $user)**
    If ``getUser()`` returns a User object, this method is called. Your job is to
    verify if the credentials are correct. For a login form, this is where you would
    check that the password is correct for the user. To pass authentication, return
    ``true``. If you return *anything* else
    (or throw an :ref:`AuthenticationException <guard-customize-error>`),
    authentication will fail.

**onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)**
    This is called after successful authentication and your job is to either
    return a :class:`Symfony\\Component\\HttpFoundation\\Response` object
    that will be sent to the client or ``null`` to continue the request
    (e.g. allow the route/controller to be called like normal). Since this
    is an API where each request authenticates itself, you want to return
    ``null``.

**onAuthenticationFailure(Request $request, AuthenticationException $exception)**
    This is called if authentication fails. Your job
    is to return the :class:`Symfony\\Component\\HttpFoundation\\Response`
    object that should be sent to the client. The ``$exception`` will tell you
    *what* went wrong during authentication.

**start(Request $request, AuthenticationException $authException = null)**
    This is called if the client accesses a URI/resource that requires authentication,
    but no authentication details were sent. Your job is to return a
    :class:`Symfony\\Component\\HttpFoundation\\Response` object that helps
    the user authenticate (e.g. a 401 response that says "token is missing!").

**supportsRememberMe()**
    If you want to support "remember me" functionality, return true from this method.
    You will still need to activate ``remember_me`` under your firewall for it to work.
    Since this is a stateless API, you do not want to support "remember me"
    functionality in this example.

**createAuthenticatedToken(UserInterface $user, string $providerKey)**
    If you are implementing the :class:`Symfony\\Component\\Security\\Guard\\AuthenticatorInterface`
    instead of extending the :class:`Symfony\\Component\\Security\\Guard\\AbstractGuardAuthenticator`
    class, you have to implement this method. It will be called
    after a successful authentication to create and return the token
    for the user, who was supplied as the first argument.

The picture below shows how Symfony calls Guard Authenticator methods:

.. raw:: html

    <object data="../_images/security/authentication-guard-methods.svg" type="image/svg+xml"></object>

.. _guard-customize-error:

Customizing Error Messages
--------------------------

When ``onAuthenticationFailure()`` is called, it is passed an ``AuthenticationException``
that describes *how* authentication failed via its ``$exception->getMessageKey()`` (and
``$exception->getMessageData()``) method. The message will be different based on *where*
authentication fails (i.e. ``getUser()`` versus ``checkCredentials()``).

But, you can easily return a custom message by throwing a
:class:`Symfony\\Component\\Security\\Core\\Exception\\CustomUserMessageAuthenticationException`.
You can throw this from ``getCredentials()``, ``getUser()`` or ``checkCredentials()``
to cause a failure::

    // src/AppBundle/Security/TokenAuthenticator.php
    // ...

    use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

    class TokenAuthenticator extends AbstractGuardAuthenticator
    {
        // ...

        public function getCredentials(Request $request)
        {
            // ...

            if ($token == 'ILuvAPIs') {
                throw new CustomUserMessageAuthenticationException(
                    'ILuvAPIs is not a real API key: it\'s just a silly phrase'
                );
            }

            // ...
        }

        // ...
    }

In this case, since "ILuvAPIs" is a ridiculous API key, you could include an easter
egg to return a custom message if someone tries this:

.. code-block:: bash

    curl -H "X-AUTH-TOKEN: ILuvAPIs" http://localhost:8000/
    # {"message":"ILuvAPIs is not a real API key: it's just a silly phrase"}

Building a Login Form
---------------------

If you're building a login form, use the :class:`Symfony\\Component\\Security\\Guard\\Authenticator\\AbstractFormLoginAuthenticator`
as your base class - it implements a few methods for you. Then, fill in the other
methods just like with the ``TokenAuthenticator``. Outside of Guard, you are still
responsible for creating a route, controller and template for your login form.

.. _guard-csrf-protection:

Adding CSRF Protection
----------------------

If you're using a Guard authenticator to build a login form and want to add CSRF
protection, no problem!

First, :ref:`add the _csrf_token to your login template <csrf-login-template>`.

Then, type-hint ``CsrfTokenManagerInterface`` in your ``__construct()`` method
(or manually configure the ``security.csrf.token_manager`` service to be passed)
and add the following logic::

    // src/AppBundle/Security/ExampleFormAuthenticator.php
    // ...

    use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
    use Symfony\Component\Security\Csrf\CsrfToken;
    use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
    use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

    class ExampleFormAuthenticator extends AbstractFormLoginAuthenticator
    {
        private $csrfTokenManager;

        public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
        {
            $this->csrfTokenManager = $csrfTokenManager;
        }

        public function getCredentials(Request $request)
        {
            $csrfToken = $request->request->get('_csrf_token');

            if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            // ... all your normal logic
        }

        // ...
    }

Avoid Authenticating the Browser on Every Request
-------------------------------------------------

If you create a Guard login system that's used by a browser and you're experiencing
problems with your session or CSRF tokens, the cause could be bad behavior by your
authenticator. When a Guard authenticator is meant to be used by a browser, you
should *not* authenticate the user on *every* request. In other words, you need to
make sure the ``getCredentials()`` method *only* returns a non-null value when
you actually *need* to authenticate the user. Why? Because, when ``getCredentials()``
returns a non-null value, for security purposes, the user's session is "migrated"
to a new session id.

This is an edge-case, and unless you're having session or CSRF token issues, you
can ignore this. Here is an example of good and bad behavior::

    public function getCredentials(Request $request)
    {
        // GOOD behavior: only authenticate on a specific route
        if ($request->attributes->get('_route') !== 'login_route' || !$request->isMethod('POST')) {
            return null;
        }

        // e.g. your login system authenticates by the user's IP address
        // BAD behavior: authentication will now execute on every request
        // even if the user is already authenticated (due to the session)
        return array('ip' => $request->getClientIp());
    }

The problem occurs when your browser-based authenticator tries to authenticate
the user on *every* request - like in the IP address-based example above. There
are two possible fixes:

1) If you do *not* need authentication to be stored in the session, set ``stateless: true``
under your firewall.

2) Update your authenticator to avoid authentication if the user is already authenticated:

.. code-block:: diff

    // src/Security/MyIpAuthenticator.php
    // ...

    + use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

    class MyIpAuthenticator
    {
    +     private $tokenStorage;

    +     public function __construct(TokenStorageInterface $tokenStorage)
    +     {
    +         $this->tokenStorage = $tokenStorage;
    +     }

        public function getCredentials(Request $request)
        {
    +         // if there is already an authenticated user (likely due to the session)
    +         // then return null and skip authentication: there is no need.
    +         $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
    +         if (is_object($user)) {
    +             return null;
    +         }

            return array('ip' => $request->getClientIp());
        }
    }

You'll also need to update your service configuration to pass the token storage:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.token_authenticator:
                class: AppBundle\Security\TokenAuthenticator
                arguments: ['@security.token_storage']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.token_authenticator" class="AppBundle\Security\TokenAuthenticator">
                <argument type="service" id="security.token_storage" />
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Security\TokenAuthenticator;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('app.token_authenticator', TokenAuthenticator::class)
            ->addArgument(new Reference('security.token_storage'));

Frequently Asked Questions
--------------------------

**Can I have Multiple Authenticators?**
    Yes! But when you do, you'll need choose just *one* authenticator to be your
    "entry_point". This means you'll need to choose *which* authenticator's ``start()``
    method should be called when an anonymous user tries to access a protected resource.
    For more details, see :doc:`/security/multiple_guard_authenticators`.

**Can I use this with form_login?**
    Yes! ``form_login`` is *one* way to authenticate a user, so you could use
    it *and* then add one or more authenticators. Using a guard authenticator doesn't
    collide with other ways to authenticate.

**Can I use this with FOSUserBundle?**
    Yes! Actually, FOSUserBundle doesn't handle security: it simply gives you a
    ``User`` object and some routes and controllers to help with login, registration,
    forgot password, etc. When you use FOSUserBundle, you typically use ``form_login``
    to actually authenticate the user. You can continue doing that (see previous
    question) or use the ``User`` object from FOSUserBundle and create your own
    authenticator(s) (just like in this article).

.. _`must be quoted with backticks`: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
