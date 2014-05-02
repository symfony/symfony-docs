.. index::
    single: Security; Custom Request Authenticator

How to Authenticate Users with API Keys
=======================================

Nowadays, it's quite usual to authenticate the user via an API key (when developing
a web service for instance). The API key is provided for every request and is
passed as a query string parameter or via an HTTP header.

The API Key Authenticator
-------------------------

.. versionadded:: 2.4
    The ``SimplePreAuthenticatorInterface`` interface was introduced in Symfony 2.4.

Authenticating a user based on the Request information should be done via a
pre-authentication mechanism. The :class:`Symfony\\Component\\Security\\Core\\Authentication\\SimplePreAuthenticatorInterface`
allows you to implement such a scheme really easily.

Your exact situation may differ, but in this example, a token is read
from an ``apikey`` query parameter, the proper username is loaded from that
value and then a User object is created::

    // src/Acme/HelloBundle/Security/ApiKeyAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\BadCredentialsException;

    class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
    {
        protected $userProvider;

        public function __construct(ApiKeyUserProvider $userProvider)
        {
            $this->userProvider = $userProvider;
        }

        public function createToken(Request $request, $providerKey)
        {
            if (!$request->query->has('apikey')) {
                throw new BadCredentialsException('No API key found');
            }

            return new PreAuthenticatedToken(
                'anon.',
                $request->query->get('apikey'),
                $providerKey
            );
        }

        public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
        {
            $apiKey = $token->getCredentials();
            $username = $this->userProvider->getUsernameForApiKey($apiKey);

            if (!$username) {
                throw new AuthenticationException(
                    sprintf('API Key "%s" does not exist.', $apiKey)
                );
            }

            $user = $this->userProvider->loadUserByUsername($username);

            return new PreAuthenticatedToken(
                $user,
                $apiKey,
                $providerKey,
                $user->getRoles()
            );
        }

        public function supportsToken(TokenInterface $token, $providerKey)
        {
            return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
        }
    }

Once you've :ref:`configured <cookbook-security-api-key-config>` everything,
you'll be able to authenticate by adding an apikey parameter to the query
string, like ``http://example.com/admin/foo?apikey=37b51d194a7513e45b56f6524f2d51f2``.

The authentication process has several steps, and your implementation will
probably differ:

1. createToken
~~~~~~~~~~~~~~

Early in the request cycle, Symfony calls ``createToken()``. Your job here
is to create a token object that contains all of the information from the
request that you need to authenticate the user (e.g. the ``apikey`` query
parameter). If that information is missing, throwing a
:class:`Symfony\\Component\\Security\\Core\\Exception\\BadCredentialsException`
will cause authentication to fail.

2. supportsToken
~~~~~~~~~~~~~~~~

.. include:: _supportsToken.rst.inc

3. authenticateToken
~~~~~~~~~~~~~~~~~~~~

If ``supportsToken()`` returns ``true``, Symfony will now call ``authenticateToken()``.
One key part is the ``$userProvider``, which is an external class that helps
you load information about the user. You'll learn more about this next.

In this specific example, the following things happen in ``authenticateToken()``:

#. First, you use the ``$userProvider`` to somehow look up the ``$username`` that
   corresponds to the ``$apiKey``;
#. Second, you use the ``$userProvider`` again to load or create a ``User``
   object for the ``$username``;
#. Finally, you create an *authenticated token* (i.e. a token with at least one
   role) that has the proper roles and the User object attached to it.

The goal is ultimately to use the ``$apiKey`` to find or create a ``User``
object. *How* you do this (e.g. query a database) and the exact class for
your ``User`` object may vary. Those differences will be most obvious in your
user provider.

The User Provider
~~~~~~~~~~~~~~~~~

The ``$userProvider`` can be any user provider (see :doc:`/cookbook/security/custom_provider`).
In this example, the ``$apiKey`` is used to somehow find the username for
the user. This work is done in a ``getUsernameForApiKey()`` method, which
is created entirely custom for this use-case (i.e. this isn't a method that's
used by Symfony's core user provider system).

The ``$userProvider`` might look something like this::

    // src/Acme/HelloBundle/Security/ApiKeyUserProvider.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\User;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

    class ApiKeyUserProvider implements UserProviderInterface
    {
        public function getUsernameForApiKey($apiKey)
        {
            // Look up the username based on the token in the database, via
            // an API call, or do something entirely different
            $username = ...;

            return $username;
        }

        public function loadUserByUsername($username)
        {
            return new User(
                $username,
                null,
                // the roles for the user - you may choose to determine
                // these dynamically somehow based on the user
                array('ROLE_USER')
            );
        }

        public function refreshUser(UserInterface $user)
        {
            // this is used for storing authentication in the session
            // but in this example, the token is sent in each request,
            // so authentication can be stateless. Throwing this exception
            // is proper to make things stateless
            throw new UnsupportedUserException();
        }

        public function supportsClass($class)
        {
            return 'Symfony\Component\Security\Core\User\User' === $class;
        }
    }

.. note::

    Read the dedicated article to learn
    :doc:`how to create a custom user provider </cookbook/security/custom_provider>`.

The logic inside ``getUsernameForApiKey()`` is up to you. You may somehow transform
the API key (e.g. ``37b51d``) into a username (e.g. ``jondoe``) by looking
up some information in a "token" database table.

The same is true for ``loadUserByUsername()``. In this example, Symfony's core
:class:`Symfony\\Component\\Security\\Core\\User\\User` class is simply created.
This makes sense if you don't need to store any extra information on your
User object (e.g. ``firstName``). But if you do, you may instead have your *own*
user class which you create and populate here by querying a database. This
would allow you to have custom data on the ``User`` object.

Finally, just make sure that ``supportsClass()`` returns ``true`` for User
objects with the same class as whatever user you return in ``loadUserByUsername()``.
If your authentication is stateless like in this example (i.e. you expect
the user to send the API key with every request and so you don't save the
login to the session), then you can simply throw the ``UnsupportedUserException``
exception in ``refreshUser()``.

.. note::

    If you *do* want to store authentication data in the session so that
    the key doesn't need to be sent on every request, see :ref:`cookbook-security-api-key-session`.

Handling Authentication Failure
-------------------------------

In order for your ``ApiKeyAuthentication`` to correctly display a 403
http status when either bad credentials or authentication fails you will
need to implement the :class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface` on your
Authenticator. This will provide a method ``onAuthenticationFailure`` which
you can use to create an error ``Response``.

.. code-block:: php

    // src/Acme/HelloBundle/Security/ApiKeyAuthenticator.php
    namespace Acme\HelloBundle\Security;

    use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;

    class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
    {
        //...

        public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
        {
            return new Response("Authentication Failed.", 403);
        }
    }

.. _cookbook-security-api-key-config:

Configuration
-------------

Once you have your ``ApiKeyAuthentication`` all setup, you need to register
it as a service and use it in your security configuration (e.g. ``security.yml``).
First, register it as a service. This assumes that you have already setup
your custom user provider as a service called ``your_api_key_user_provider``
(see :doc:`/cookbook/security/custom_provider`).

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...

            apikey_authenticator:
                class:     Acme\HelloBundle\Security\ApiKeyAuthenticator
                arguments: ["@your_api_key_user_provider"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="apikey_authenticator"
                    class="Acme\HelloBundle\Security\ApiKeyAuthenticator"
                >
                    <argument type="service" id="your_api_key_user_provider" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...

        $container->setDefinition('apikey_authenticator', new Definition(
            'Acme\HelloBundle\Security\ApiKeyAuthenticator',
            array(new Reference('your_api_key_user_provider'))
        ));

Now, activate it in the ``firewalls`` section of your security configuration
using the ``simple_preauth`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                secured_area:
                    pattern: ^/admin
                    stateless: true
                    simple_preauth:
                        authenticator: apikey_authenticator

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

                <firewall name="secured_area"
                    pattern="^/admin"
                    stateless="true"
                >
                    <simple-preauth authenticator="apikey_authenticator" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'       => array(
                    'pattern'        => '^/admin',
                    'stateless'      => true,
                    'simple_preauth' => array(
                        'authenticator'  => 'apikey_authenticator',
                    ),
                ),
            ),
        ));

That's it! Now, your ``ApiKeyAuthentication`` should be called at the beginning
of each request and your authentication process will take place.

The ``stateless`` configuration parameter prevents Symfony from trying to
store the authentication information in the session, which isn't necessary
since the client will send the ``apikey`` on each request. If you *do* need
to store authentication in the session, keep reading!

.. _cookbook-security-api-key-session:

Storing Authentication in the Session
-------------------------------------

So far, this entry has described a situation where some sort of authentication
token is sent on every request. But in some situations (like an OAuth flow),
the token may be sent on only *one* request. In this case, you will want to
authenticate the user and store that authentication in the session so that
the user is automatically logged in for every subsequent request.

To make this work, first remove the ``stateless`` key from your firewall
configuration or set it to ``false``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                secured_area:
                    pattern: ^/admin
                    stateless: false
                    simple_preauth:
                        authenticator: apikey_authenticator

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

                <firewall name="secured_area"
                    pattern="^/admin"
                    stateless="false"
                >
                    <simple-preauth authenticator="apikey_authenticator" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ..
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area'       => array(
                    'pattern'        => '^/admin',
                    'stateless'      => false,
                    'simple_preauth' => array(
                        'authenticator'  => 'apikey_authenticator',
                    ),
                ),
            ),
        ));

Storing authentication information in the session works like this:

#. At the end of each request, Symfony serializes the token object (returned
   from ``authenticateToken()``), which also serializes the ``User`` object
   (since it's set on a property on the token);
#. On the next request the token is deserialized and the deserialized ``User``
   object is passed to the ``refreshUser()`` function of the user provider.

The second step is the important one: Symfony calls ``refreshUser()`` and passes
you the user object that was serialized in the session. If your users are
stored in the database, then you may want to re-query for a fresh version
of the user to make sure it's not out-of-date. But regardless of your requirements,
``refreshUser()`` should now return the User object::

    // src/Acme/HelloBundle/Security/ApiKeyUserProvider.php

    // ...
    class ApiKeyUserProvider implements UserProviderInterface
    {
        // ...

        public function refreshUser(UserInterface $user)
        {
            // $user is the User that you set in the token inside authenticateToken()
            // after it has been deserialized from the session

            // you might use $user to query the database for a fresh user
            // $id = $user->getId();
            // use $id to make a query

            // if you are *not* reading from a database and are just creating
            // a User object (like in this example), you can just return it
            return $user;
        }
    }

.. note::

    You'll also want to make sure that your ``User`` object is being serialized
    correctly. If your ``User`` object has private properties, PHP can't serialize
    those. In this case, you may get back a User object that has a ``null``
    value for each property. For an example, see :doc:`/cookbook/security/entity_provider`.

Only Authenticating for Certain URLs
------------------------------------

This entry has assumed that you want to look for the ``apikey`` authentication
on *every* request. But in some situations (like an OAuth flow), you only
really need to look for authentication information once the user has reached
a certain URL (e.g. the redirect URL in OAuth).

Fortunately, handling this situation is easy: just check to see what the
current URL is before creating the token in ``createToken()``::

    // src/Acme/HelloBundle/Security/ApiKeyAuthenticator.php

    // ...
    use Symfony\Component\Security\Http\HttpUtils;
    use Symfony\Component\HttpFoundation\Request;

    class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
    {
        protected $userProvider;

        protected $httpUtils;

        public function __construct(ApiKeyUserProviderInterface $userProvider, HttpUtils $httpUtils)
        {
            $this->userProvider = $userProvider;
            $this->httpUtils = $httpUtils;
        }

        public function createToken(Request $request, $providerKey)
        {
            // set the only URL where we should look for auth information
            // and only return the token if we're at that URL
            $targetUrl = '/login/check';
            if (!$this->httpUtils->checkRequestPath($request, $targetUrl)) {
                return;
            }

            // ...
        }
    }

This uses the handy :class:`Symfony\\Component\\Security\\Http\\HttpUtils`
class to check if the current URL matches the URL you're looking for. In this
case, the URL (``/login/check``) has been hardcoded in the class, but you
could also inject it as the third constructor argument.

Next, just update your service configuration to inject the ``security.http_utils``
service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...

            apikey_authenticator:
                class:     Acme\HelloBundle\Security\ApiKeyAuthenticator
                arguments: ["@your_api_key_user_provider", "@security.http_utils"]

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <!-- ... -->

                <service id="apikey_authenticator"
                    class="Acme\HelloBundle\Security\ApiKeyAuthenticator"
                >
                    <argument type="service" id="your_api_key_user_provider" />
                    <argument type="service" id="security.http_utils" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        // ...

        $container->setDefinition('apikey_authenticator', new Definition(
            'Acme\HelloBundle\Security\ApiKeyAuthenticator',
            array(
                new Reference('your_api_key_user_provider'),
                new Reference('security.http_utils')
            )
        ));

That's it! Have fun!
