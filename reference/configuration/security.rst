Security Configuration Reference (SecurityBundle)
=================================================

The SecurityBundle integrates the :doc:`Security component </security>`
in Symfony applications. All these options are configured under the ``security``
key in your application configuration.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference security

    # displays the actual config values used by your application
    $ php bin/console debug:config security

**Basic Options**:

* `access_denied_url`_
* `expose_security_errors`_
* `session_fixation_strategy`_

**Advanced Options**:

Some of these options define tens of sub-options and they are explained in
separate articles:

* `access_decision_manager`_
* `access_control`_
* `password_hashers`_
* `firewalls`_
* `providers`_
* `role_hierarchy`_

access_denied_url
-----------------

**type**: ``string`` **default**: ``null``

Defines the URL where the user is redirected after a ``403`` HTTP error (unless
you define a custom access denial handler). Example: ``/no-permission``

expose_security_errors
----------------------

**type**: ``string`` **default**: ``'none'``

User enumeration is a common security issue where attackers infer valid usernames
based on error messages. For example, a message like "This user does not exist"
shown by your login form reveals whether a username exists.

This option lets you hide some or all errors related to user accounts
(e.g. blocked or expired accounts) to prevent this issue. Instead, these
errors will trigger a generic ``BadCredentialsException``. The value of this
option can be one of the following:

* ``'none'``: hides all user-related security exceptions;
* ``'account_status'``: shows account-related exceptions (e.g. blocked or expired
  accounts) but only for users who provided the correct password;
* ``'all'``: shows all security-related exceptions.

session_fixation_strategy
-------------------------

**type**: ``string`` **default**: ``SessionAuthenticationStrategy::MIGRATE``

`Session Fixation`_ is a security attack that permits an attacker to hijack a
valid user session. Applications that don't assign new session IDs when
authenticating users are vulnerable to this attack.

The possible values of this option are:

* ``NONE`` constant from :class:`Symfony\\Component\\Security\\Http\\Session\\SessionAuthenticationStrategy`
  Don't change the session after authentication. This is **not recommended**.
* ``MIGRATE`` constant from :class:`Symfony\\Component\\Security\\Http\\Session\\SessionAuthenticationStrategy`
  The session ID is updated, but the rest of session attributes are kept.
* ``INVALIDATE`` constant from :class:`Symfony\\Component\\Security\\Http\\Session\\SessionAuthenticationStrategy`
  The entire session is regenerated, so the session ID is updated but all the
  other session attributes are lost.

erase_credentials
-----------------

**type**: ``boolean`` **default**: ``true``

.. deprecated:: 8.1

    The ``erase_credentials`` option is deprecated since Symfony 8.1 and will
    be removed in 9.0, as the feature behind it was removed in Symfony 8.0.

If ``true``, the ``eraseCredentials()`` method of the user object was called
after authentication. This was used to remove sensitive data (e.g. plain-text
passwords) from the user object stored in the session.

Since Symfony 8.0 removed the ``eraseCredentials()`` method from the user
interface, this option no longer has any effect. You should remove it from
your security configuration to avoid the deprecation warning.

access_decision_manager
-----------------------

Configures the access decision manager used by the security system to make
authorization decisions (e.g. when ``is_granted()`` is called):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            access_decision_manager:
                strategy: affirmative
                allow_if_all_abstain: false
                allow_if_equal_granted_denied: true

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'access_decision_manager' => [
                    'strategy' => 'affirmative',
                    'allow_if_all_abstain' => false,
                    'allow_if_equal_granted_denied' => true,
                ],
            ],
        ]);

.. seealso::

    For a detailed explanation of access decision strategies and custom
    voters, see :doc:`/security/voters`.

strategy
........

**type**: ``string`` **default**: ``affirmative``

Defines the strategy used by the access decision manager to decide whether
access should be granted. Read about :ref:`all the available strategies <security-voters-change-strategy>`.

allow_if_all_abstain
....................

**type**: ``boolean`` **default**: ``false``

If ``true``, access is granted when all :doc:`voters </security/voters>`
abstain (no voter explicitly grants or denies access). If ``false``
(the default), access is denied when all voters abstain.

allow_if_equal_granted_denied
.............................

**type**: ``boolean`` **default**: ``true``

This option is only used by the ``consensus`` strategy. If the number of
:doc:`voters </security/voters>` granting access is equal to the number of
voters denying access, this option determines the final decision. If ``true``
(the default), access is granted in case of a tie.

service
.......

**type**: ``string`` **default**: ``null``

Defines a :ref:`custom access decision manager <security-custom-access-decision-manager>`
service to replace the default one entirely. The service must implement the
:class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManagerInterface`.

When this option is set, the ``strategy``, ``allow_if_all_abstain`` and
``allow_if_equal_granted_denied`` options are ignored.

strategy_service
................

**type**: ``string`` **default**: ``null``

Defines a :ref:`custom strategy service <security-custom-access-decision-strategy>`
to use instead of one of the built-in strategies. The service must implement the
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Strategy\\AccessDecisionStrategyInterface`.

access_control
--------------

Defines the security protection of the URLs of your application. It's used for
example to trigger the user authentication when trying to access to the backend
and to allow unauthenticated users to the login form page.

This option is explained in detail in :doc:`/security/access_control`.

firewalls
---------

This is arguably the most important option of the security config file. It
defines the authentication mechanism used for each URL (or URL pattern) of your
application:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                # 'main' is the name of the firewall (can be chosen freely)
                main:
                    # 'pattern' is a regular expression matched against the incoming
                    # request URL. If there's a match, authentication is triggered
                    pattern: ^/admin
                    # the rest of options depend on the authentication mechanism
                    # ...

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...

                // 'main' is the name of the firewall (can be chosen freely)
                'firewalls' => [
                    'main' => [
                        // 'pattern' is a regular expression matched against the incoming
                        // request URL. If there's a match, authentication is triggered
                        'pattern' => '^/admin',
                        // the rest of options depend on the authentication mechanism
                        // ...
                    ],
                ],
            ],
        ]);

.. seealso::

    Read :doc:`this article </security/firewall_restriction>` to learn about how
    to restrict firewalls by host and HTTP methods.

In addition to some common config options, the most important firewall options
depend on the authentication mechanism, which can be any of these:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...
        firewalls:
            main:
                # ...
                    x509:
                        # ...
                    remote_user:
                        # ...
                    guard:
                        # ...
                    form_login:
                        # ...
                    form_login_ldap:
                        # ...
                    json_login:
                        # ...
                    http_basic:
                        # ...
                    http_basic_ldap:
                        # ...
                    http_digest:
                        # ...
                    access_token:
                        # ...
                    login_link:
                        # ...
                    login_throttling:
                        # ...
                    remember_me:
                        # ...

You can view actual information about the firewalls in your application with
the ``debug:firewall`` command:

.. code-block:: terminal

    # displays a list of firewalls currently configured for your application
    $ php bin/console debug:firewall

    # displays the details of a specific firewall
    $ php bin/console debug:firewall main

    # displays the details of a specific firewall, including detailed information
    # about the event listeners for the firewall
    $ php bin/console debug:firewall main --events

.. _reference-security-firewall-form-login:

``form_login`` Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the ``form_login`` authentication listener beneath a firewall,
there are several common options for configuring the "form login" experience.
For even more details, see :doc:`/security/form_login`.

login_path
..........

**type**: ``string`` **default**: ``/login``

This is the route or path that the user will be redirected to (unless ``use_forward``
is set to ``true``) when they try to access a protected resource but aren't
fully authenticated.

This path **must** be accessible by a normal, unauthenticated user, else
you might create a redirect loop.

check_path
..........

**type**: ``string`` **default**: ``/login_check``

This is the route or path that your login form must submit to. The firewall
will intercept any requests (``POST`` requests only, by default) to this
URL and process the submitted login credentials.

Be sure that this URL is covered by your main firewall (i.e. don't create
a separate firewall just for ``check_path`` URL).

failure_path
............

**type**: ``string`` **default**: ``/login``

This is the route or path that the user is redirected to after a failed login attempt.
It can be a relative/absolute URL or a Symfony route name.

form_only
.........

**type**: ``boolean`` **default**: ``false``

Set this option to ``true`` to require that the login data is sent using a form
(it checks that the request content-type is ``application/x-www-form-urlencoded``
or ``multipart/form-data``). This is useful for example to prevent the
:ref:`form login authenticator <security-form-login>` from responding to
requests that should be handled by the :ref:`JSON login authenticator <security-json-login>`.

use_forward
...........

**type**: ``boolean`` **default**: ``false``

If you'd like the user to be forwarded to the login form instead of being
redirected, set this option to ``true``.

username_parameter
..................

**type**: ``string`` **default**: ``_username``

This is the name of the username field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

password_parameter
..................

**type**: ``string`` **default**: ``_password``

This is the name of the password field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

post_only
.........

**type**: ``boolean`` **default**: ``true``

By default, you must submit your login form to the ``check_path`` URL as
a POST request. By setting this option to ``false``, you can send a GET
request too.

**Options Related to Redirecting after Login**

always_use_default_target_path
..............................

**type**: ``boolean`` **default**: ``false``

If ``true``, users are always redirected to the default target path regardless
of the previous URL that was stored in the session.

default_target_path
...................

**type**: ``string`` **default**: ``/``

The page users are redirected to when there is no previous page stored in the
session (for example, when the users browse the login page directly).

target_path_parameter
.....................

**type**: ``string`` **default**: ``_target_path``

When using a login form, if you include an HTML element to set the target path,
this option lets you change the name of the HTML element itself.

failure_path_parameter
......................

**type**: ``string`` **default**: ``_failure_path``

When using a login form, if you include an HTML element to set the failure path,
this option lets you change the name of the HTML element itself.

use_referer
...........

**type**: ``boolean`` **default**: ``false``

If ``true``, the user is redirected to the value stored in the ``HTTP_REFERER``
header when no previous URL was stored in the session. If the referrer URL is
the same as the one generated with the ``login_path`` route, the user is
redirected to the ``default_target_path`` to avoid a redirection loop.

.. note::

    For historical reasons, and to match the misspelling of the HTTP standard,
    the option is called ``use_referer`` instead of ``use_referrer``.

logout
~~~~~~

You can configure logout options.

delete_cookies
..............

**type**: ``array`` **default**: ``[]``

Lists the names (and other optional features) of the cookies to delete when the
user logs out:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    logout:
                        delete_cookies:
                            cookie1-name: null
                            cookie2-name:
                                path: '/'
                            cookie3-name:
                                path: null
                                domain: example.com

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'logout' => [
                            'delete_cookies' => [
                                'cookie1-name' => null,
                                'cookie2-name' => [
                                    'path' => '/',
                                ],
                                'cookie3-name' => [
                                    'path' => null,
                                    'domain' => 'example.com',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

clear_site_data
...............

**type**: ``array`` **default**: ``[]``

The ``Clear-Site-Data`` HTTP header clears browsing data (cookies, storage, cache)
associated with the requesting website. It allows web developers to have more
control over the data stored by a client browser for their origins.

Allowed values are ``cache``, ``cookies``, ``storage``, ``clientHints``, ``executionContexts``,
``prefetchCache`` and ``prerenderCache``.
It's also possible to use ``*`` as a wildcard for all directives:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    logout:
                        clear_site_data:
                            - cookies
                            - storage

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'logout' => [
                            'clear_site_data' => ['cookies', 'storage'],
                        ],
                    ],
                ],
            ],
        ]);

.. versionadded:: 8.1

    The ``clientHints``, ``prefetchCache`` and ``prerenderCache`` options were
    introduced in Symfony 8.1.

invalidate_session
..................

**type**: ``boolean`` **default**: ``true``

By default, when users log out from any firewall, their sessions are invalidated.
This means that logging out from one firewall automatically logs them out from
all the other firewalls.

The ``invalidate_session`` option allows you to redefine this behavior. Set this
option to ``false`` in every firewall and the user will only be logged out from
the current firewall and not the other ones.

``path``
........

**type**: ``string`` **default**: ``/logout``

The path or route name that triggers the logout. If the value starts with a
``/`` character, it's treated as a path; otherwise, it's treated as a route
name. If you use a path, make sure to also define a route that matches it.

target
......

**type**: ``string`` **default**: ``/``

The relative path (if the value starts with ``/``), or absolute URL (if it
starts with ``http://`` or ``https://``) or the route name (otherwise) to
redirect after logout.

.. _reference-security-logout-csrf:

enable_csrf
...........

**type**: ``boolean`` **default**: ``null``

Set this option to ``true`` to enable CSRF protection in the logout process
using Symfony's default CSRF token manager. Set also the ``csrf_token_manager``
option if you need to use a custom CSRF token manager.

csrf_parameter
..............

**type**: ``string`` **default**: ``_csrf_token``

The name of the parameter that stores the CSRF token value.

csrf_token_manager
..................

**type**: ``string`` **default**: ``null``

The ``id`` of the service used to generate the CSRF tokens. Symfony provides a
default service whose ID is ``security.csrf.token_manager``.

csrf_token_id
.............

**type**: ``string`` **default**: ``logout``

An arbitrary string used to identify the token (and check its validity afterwards).

.. _reference-security-firewall-json-login:

JSON Login Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~

check_path
..........

**type**: ``string`` **default**: ``/login_check``

This is the URL or route name the system must post to authenticate using
the JSON authenticator. The path must be covered by the firewall to which
the user will authenticate.

username_path
.............

**type**: ``string`` **default**: ``username``

Use this and ``password_path`` to modify the expected request body
structure of the JSON authenticator. For instance, if the JSON document has
the following structure:

.. code-block:: json

    {
        "security": {
            "credentials": {
                "login": "dunglas",
                "password": "MyPassword"
            }
        }
    }

The security configuration should be:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    lazy: true
                    json_login:
                        check_path:    login
                        username_path: security.credentials.login
                        password_path: security.credentials.password

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'lazy' => true,
                        'json_login' => [
                            'check_path' => '/login',
                            'username_path' => 'security.credentials.login',
                            'password_path' => 'security.credentials.password',
                        ],
                    ],
                ],
            ],
        ]);

password_path
.............

**type**: ``string`` **default**: ``password``

Use this option to modify the expected request body structure. See
`username_path`_ for more details.

.. _reference-security-ldap:

LDAP Authentication
~~~~~~~~~~~~~~~~~~~

There are several options for connecting against an LDAP server,
using the ``form_login_ldap``, ``http_basic_ldap`` and ``json_login_ldap`` authentication
providers or the ``ldap`` user provider.

For even more details, see :doc:`/security/ldap`.

**Authentication**

You can authenticate to an LDAP server using the LDAP variants of the
``form_login``, ``http_basic`` and ``json_login`` authentication providers. Use
``form_login_ldap``, ``http_basic_ldap`` and ``json_login_ldap``, which will
attempt to ``bind`` against an LDAP server instead of using password comparison.

Both authentication providers have the same arguments as their normal
counterparts, with the addition of two configuration keys:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client.

dn_string
.........

**type**: ``string`` **default**: ``{user_identifier}``

This is the string which will be used as the bind DN. The ``{user_identifier}``
placeholder will be replaced with the user-provided value (their login).
Depending on your LDAP server's configuration, you may need to override
this value.

query_string
............

**type**: ``string`` **default**: ``null``

This is the string which will be used to query for the DN. The ``{user_identifier}``
placeholder will be replaced with the user-provided value (their login).
Depending on your LDAP server's configuration, you will need to override
this value. This setting is only necessary if the user's DN cannot be derived
statically using the ``dn_string`` config option.

**User provider**

Users will still be fetched from the configured user provider. If you wish to
fetch your users from an LDAP server, you will need to use the
:doc:`LDAP User Provider </security/ldap>` and any of these authentication
providers: ``form_login_ldap`` or ``http_basic_ldap`` or ``json_login_ldap``.

.. _reference-security-firewall-x509:

X.509 Authentication
~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    x509:
                        provider:        your_user_provider
                        user:            SSL_CLIENT_S_DN_Email
                        credentials:     SSL_CLIENT_S_DN
                        user_identifier: emailAddress

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'x509' => [
                            'provider' => 'your_user_provider',
                            'user' => 'SSL_CLIENT_S_DN_Email',
                            'credentials' => 'SSL_CLIENT_S_DN',
                            'user_identifier' => 'emailAddress',
                        ],
                    ],
                ],
            ],
        ]);

user
....

**type**: ``string`` **default**: ``SSL_CLIENT_S_DN_Email``

The name of the ``$_SERVER`` parameter containing the user identifier used
to load the user in Symfony. The default value is exposed by Apache.

credentials
...........

**type**: ``string`` **default**: ``SSL_CLIENT_S_DN``

If the ``user`` parameter is not available, the name of the ``$_SERVER``
parameter containing the full "distinguished name" of the certificate
(exposed by e.g. Nginx).

By default, Symfony identifies the value following ``emailAddress=`` in this
parameter. This can be changed using the ``user_identifier`` option.

user_identifier
...............

**type**: ``string`` **default**: ``emailAddress``

The value of this option tells Symfony which parameter to use to find the user
identifier in the "distinguished name".

For example, if the "distinguished name" is
``Subject: C=FR, O=My Organization, CN=user1, emailAddress=user1@myorg.fr``,
and the value of this option is ``'CN'``, the user identifier will be ``'user1'``.

.. _reference-security-firewall-remote-user:

Remote User Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            firewalls:
                main:
                    # ...
                    remote_user:
                        provider: your_user_provider
                        user:     REMOTE_USER

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'remote_user' => [
                            'provider' => 'your_user_provider',
                            'user' => 'REMOTE_USER',
                        ],
                    ],
                ],
            ],
        ]);

provider
........

**type**: ``string``

The service ID of the user provider that should be used by this
authenticator.

user
....

**type**: ``string`` **default**: ``REMOTE_USER``

The name of the ``$_SERVER`` parameter holding the user identifier.

``access_token`` Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the ``access_token`` authentication mechanism beneath a firewall,
the application authenticates users based on an API token sent with the request.
For even more details, see :doc:`/security/access_token`.

token_handler
.............

**type**: ``string`` | ``array``

The service id of the token handler or an array of options for a built-in
handler (e.g. ``oidc``, ``oidc_user_info``). This option is required.

token_extractors
................

**type**: ``array`` **default**: ``['security.access_token_extractor.header']``

The list of service ids used to extract the token from the request. By default,
the token is extracted from the ``Authorization`` request header.

realm
.....

**type**: ``string`` **default**: ``null``

The "realm" for the ``WWW-Authenticate`` response header returned when
authentication fails.

provider
........

**type**: ``string``

The service id of the user provider that should be used by this authenticator.

success_handler
...............

**type**: ``string``

The service id of a service that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationSuccessHandlerInterface`.

failure_handler
...............

**type**: ``string``

The service id of a service that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`.


``login_link`` Authentication
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the ``login_link`` authentication mechanism beneath a firewall,
users are authenticated via a unique, signed URL sent to them (e.g. by email).
For even more details, see :doc:`/security/login_link`.

check_route
...........

**type**: ``string``

The route name that the login link URL will point to. This option is required.

check_post_only
...............

**type**: ``boolean`` **default**: ``false``

If ``true``, the login link is only accepted via ``POST`` requests.

signature_properties
....................

**type**: ``array``

An array of user properties used to create the login link signature. This option
is required and must contain at least one property (e.g. ``['email']``).

lifetime
........

**type**: ``integer`` **default**: ``600``

The lifetime of the login link in seconds.

max_uses
........

**type**: ``integer`` **default**: ``null``

The maximum number of times a login link can be used. ``null`` means unlimited.

used_link_cache
...............

**type**: ``string``

The service id of the cache pool used to track used login links. This is required
when ``max_uses`` is set.

secret
......

**type**: ``string`` **default**: ``%kernel.secret%``

The secret used to sign the login link URL.

provider
........

**type**: ``string``

The service id of the user provider that should be used by this authenticator.

success_handler
...............

**type**: ``string``

The service id of a service that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationSuccessHandlerInterface`.

failure_handler
...............

**type**: ``string``

The service id of a service that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`.

.. _reference-security-firewall-login-throttling:

``login_throttling``
~~~~~~~~~~~~~~~~~~~~

Login throttling limits the number of failed login attempts over a given period of
time. This helps protect against brute-force attacks. For even more details, see
:doc:`/security` and :doc:`/rate_limiter`.

limiter
.......

**type**: ``string``

The service id of a custom rate limiter implementing
:class:`Symfony\\Component\\HttpFoundation\\RateLimiter\\RequestRateLimiterInterface`.
When set, all other options below are ignored because the provided limiter is used
directly.

max_attempts
............

**type**: ``integer`` **default**: ``5``

The maximum number of failed login attempts before the login is throttled.

interval
........

**type**: ``string`` **default**: ``'1 minute'``

The period of time in which the ``max_attempts`` are counted (e.g. ``'1 minute'``,
``'30 seconds'``).

lock_factory
............

**type**: ``string`` **default**: ``null``

The service id of the lock factory used by the rate limiter. Set to ``null`` to
disable locking.

cache_pool
..........

**type**: ``string`` **default**: ``'cache.rate_limiter'``

The cache pool service id used to store the rate limiter state.

storage_service
...............

**type**: ``string`` **default**: ``null``

The service id of a custom storage service for the rate limiter. When set, this
takes precedence over ``cache_pool``.

.. _reference-security-firewall-remember-me:

``remember_me``
~~~~~~~~~~~~~~~

The "remember me" authentication mechanism allows users to stay authenticated
across browser sessions by storing a special cookie. For even more details, see
:doc:`/security/remember_me`.

secret
......

**type**: ``string`` **default**: ``%kernel.secret%``

The secret used to encode the cookie content. It's common to use the
``kernel.secret`` parameter.

service
.......

**type**: ``string``

The service id of a custom remember-me handler implementing
:class:`Symfony\\Component\\Security\\Http\\RememberMe\\RememberMeHandlerInterface`.

token_provider
..............

**type**: ``string`` | ``array``

A service id (as a string) or an array with the following sub-options:

* ``service`` (**type**: ``string``): the service id of a custom token provider.
* ``doctrine`` (**type**: ``array``): enables the Doctrine token provider with an
  optional ``connection`` sub-option (**type**: ``string``, **default**: ``null``)
  to define the Doctrine connection to use.

token_verifier
..............

**type**: ``string``

The service id of a custom token verifier service.

signature_properties
....................

**type**: ``array`` **default**: ``['password']``

An array of user properties used to verify the remember-me cookie. At least one
property is required. When any of these properties change, existing remember-me
cookies are invalidated automatically.

catch_exceptions
................

**type**: ``boolean`` **default**: ``true``

If ``true``, exceptions thrown during the remember-me authentication are caught
and the user is not authenticated (instead of returning a server error).

**Cookie Options**

name
....

**type**: ``string`` **default**: ``'REMEMBERME'``

The name of the cookie used for the remember-me feature.

lifetime
........

**type**: ``integer`` **default**: ``31536000``

The lifetime of the cookie in seconds (``31536000`` is one year).

path
....

**type**: ``string`` **default**: ``'/'``

The path of the cookie.

domain
......

**type**: ``string`` **default**: ``null``

The domain of the cookie. If ``null``, the current domain from the request
is used.

secure
......

**type**: ``boolean`` | ``string`` **default**: ``false``

If ``true``, the cookie is only sent over HTTPS. Set to ``'auto'`` to use the
same value as the session cookie.

httponly
........

**type**: ``boolean`` **default**: ``true``

If ``true``, the cookie is accessible only through the HTTP protocol (it can't
be accessed by scripting languages like JavaScript).

samesite
........

**type**: ``string`` **default**: ``null``

If set, the ``SameSite`` attribute of the cookie is set with this value. Valid
values are ``'lax'``, ``'strict'`` and ``'none'``.

always_remember_me
..................

**type**: ``boolean`` **default**: ``false``

If ``true``, the remember-me feature is always active, regardless of whether
the user checks a "remember me" checkbox in the login form.

remember_me_parameter
.....................

**type**: ``string`` **default**: ``'_remember_me'``

The name of the form field or request parameter checked to determine whether
the "remember me" feature should be activated.

.. _reference-security-firewall-context:

Firewall Context
~~~~~~~~~~~~~~~~

If your application uses multiple :ref:`firewalls <firewalls-authentication>`, you'll notice that
if you're authenticated in one firewall, you're not automatically authenticated
in another. In other words, the systems don't share a common "context":
each firewall acts like a separate security system.

However, each firewall has an optional ``context`` key (which defaults to
the name of the firewall), which is used when storing and retrieving security
data to and from the session. If this key were set to the same value across
multiple firewalls, the "context" could actually be shared:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                somename:
                    # ...
                    context: my_context
                othername:
                    # ...
                    context: my_context

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'somename' => [
                        'context' => 'my_context',
                    ],
                    'othername' => [
                        'context' => 'my_context',
                    ],
                ],
            ],
        ]);

.. note::

    The firewall context key is stored in session, so every firewall using it
    must set its ``stateless`` option to ``false``. Otherwise, the context is
    ignored and you won't be able to authenticate on multiple firewalls at the
    same time.

.. _reference-security-stateless:

stateless
~~~~~~~~~

Firewalls can configure a ``stateless`` boolean option in order to declare that
the session must not be used when authenticating users:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    stateless: true

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'stateless' => true,
                    ],
                ],
            ],
        ]);

.. _reference-security-lazy:

lazy
~~~~

Firewalls can configure a ``lazy`` boolean option to load the user and start the
session only if the application actually accesses the User object, (e.g. calling
``is_granted()`` in a template or ``isGranted()`` in a controller or service):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    lazy: true

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'lazy' => true,
                    ],
                ],
            ],
        ]);

User Checkers
~~~~~~~~~~~~~

During the authentication of a user, additional checks might be required to
verify if the identified user is allowed to log in. Each firewall can include
a ``user_checker`` option to define the service used to perform those checks.

Learn more about user checkers in :doc:`/security/user_checkers`.

Required Badges
~~~~~~~~~~~~~~~

Firewalls can configure a list of required badges that must be present on the authenticated passport:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    required_badges: ['CsrfTokenBadge', 'My\Badge']

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'required_badges' => ['CsrfTokenBadge', 'My\Badge'],
                    ],
                ],
            ],
        ]);

password_hashers
----------------

**type**: ``array``

Defines the :doc:`password hasher </security/passwords>` for each user class.
The key of each item is the fully qualified class name of the user class, and
the value defines the hasher configuration. Use ``auto`` as the algorithm to let
Symfony choose the best available algorithm.:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                App\Entity\User: 'auto'

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main');
            // ...

            $security->passwordHasher('App\Entity\User')
                ->algorithm('auto');
        };

Or use the expanded syntax to configure additional options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                App\Entity\User:
                    algorithm: 'auto'
                    cost: 15

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main');
            // ...

            $security->passwordHasher('App\Entity\User')
                ->algorithm('auto')
                ->cost(15);
        };

algorithm
~~~~~~~~~

**type**: ``string``

The hashing algorithm to use. The available algorithms depend on your PHP
installation. The most common values are:

* ``auto``: automatically selects the best available hasher (currently Bcrypt);
* ``bcrypt``: uses the `bcrypt`_ algorithm;
* ``sodium``: uses the Argon2 algorithm via the `libsodium`_ extension;
* ``pbkdf2``: uses the `PBKDF2`_ algorithm (no longer recommended).

You can also set this to the ``id`` of a service to use a custom password hasher
(see the ``id`` option below).

.. seealso::

    Read more about the :ref:`supported hashing algorithms <passwordhasher-supported-algorithms>`.

cost
~~~~

**type**: ``integer`` **default**: ``null``

This option is only available for the ``bcrypt`` algorithm. It defines the
value of the ``cost`` used to hash the password. The allowed range is ``4``
to ``31``. Each single increment of the cost **doubles** the time it takes
to hash a password.

memory_cost
~~~~~~~~~~~

**type**: ``integer`` **default**: ``null``

This option is only available for the ``sodium`` (Argon2) algorithm. It defines
the amount of memory (in KiB) the algorithm may consume.

time_cost
~~~~~~~~~

**type**: ``integer`` **default**: ``null``

This option is only available for the ``sodium`` (Argon2) algorithm. It defines
the number of iterations used to hash the password.

hash_algorithm
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``sha512``

The hash algorithm used by the ``pbkdf2`` hasher. You can use any algorithm
returned by the ``hash_algos()`` PHP function (e.g. ``sha256``, ``sha512``, etc.).

key_length
~~~~~~~~~~

**type**: ``integer`` **default**: ``40``

The length of the generated hash for the ``pbkdf2`` hasher.

iterations
~~~~~~~~~~

**type**: ``integer`` **default**: ``5000``

The number of iterations used by the ``pbkdf2`` hasher to generate the hash.

encode_as_base64
~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Whether to encode the generated hash in base64 for the ``pbkdf2`` hasher.

ignore_case
~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Whether to ignore case when comparing the password for the ``pbkdf2`` hasher.

id
~~

**type**: ``string``

The service id of a custom password hasher. This option can be used to
configure a custom hasher service instead of using a built-in algorithm.

.. seealso::

    Read more about :ref:`creating a custom password hasher <custom-password-hasher>`.

migrate_from
~~~~~~~~~~~~

**type**: ``array``

A list of previous hasher configurations to migrate from. When a password
is verified, Symfony checks if it was hashed with one of these previous
algorithms. If so, the password is automatically rehashed using the
current algorithm the next time the user logs in.

.. seealso::

    Read more about :ref:`password migration <security-password-migration>`.

providers
---------

This option defines how the application users are loaded (from a database,
an LDAP server, a configuration file, etc.) Read
:doc:`/security/user_providers` to learn more about each of those
providers.

role_hierarchy
--------------

Instead of associating many roles to users, this option allows you to define
role inheritance rules by creating a role hierarchy, as explained in
:ref:`security-role-hierarchy`.

.. _`bcrypt`: https://en.wikipedia.org/wiki/Bcrypt
.. _`libsodium`: https://pecl.php.net/package/libsodium
.. _`PBKDF2`: https://en.wikipedia.org/wiki/PBKDF2
.. _`Session Fixation`: https://owasp.org/www-community/attacks/Session_fixation
