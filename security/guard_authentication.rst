.. index::
    single: Security; Custom Authentication

Custom Authentication System with Guard (API Token Example)
===========================================================

Whether you need to build a traditional login form, an API token authentication system
or you need to integrate with some proprietary single-sign-on system, the Guard
component will be the right choice!

Guard authentication can be used to:

* :doc:`Build a Login Form </security/form_login_setup>`,
* Create an API token authentication system (done on this page!)
* `Social Authentication`_ (or use `HWIOAuthBundle`_ for a robust, but non-Guard solution)

or anything else. In this example, we'll build an API token authentication
system so we can learn more about Guard in detail.

Step 1) Prepare your User Class
-------------------------------

Suppose you want to build an API where your clients will send an ``X-AUTH-TOKEN`` header
on each request with their API token. Your job is to read this and find the associated
user (if any).

First, make sure you've followed the main :doc:`Security Guide </security>` to
create your ``User`` class. Then, to keep things simple, add an ``apiToken`` property
directly to your ``User`` class (the ``make:entity`` command is a good way to do this):

.. code-block:: diff

    // src/Entity/User.php
    // ...

    class User implements UserInterface
    {
        // ...

    +     /**
    +      * @ORM\Column(type="string", unique=true)
    +      */
    +     private $apiToken;

        // the getter and setter methods
    }

Don't forget to generate and execute the migration:

.. code-block:: terminal

    $ php bin/console make:migration
    $ php bin/console doctrine:migrations:migrate

Step 2) Create the Authenticator Class
--------------------------------------

To create a custom authentication system, create a class and make it implement
:class:`Symfony\\Component\\Security\\Guard\\AuthenticatorInterface`. Or, extend
the simpler :class:`Symfony\\Component\\Security\\Guard\\AbstractGuardAuthenticator`.

This requires you to implement several methods::

    // src/Security/TokenAuthenticator.php
    namespace App\Security;

    use App\Entity\User;
    use Doctrine\ORM\EntityManagerInterface;
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
        private $em;

        public function __construct(EntityManagerInterface $em)
        {
            $this->em = $em;
        }

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
            return [
                'token' => $request->headers->get('X-AUTH-TOKEN'),
            ];
        }

        public function getUser($credentials, UserProviderInterface $userProvider)
        {
            $apiToken = $credentials['token'];

            if (null === $apiToken) {
                return;
            }

            // if a User object, checkCredentials() is called
            return $this->em->getRepository(User::class)
                ->findOneBy(['apiToken' => $apiToken]);
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
            $data = [
                'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

                // or to translate this message
                // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
            ];

            return new JsonResponse($data, Response::HTTP_FORBIDDEN);
        }

        /**
         * Called when authentication is needed, but it's not sent
         */
        public function start(Request $request, AuthenticationException $authException = null)
        {
            $data = [
                // you might translate this message
                'message' => 'Authentication Required'
            ];

            return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }

        public function supportsRememberMe()
        {
            return false;
        }
    }

Nice work! Each method is explained below: :ref:`The Guard Authenticator Methods<guard-auth-methods>`.

Step 3) Configure the Authenticator
-----------------------------------

To finish this, make sure your authenticator is registered as a service. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
that happens automatically.

Finally, configure your ``firewalls`` key in ``security.yaml`` to use this authenticator:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                # ...

                main:
                    anonymous: ~
                    logout: ~

                    guard:
                        authenticators:
                            - App\Security\TokenAuthenticator

                    # if you want, disable storing the user in the session
                    # stateless: true

                    # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <!-- ... -->

                <firewall name="main"
                    pattern="^/"
                    anonymous="true"
                >
                    <logout/>

                    <guard>
                        <authenticator>App\Security\TokenAuthenticator</authenticator>
                    </guard>

                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php

        // ...
        use App\Security\TokenAuthenticator;

        $container->loadFromExtension('security', [
            'firewalls' => [
                'main'       => [
                    'pattern'        => '^/',
                    'anonymous'      => true,
                    'logout'         => true,
                    'guard'          => [
                        'authenticators'  => [
                            TokenAuthenticator::class
                        ],
                    ],
                    // ...
                ],
            ],
        ]);

You did it! You now have a fully-working API token authentication system. If your
homepage required ``ROLE_USER``, then you could test it under different conditions:

.. code-block:: terminal

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

But, you can also return a custom message by throwing a
:class:`Symfony\\Component\\Security\\Core\\Exception\\CustomUserMessageAuthenticationException`.
You can throw this from ``getCredentials()``, ``getUser()`` or ``checkCredentials()``
to cause a failure::

    // src/Security/TokenAuthenticator.php
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

.. code-block:: terminal

    curl -H "X-AUTH-TOKEN: ILuvAPIs" http://localhost:8000/
    # {"message":"ILuvAPIs is not a real API key: it's just a silly phrase"}

.. _guard-manual-auth:

Manually Authenticating a User
------------------------------

Sometimes you might want to manually authenticate a user - like after the user
completes registration. To do that, use your authenticator and a service called
``GuardAuthenticatorHandler``::

    // src/Controller/RegistrationController.php
    // ...

    use App\Security\LoginFormAuthenticator;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

    class RegistrationController extends AbstractController
    {
        public function register(LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler, Request $request)
        {
            // ...

            // after validating the user and saving them to the database
            // authenticate the user and use onAuthenticationSuccess on the authenticator
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,          // the User object you just created
                $request,
                $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                'main'          // the name of your firewall in security.yaml
            );
        }
    }

Avoid Authenticating the Browser on Every Request
-------------------------------------------------

If you create a Guard login system that's used by a browser and you're experiencing
problems with your session or CSRF tokens, the cause could be bad behavior by your
authenticator. When a Guard authenticator is meant to be used by a browser, you
should *not* authenticate the user on *every* request. In other words, you need to
make sure the ``supports()`` method *only* returns ``true`` when
you actually *need* to authenticate the user. Why? Because, when ``supports()``
returns true (and authentication is ultimately successful), for security purposes,
the user's session is "migrated" to a new session id.

This is an edge-case, and unless you're having session or CSRF token issues, you
can ignore this. Here is an example of good and bad behavior::

    public function supports(Request $request)
    {
        // GOOD behavior: only authenticate (i.e. return true) on a specific route
        return 'login_route' === $request->attributes->get('_route') && $request->isMethod('POST');

        // e.g. your login system authenticates by the user's IP address
        // BAD behavior: So, you decide to *always* return true so that
        // you can check the user's IP address on every request
        return true;
    }

The problem occurs when your browser-based authenticator tries to authenticate
the user on *every* request - like in the IP address-based example above. There
are two possible fixes:

1. If you do *not* need authentication to be stored in the session, set
   ``stateless: true`` under your firewall.
2. Update your authenticator to avoid authentication if the user is already
   authenticated:

.. code-block:: diff

    // src/Security/MyIpAuthenticator.php
    // ...

    + use Symfony\Component\Security\Core\Security;

    class MyIpAuthenticator
    {
    +     private $security;

    +     public function __construct(Security $security)
    +     {
    +         $this->security = $security;
    +     }

        public function supports(Request $request)
        {
    +         // if there is already an authenticated user (likely due to the session)
    +         // then return false and skip authentication: there is no need.
    +         if ($this->security->getUser()) {
    +             return false;
    +         }

    +         // the user is not logged in, so the authenticator should continue
    +         return true;
        }
    }

If you use autowiring, the ``Security``  service will automatically be passed to
your authenticator.

Frequently Asked Questions
--------------------------

**Can I have Multiple Authenticators?**
    Yes! But when you do, you'll need to choose just *one* authenticator to be your
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
.. _`Social Authentication`: https://github.com/knpuniversity/oauth2-client-bundle#authenticating-with-guard
.. _`HWIOAuthBundle`: https://github.com/hwi/HWIOAuthBundle
