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
and configure a :doc:`user provider </cookbook/security/custom_provider>`. In this
example, users are stored in the database via Doctrine, and each user has an ``apiKey``
property they use to access their account via the API::

    // src/AppBundle/Entity/User.php
    namespace AppBundle\Entity;

    use Symfony\Component\Security\Core\User\UserInterface;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="user")
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
            return ['ROLE_USER'];
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

* :doc:`/cookbook/security/entity_provider`
* :doc:`/cookbook/security/custom_provider`

Step 1) Create the Authenticator Class
--------------------------------------

Suppose you have an API where your clients will send an ``X-AUTH-TOKEN`` header
on each request with their API token. Your job is to read this and find the associated
user (if any).

To create a custom authentication system, just create a class and make it implement
:class:`Symfony\\Component\\Security\\Guard\\GuardAuthenticatorInterface`. Or, extend
the simpler :class:`Symfony\\Component\\Security\\Guard\\AbstractGuardAuthenticator`.
This requires you to implement six methods::

    // src/AppBundle/Security/TokenAuthenticator.php
    namespace AppBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Doctrine\ORM\EntityManager;

    class TokenAuthenticator extends AbstractGuardAuthenticator
    {
        private $em;

        public function __construct(EntityManager $em)
        {
            $this->em = $em;
        }

        /**
         * Called on every request. Return whatever credentials you want,
         * or null to stop authentication.
         */
        public function getCredentials(Request $request)
        {
            if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
                // no token? Return null and no other methods will be called
                return;
            }

            // What you return here will be passed to getUser() as $credentials
            return array(
                'token' => $token,
            );
        }

        public function getUser($credentials, UserProviderInterface $userProvider)
        {
            $apiKey = $credentials['token'];

            // if null, authentication will fail
            // if a User object, checkCredentials() is called
            return $this->em->getRepository('AppBundle:User')
                ->findOneBy(array('apiKey' => $apiKey));
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

            return new JsonResponse($data, 403);
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

            return new JsonResponse($data, 401);
        }

        public function supportsRememberMe()
        {
            return false;
        }
    }

Nice work! Each method is explained below: :ref:`The Guard Authenticator Methods<guard-auth-methods>`.

Step 2) Configure the Authenticator
-----------------------------------

To finish this, register the class as a service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.token_authenticator:
                class: AppBundle\Security\TokenAuthenticator
                arguments: ['@doctrine.orm.entity_manager']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.token_authenticator" class="AppBundle\Security\TokenAuthenticator">
                <argument type="service" id="doctrine.orm.entity_manager"/>
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.token_authenticator', new Definition(
            'AppBundle\Security\TokenAuthenticator',
            array(new Reference('doctrine.orm.entity_manager'))
        ));

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
                            - app.token_authenticator

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
                        <authenticator>app.token_authenticator</authenticator>
                    </guard>

                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main'       => array(
                    'pattern'        => '^/',
                    'anonymous'      => true,
                    'logout'         => true,
                    'guard'          => array(
                        'authenticators'  => array(
                            'app.token_authenticator'
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

**getCredentials(Request $request)**
    This will be called on *every* request and your job is to read the token (or
    whatever your "authentication" information is) from the request and return it.
    If you return ``null``, the rest of the authentication process is skipped. Otherwise,
    ``getUser()`` will be called and the return value is passed as the first argument.

**getUser($credentials, UserProviderInterface $userProvider)**
    If ``getCredentials()`` returns a non-null value, then this method is called
    and its return value is passed here as the ``$credentials`` argument. Your job
    is to return an object that implements ``UserInterface``. If you do, then
    ``checkCredentials()`` will be called. If you return ``null`` (or throw an
    :ref:`AuthenticationException <guard-customize-error>`)
    authentication will fail.

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
    but no authentication details were sent (i.e. you returned ``null`` from
    ``getCredentials()``). Your job is to return a
    :class:`Symfony\\Component\\HttpFoundation\\Response` object that helps
    the user authenticate (e.g. a 401 response that says "token is missing!").

**supportsRememberMe**
    If you want to support "remember me" functionality, return true from this method.
    You will still need to active ``remember_me`` under your firewall for it to work.
    Since this is a stateless API, you do not want to support "remember me"
    functionality in this example.

.. _guard-customize-error:

Customizing Error Messages
--------------------------

When ``onAuthenticationFailure()`` is called, it is passed an ``AuthenticationException``
that describes *how* authentication failed via its ``$e->getMessageKey()`` (and
``$e->getMessageData()``) method. The message will be different based on *where*
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

Frequently Asked Questions
--------------------------

**Can I have Multiple Authenticators?**
    Yes! But when you do, you'll need choose just *one* authenticator to be your
    "entry_point". This means you'll need to choose *which* authenticator's ``start()``
    method should be called when an anonymous user tries to access a protected resource.
    For example, suppose you have an ``app.form_login_authenticator`` that handles
    a traditional form login. When a user accesses a protected page anonymously, you
    want to use the ``start()`` method from the form authenticator and redirect them
    to the login page (instead of returning a JSON response):

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
                                - app.token_authenticator

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
                            <authenticator>app.token_authenticator</authenticator>
                        </guard>

                        <!-- ... -->
                    </firewall>
                </config>
            </srv:container>

        .. code-block:: php

            // app/config/security.php

            // ..

            $container->loadFromExtension('security', array(
                'firewalls' => array(
                    'main'       => array(
                        'pattern'        => '^/',
                        'anonymous'      => true,
                        'logout'         => true,
                        'guard'          => array(
                            'authenticators'  => array(
                                'app.token_authenticator'
                            ),
                        ),
                        // ...
                    ),
                ),
            ));

**Can I use this with ``form_login``?**
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
