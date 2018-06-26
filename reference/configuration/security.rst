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
    ``http://symfony.com/schema/dic/services/services-1.0.xsd``

.. versionadded:: 4.1
    The ``providers`` option is optional starting from Symfony 4.1.

.. _reference-security-firewall-form-login:

Form Login Configuration
------------------------

When using the ``form_login`` authentication listener beneath a firewall,
there are several common options for configuring the "form login" experience.

For even more details, see :doc:`/security/form_login`.

The Login Form and Process
~~~~~~~~~~~~~~~~~~~~~~~~~~

login_path
..........

**type**: ``string`` **default**: ``/login``

This is the route or path that the user will be redirected to (unless ``use_forward``
is set to ``true``) when they try to access a protected resource but isn't
fully authenticated.

This path **must** be accessible by a normal, un-authenticated user, else
you may create a redirect loop. For details, see
":ref:`Avoid Common Pitfalls <security-common-pitfalls>`".

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

Redirecting after Login
~~~~~~~~~~~~~~~~~~~~~~~

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

.. _reference-security-pbkdf2:

Logout Configuration
--------------------

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

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 3.4
    The ``logout_on_user_change`` option was introduced in Symfony 3.4.

If ``true`` this option makes Symfony to trigger a logout when the user has
changed. Not doing that is deprecated, so this option should be set to ``true``
to avoid getting deprecation messages.

The user is considered to have changed when the user class implements
:class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface` and the
``isEqualTo()`` method returns ``false``. Also, when any of the properties
required by the :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
(like the username, password or salt) changes.

.. _reference-security-ldap:

LDAP functionality
------------------

There are several options for connecting against an LDAP server,
using the ``form_login_ldap`` and ``http_basic_ldap`` authentication
providers or the ``ldap`` user provider.

For even more details, see :doc:`/security/ldap`.

Authentication
~~~~~~~~~~~~~~

You can authenticate to an LDAP server using the LDAP variants of the
``form_login`` and ``http_basic`` authentication providers. Simply use
``form_login_ldap`` and ``http_basic_ldap``, which will attempt to
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

User provider
~~~~~~~~~~~~~

Users will still be fetched from the configured user provider. If you
wish to fetch your users from a LDAP server, you will need to use the
``ldap`` user provider, in addition to one of the two authentication
providers (``form_login_ldap`` or ``http_basic_ldap``).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
                my_ldap_users:
                    ldap:
                        service: ldap
                        base_dn: 'dc=symfony,dc=com'
                        search_dn: '%ldap.search_dn%'
                        search_password: '%ldap.search_password%'
                        default_roles: ''
                        uid_key: 'uid'
                        filter: '(&({uid_key}={username})(objectclass=person)(ou=Users))'

Using the PBKDF2 Encoder: Security and Speed
--------------------------------------------

The `PBKDF2`_ encoder provides a high level of Cryptographic security, as
recommended by the National Institute of Standards and Technology (NIST).

You can see an example of the ``pbkdf2`` encoder in the YAML block on this
page.

But using PBKDF2 also warrants a warning: using it (with a high number
of iterations) slows down the process. Thus, PBKDF2 should be used with
caution and care.

A good configuration lies around at least 1000 iterations and sha512
for the hash algorithm.

.. _reference-security-bcrypt:

Using the BCrypt Password Encoder
---------------------------------

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            encoders:
                Symfony\Component\Security\Core\User\User:
                    algorithm: bcrypt
                    cost:      15

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <encoder
                    class="Symfony\Component\Security\Core\User\User"
                    algorithm="bcrypt"
                    cost="15"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        use Symfony\Component\Security\Core\User\User;

        $container->loadFromExtension('security', array(
            // ...
            'encoders' => array(
                User::class => array(
                    'algorithm' => 'bcrypt',
                    'cost'      => 15,
                ),
            ),
        ));

The ``cost`` can be in the range of ``4-31`` and determines how long a password
will be encoded. Each increment of ``cost`` *doubles* the time it takes
to encode a password.

If you don't provide the ``cost`` option, the default cost of ``13`` is
used.

.. note::

    You can change the cost at any time — even if you already have some
    passwords encoded using a different cost. New passwords will be encoded
    using the new cost, while the already encoded ones will be validated
    using a cost that was used back when they were encoded.

A salt for each new password is generated automatically and need not be
persisted. Since an encoded password contains the salt used to encode it,
persisting the encoded password alone is enough.

.. note::

    BCrypt encoded passwords are ``60`` characters long, so make sure to
    allocate enough space for them to be persisted.

.. tip::

    A simple technique to make tests much faster when using BCrypt is to set
    the cost to ``4``, which is the minimum value allowed, in the ``test``
    environment configuration.

.. _reference-security-argon2i:

Using the Argon2i Password Encoder
----------------------------------

.. caution::

    To use this encoder, you either need to use PHP version 7.2 or install
    the `libsodium`_ extension.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            encoders:
                Symfony\Component\Security\Core\User\User:
                    algorithm: argon2i
                    memory_cost:          16384 # Amount in KiB. 16 MiB
                    time_cost:            2 # Number of iterations
                    threads:              4 # Number of parallel threads

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <!-- ... -->
            <encoder
                class="Symfony\Component\Security\Core\User\User"
                algorithm="argon2i"
                memory_cost="16384"
                time_cost="2"
                threads="4"
            />
        </config>

    .. code-block:: php

        // app/config/security.php
        use Symfony\Component\Security\Core\User\User;

        $container->loadFromExtension('security', array(
            // ...
            'encoders' => array(
                User::class => array(
                    'algorithm' => 'argon2i',
                    'memory_cost' => 16384,
                    'time_cost' => 2,
                    'threads' => 4,
                ),
            ),
        ));

A salt for each new password is generated automatically and need not be
persisted. Since an encoded password contains the salt used to encode it,
persisting the encoded password alone is enough.

.. note::

    Argon2i encoded passwords are ``96`` characters long, but due to the hashing
    requirements saved in the resulting hash this may change in the future.

.. _reference-security-firewall-context:

Firewall Context
----------------

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
                http://symfony.com/schema/dic/services/services-1.0.xsd">

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
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'somename' => array(
                    // ...
                    'context' => 'my_context',
                ),
                'othername' => array(
                    // ...
                    'context' => 'my_context',
                ),
            ),
        ));

.. note::

    The firewall context key is stored in session, so every firewall using it
    must set its ``stateless`` option to ``false``. Otherwise, the context is
    ignored and you won't be able to authenticate on multiple firewalls at the
    same time.

.. _`PBKDF2`: https://en.wikipedia.org/wiki/PBKDF2
.. _`ircmaxell/password-compat`: https://packagist.org/packages/ircmaxell/password-compat
.. _`libsodium`: https://pecl.php.net/package/libsodium
