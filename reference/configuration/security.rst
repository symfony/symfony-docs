.. index::
    single: Security; Configuration reference

Security Configuration Reference (SecurityBundle)
=================================================

The SecurityBundle integrates the :doc:`Security component </components/security>`
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
* `always_authenticate_before_granting`_
* `erase_credentials`_
* `hide_user_not_found`_
* `session_fixation_strategy`_

**Advanced Options**:

Some of these options define tens of sub-options and they are explained in
separate articles:

* `access_control`_
* `encoders`_
* `firewalls`_
* `providers`_
* `role_hierarchy`_

access_denied_url
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Defines the URL where the user is redirected after a ``403`` HTTP error (unless
you define a custom access deny handler). Example: ``/no-permission``

always_authenticate_before_granting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If ``true``, the user is asked to authenticate before each call to the
``isGranted()`` method in services and controllers or ``is_granted()`` from
templates.

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
:class:`Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException`
and it includes the given not found username.

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
and to allow anonymous users to the login form page.

This option is explained in detail in :doc:`/security/access_control`.

encoders
--------

This option defines the algorithm used to *encode* the password of the users.
Although Symfony calls it *"password encoding"* for historical reasons, this is
in fact, *"password hashing"*.

If your app defines more than one user class, each of them can define its own
encoding algorithm. Also, each algorithm defines different config options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            encoders:
                # bcrypt encoder with default options
                App\Entity\User: 'bcrypt'

                # bcrypt encoder with custom options
                App\Entity\User:
                    algorithm: 'bcrypt'
                    cost:      15

                # Argon2i encoder with default options
                App\Entity\User: 'argon2i'

                # Argon2i encoder with custom options
                App\Entity\User:
                    algorithm:   'argon2i'
                    memory_cost:  16384 # Amount in KiB. (16384 = 16 MiB)
                    time_cost:    2     # Number of iterations
                    threads:      4     # Number of parallel threads

                # PBKDF2 encoder using SHA512 hashing with default options
                App\Entity\User: 'sha512'

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <!-- bcrypt encoder with default options -->
                <encoder
                    class="App\Entity\User"
                    algorithm="bcrypt"
                />

                <!-- bcrypt encoder with custom options -->
                <encoder
                    class="App\Entity\User"
                    algorithm="bcrypt"
                    cost="15"
                />

                <!-- Argon2i encoder with default options -->
                <encoder
                    class="App\Entity\User"
                    algorithm="argon2i"
                />

                <!-- Argon2i encoder with custom options -->
                <!-- memory_cost: amount in KiB. (16384 = 16 MiB)
                     time_cost: number of iterations
                     threads: number of parallel threads -->
                <encoder
                    class="App\Entity\User"
                    algorithm="argon2i"
                    memory_cost="16384"
                    time_cost="2"
                    threads="4"
                />

                <!-- PBKDF2 encoder using SHA512 hashing with default options -->
                <encoder
                    class="App\Entity\User"
                    algorithm="sha512"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', [
            // ...
            'encoders' => [
                // bcrypt encoder with default options
                User::class => [
                    'algorithm' => 'bcrypt',
                ],

                // bcrypt encoder with custom options
                User::class => [
                    'algorithm' => 'bcrypt',
                    'cost'      => 15,
                ],

                // Argon2i encoder with default options
                User::class => [
                    'algorithm' => 'argon2i',
                ],

                // Argon2i encoder with custom options
                User::class => [
                    'algorithm' => 'argon2i',
                    'memory_cost' => 16384, // Amount in KiB. (16384 = 16 MiB)
                    'time_cost' => 2,       // Number of iterations
                    'threads' => 4,         // Number of parallel threads
                ],

                // PBKDF2 encoder using SHA512 hashing with default options
                User::class => [
                    'algorithm' => 'sha512',
                ],
            ],
        ]);

.. tip::

    You can also create your own password encoders as services and you can even
    select a different password encoder for each user instance. Read
    :doc:`this article </security/named_encoders>` for more details.

.. _reference-security-argon2i:

Using the Argon2i Password Encoder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It uses the `Argon2 key derivation function`_ and it's the encoder recommended
by Symfony. Argon2 support was introduced in PHP 7.2, but if you use an earlier
PHP version, you can install the `libsodium`_ PHP extension.

The encoded passwords are ``96`` characters long, but due to the hashing
requirements saved in the resulting hash this may change in the future, so make
sure to allocate enough space for them to be persisted. Also, passwords include
the `cryptographic salt`_ inside them (it's generated automatically for each new
password) so you don't have to deal with it.

.. _reference-security-bcrypt:

Using the BCrypt Password Encoder
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It uses the `bcrypt password hashing function`_ and it's recommended to use it
when it's not possible to use Argon2i. The encoded passwords are ``60``
characters long, so make sure to allocate enough space for them to be persisted.
Also, passwords include the `cryptographic salt`_ inside them (it's generated
automatically for each new password) so you don't have to deal with it.

Its only configuration option is ``cost``, which is an integer in the range of
``4-31`` (by default, ``13``). Each single increment of the cost **doubles the
time** it takes to encode a password. It's designed this way so the password
strength can be adapted to the future improvements in computation power.

You can change the cost at any time — even if you already have some passwords
encoded using a different cost. New passwords will be encoded using the new
cost, while the already encoded ones will be validated using a cost that was
used back when they were encoded.

.. tip::

    A simple technique to make tests much faster when using BCrypt is to set
    the cost to ``4``, which is the minimum value allowed, in the ``test``
    environment configuration.

.. _reference-security-pbkdf2:

Using the PBKDF2 Encoder
~~~~~~~~~~~~~~~~~~~~~~~~

Using the `PBKDF2`_ encoder is no longer recommended since PHP added support for
Argon2i and bcrypt. Legacy application still using it are encouraged to upgrade
to those newer encoding algorithms.

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
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

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

        // ...
        $container->loadFromExtension('security', [
            'firewalls' => [
                // 'main' is the name of the firewall (can be chosen freely)
                'main' => [
                    // 'pattern' is a regular expression matched against the incoming
                    // request URL. If there's a match, authentication is triggered
                    'pattern' => '^/admin',
                    // the rest of options depend on the authentication mechanism
                    // ...
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
                    simple_preauth:
                        # ...
                    guard:
                        # ...
                    form_login:
                        # ...
                    form_login_ldap:
                        # ...
                    json_login:
                        # ...
                    simple_form:
                        # ...
                    http_basic:
                        # ...
                    http_basic_ldap:
                        # ...
                    http_digest:
                        # ...

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
is set to ``true``) when they try to access a protected resource but isn't
fully authenticated.

This path **must** be accessible by a normal, un-authenticated user, else
you may create a redirect loop.

check_path
..........

**type**: ``string`` **default**: ``/login_check``

This is the route or path that your login form must submit to. The firewall
will intercept any requests (``POST`` requests only, by default) to this
URL and process the submitted login credentials.

Be sure that this URL is covered by your main firewall (i.e. don't create
a separate firewall just for ``check_path`` URL).

use_forward
...........

**type**: ``boolean`` **default**: ``false``

If you'd like the user to be forwarded to the login form instead of being
redirected, set this option to ``true``.

username_parameter
..................

**type**: ``string`` **default**: ``_username``

This is the field name that you should give to the username field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

password_parameter
..................

**type**: ``string`` **default**: ``_password``

This is the field name that you should give to the password field of your
login form. When you submit the form to ``check_path``, the security system
will look for a POST parameter with this name.

post_only
.........

**type**: ``boolean`` **default**: ``true``

By default, you must submit your login form to the ``check_path`` URL as
a POST request. By setting this option to ``false``, you can send a GET
request to the ``check_path`` URL.

**Options Related to Redirecting after Login**

always_use_default_target_path
..............................

**type**: ``boolean`` **default**: ``false``

If ``true``, users are always redirected to the default target path regardless
of the previous URL that was stored in the session.

default_target_path
....................

**type**: ``string`` **default**: ``/``

The page users are redirected to when there is no previous page stored in the
session (for example, when the users browse the login page directly).

target_path_parameter
.....................

**type**: ``string`` **default**: ``_target_path``

When using a login form, if you include an HTML element to set the target path,
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

invalidate_session
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

By default, when users log out from any firewall, their sessions are invalidated.
This means that logging out from one firewall automatically logs them out from
all the other firewalls.

The ``invalidate_session`` option allows to redefine this behavior. Set this
option to ``false`` in every firewall and the user will only be logged out from
the current firewall and not the other ones.

logout_on_user_change
~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

.. deprecated:: 4.1

    The ``logout_on_user_change`` option was deprecated in Symfony 4.1.

If ``false`` this option makes Symfony to not trigger a logout when the user has
changed. Doing that is deprecated, so this option should set to ``true`` or
unset to avoid getting deprecation messages.

The user is considered to have changed when the user class implements
:class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface` and the
``isEqualTo()`` method returns ``false``. Also, when any of the properties
required by the :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
(like the username, password or salt) changes.

success_handler
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``'security.logout.success_handler'``

The service ID used for handling a successful logout. The service must implement
:class:`Symfony\\Component\\Security\\Http\\Logout\\LogoutSuccessHandlerInterface`.

.. _reference-security-logout-csrf:

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

An arbitrary string used to generate the token value (and check its validity
afterwards).

.. _reference-security-ldap:

LDAP Authentication
~~~~~~~~~~~~~~~~~~~

There are several options for connecting against an LDAP server,
using the ``form_login_ldap``, ``http_basic_ldap`` and ``json_login_ldap`` authentication
providers or the ``ldap`` user provider.

For even more details, see :doc:`/security/ldap`.

**Authentication**

You can authenticate to an LDAP server using the LDAP variants of the
``form_login``, ``http_basic`` and ``json_login`` authentication providers. Simply use
``form_login_ldap``, ``http_basic_ldap`` and ``json_login_ldap``, which will attempt to
``bind`` against a LDAP server instead of using password comparison.

Both authentication providers have the same arguments as their normal
counterparts, with the addition of two configuration keys:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client.

dn_string
.........

**type**: ``string`` **default**: ``{username}``

This is the string which will be used as the bind DN. The ``{username}``
placeholder will be replaced with the user-provided value (their login).
Depending on your LDAP server's configuration, you may need to override
this value.

query_string
............

**type**: ``string`` **default**: ``null``

This is the string which will be used to query for the DN. The ``{username}``
placeholder will be replaced with the user-provided value (their login).
Depending on your LDAP server's configuration, you will need to override
this value. This setting is only necessary if the user's DN cannot be derived
statically using the ``dn_string`` config option.

**User provider**

Users will still be fetched from the configured user provider. If you wish to
fetch your users from a LDAP server, you will need to use the
:doc:`LDAP User Provider </security/ldap>` and any of these authentication
providers: ``form_login_ldap`` or ``http_basic_ldap`` or ``json_login_ldap``.

.. _reference-security-firewall-context:

Firewall Context
~~~~~~~~~~~~~~~~

Most applications will only need one :ref:`firewall <security-firewalls>`.
But if your application *does* use multiple firewalls, you'll notice that
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
        <?xml version="1.0" charset="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

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
        $container->loadFromExtension('security', [
            'firewalls' => [
                'somename' => [
                    // ...
                    'context' => 'my_context',
                ],
                'othername' => [
                    // ...
                    'context' => 'my_context',
                ],
            ],
        ]);

.. note::

    The firewall context key is stored in session, so every firewall using it
    must set its ``stateless`` option to ``false``. Otherwise, the context is
    ignored and you won't be able to authenticate on multiple firewalls at the
    same time.

User Checkers
~~~~~~~~~~~~~

During the authentication of a user, additional checks might be required to
verify if the identified user is allowed to log in. Each firewall can include
a ``user_checker`` option to define the service used to perform those checks.

Learn more about user checkers in :doc:`/security/user_checkers`.

providers
---------

This options defines how the application users are loaded (from a database,
a LDAP server, a configuration file, etc.) Read the following articles to learn
more about each of those providers:

* :ref:`Load users from a database <security-entity-user-provider>`
* :ref:`Load users from a LDAP server <security-ldap-user-provider>`
* :ref:`Load users from a configuration file <security-memory-user-provider>`
* :ref:`Create your own user provider <custom-user-provider>`

role_hierarchy
--------------

Instead of associating many roles to users, this option allows you to define
role inheritance rules by creating a role hierarchy, as explained in
:ref:`security-role-hierarchy`.

.. _`PBKDF2`: https://en.wikipedia.org/wiki/PBKDF2
.. _`ircmaxell/password-compat`: https://packagist.org/packages/ircmaxell/password-compat
.. _`libsodium`: https://pecl.php.net/package/libsodium
.. _`Session Fixation`: https://www.owasp.org/index.php/Session_fixation
.. _`Argon2 key derivation function`: https://en.wikipedia.org/wiki/Argon2
.. _`bcrypt password hashing function`: https://en.wikipedia.org/wiki/Bcrypt
.. _`cryptographic salt`: https://en.wikipedia.org/wiki/Salt_(cryptography)
