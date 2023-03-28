.. index::
    single: Security; Configuration reference

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

.. note::

    When using XML, you must use the ``http://symfony.com/schema/dic/security``
    namespace and the related XSD schema is available at:
    ``https://symfony.com/schema/dic/services/services-1.0.xsd``

Configuration
-------------

**Basic Options**:

* `access_denied_url`_
* `erase_credentials`_
* `hide_user_not_found`_
* `session_fixation_strategy`_

**Advanced Options**:

Some of these options define tens of sub-options and they are explained in
separate articles:

* `access_control`_
* :ref:`hashers <passwordhasher-supported-algorithms>`
* `firewalls`_
* `providers`_
* `role_hierarchy`_

access_denied_url
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Defines the URL where the user is redirected after a ``403`` HTTP error (unless
you define a custom access denial handler). Example: ``/no-permission``

erase_credentials
~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If ``true``, the ``eraseCredentials()`` method of the user object is called
after authentication.

hide_user_not_found
~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If ``true``, when a user is not found a generic exception of type
:class:`Symfony\\Component\\Security\\Core\\Exception\\BadCredentialsException`
is thrown with the message "Bad credentials".

If ``false``, the exception thrown is of type
:class:`Symfony\\Component\\Security\\Core\\Exception\\UserNotFoundException`
and it includes the given not found user identifier.

session_fixation_strategy
~~~~~~~~~~~~~~~~~~~~~~~~~

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <!-- 'pattern' is a regular expression matched against the incoming
                     request URL. If there's a match, authentication is triggered -->
                <firewall name="main" pattern="^/admin">
                    <!-- the rest of options depend on the authentication mechanism -->
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            // 'main' is the name of the firewall (can be chosen freely)
            $security->firewall('main')
                // 'pattern' is a regular expression matched against the incoming
                // request URL. If there's a match, authentication is triggered
                ->pattern('^/admin')
                // the rest of options depend on the authentication mechanism
                // ...
            ;
        };

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
you may create a redirect loop.

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
(it checks that the request content-type is ``application/x-www-form-urlencoded``).
This is useful for example to prevent the :ref:`form login authenticator <security-form-login>`
from responding to requests that should be handled by the
:ref:`JSON login authenticator <security-json-login>`.

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

**Options Related to Logout Configuration**

delete_cookies
~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Lists the names (and other optional features) of the cookies to delete when the
user logs out::

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <logout path="...">
                        <delete-cookie name="cookie1-name"/>
                        <delete-cookie name="cookie2-name" path="/"/>
                        <delete-cookie name="cookie3-name" domain="example.com"/>
                    </logout>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...
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
        ]);

invalidate_session
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

By default, when users log out from any firewall, their sessions are invalidated.
This means that logging out from one firewall automatically logs them out from
all the other firewalls.

The ``invalidate_session`` option allows to redefine this behavior. Set this
option to ``false`` in every firewall and the user will only be logged out from
the current firewall and not the other ones.

``path``
~~~~~~~~

**type**: ``string`` **default**: ``/logout``

The path which triggers logout. You need to set up a route with a matching path.

target
~~~~~~

**type**: ``string`` **default**: ``/``

The relative path (if the value starts with ``/``), or absolute URL (if it
starts with ``http://`` or ``https://``) or the route name (otherwise) to
redirect after logout.

.. _reference-security-logout-csrf:

enable_csrf
~~~~~~~~~~~

**type**: ``boolean`` **default**: ``null``

Set this option to ``true`` to enable CSRF protection in the logout process
using Symfony's default CSRF token generator. Set also the ``csrf_token_generator``
option if you need to use a custom CSRF token generator.

.. versionadded:: 6.2

    The ``enable_csrf`` option was introduced in Symfony 6.2.

csrf_parameter
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'_csrf_token'``

The name of the parameter that stores the CSRF token value.

csrf_token_generator
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The ``id`` of the service used to generate the CSRF tokens. Symfony provides a
default service whose ID is ``security.csrf.token_manager``.

csrf_token_id
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'logout'``

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main" lazy="true">
                    <json-login check-path="login"
                        username-path="security.credentials.login"
                        password-path="security.credentials.password"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->lazy(true);
            $mainFirewall->jsonLogin()
                ->checkPath('/login')
                ->usernamePath('security.credentials.login')
                ->passwordPath('security.credentials.password')
            ;
        };

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
                        provider:    your_user_provider
                        user:        SSL_CLIENT_S_DN_Email
                        credentials: SSL_CLIENT_S_DN

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <x509 provider="your_user_provider"
                        user="SSL_CLIENT_S_DN_Email"
                        credentials="SSL_CLIENT_S_DN"
                    />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->x509()
                ->provider('your_user_provider')
                ->user('SSL_CLIENT_S_DN_Email')
                ->credentials('SSL_CLIENT_S_DN')
            ;
        };

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

Symfony identifies the value following ``emailAddress=`` in this parameter.

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main">
                    <remote-user provider="your_user_provider"
                        user="REMOTE_USER"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->remoteUser()
                ->provider('your_user_provider')
                ->user('REMOTE_USER')
            ;
        };

provider
........

**type**: ``string``

The service ID of the user provider that should be used by this
authenticator.

user
....

**type**: ``string`` **default**: ``REMOTE_USER``

The name of the ``$_SERVER`` parameter holding the user identifier.

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="somename" context="my_context">
                    <!-- ... -->
                </firewall>
                <firewall name="othername" context="my_context">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $security->firewall('somename')
                // ...
                ->context('my_context')
            ;

            $security->firewall('othername')
                // ...
                ->context('my_context')
            ;
        };

.. note::

    The firewall context key is stored in session, so every firewall using it
    must set its ``stateless`` option to ``false``. Otherwise, the context is
    ignored and you won't be able to authenticate on multiple firewalls at the
    same time.

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <firewall name="main" stateless="true">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            $mainFirewall = $security->firewall('main');
            $mainFirewall->stateless(true);
            // ...
        };

User Checkers
~~~~~~~~~~~~~

During the authentication of a user, additional checks might be required to
verify if the identified user is allowed to log in. Each firewall can include
a ``user_checker`` option to define the service used to perform those checks.

Learn more about user checkers in :doc:`/security/user_checkers`.

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

.. _`Session Fixation`: https://owasp.org/www-community/attacks/Session_fixation
