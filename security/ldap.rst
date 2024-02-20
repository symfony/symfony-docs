Authenticating against an LDAP server
=====================================

Symfony provides different means to work with an LDAP server.

The Security component offers:

* The ``ldap`` :doc:`user provider </security/user_providers>`, using the
  :class:`Symfony\\Component\\Ldap\\Security\\LdapUserProvider`
  class. Like all other user providers, it can be used with any
  authentication provider.

* The ``form_login_ldap`` authentication provider, for authenticating
  against an LDAP server using a login form. Like all other
  authentication providers, it can be used with any user provider.

* The ``http_basic_ldap`` authentication provider, for authenticating
  against an LDAP server using HTTP Basic. Like all other
  authentication providers, it can be used with any user provider.

This means that the following scenarios will work:

* Checking a user's password and fetching user information against an
  LDAP server. This can be done using both the LDAP user provider and
  either the LDAP form login or LDAP HTTP Basic authentication providers.

* Checking a user's password against an LDAP server while fetching user
  information from another source (database using FOSUserBundle, for
  example).

* Loading user information from an LDAP server, while using another
  authentication strategy (token-based pre-authentication, for example).

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Ldap component before using it:

.. code-block:: terminal

    $ composer require symfony/ldap

Ldap Configuration Reference
----------------------------

See :doc:`/reference/configuration/security` for the full LDAP
configuration reference (``form_login_ldap``, ``http_basic_ldap``, ``ldap``).
Some of the more interesting options are explained below.

Configuring the LDAP client
---------------------------

All mechanisms actually need an LDAP client previously configured.
The providers are configured to use a default service named ``ldap``,
but you can override this setting in the security component's
configuration.

An LDAP client can be configured using the built-in
`LDAP PHP extension`_ with the following service definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Component\Ldap\Ldap:
                arguments: ['@Symfony\Component\Ldap\Adapter\ExtLdap\Adapter']
                tags:
                    - ldap
            Symfony\Component\Ldap\Adapter\ExtLdap\Adapter:
                arguments:
                    -   host: my-server
                        port: 389
                        encryption: tls
                        options:
                            protocol_version: 3
                            referrals: false

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Symfony\Component\Ldap\Ldap">
                    <argument type="service" id="Symfony\Component\Ldap\Adapter\ExtLdap\Adapter"/>
                    <tag name="ldap"/>
                </service>
                <service id="Symfony\Component\Ldap\Adapter\ExtLdap\Adapter">
                    <argument type="collection">
                        <argument key="host">my-server</argument>
                        <argument key="port">389</argument>
                        <argument key="encryption">tls</argument>
                        <argument key="options" type="collection">
                            <argument key="protocol_version">3</argument>
                            <argument key="referrals">false</argument>
                        </argument>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
        use Symfony\Component\Ldap\Ldap;

        $container->register(Ldap::class)
            ->addArgument(new Reference(Adapter::class))
            ->tag('ldap');

        $container
            ->register(Adapter::class)
            ->setArguments([
                'host' => 'my-server',
                'port' => 389,
                'encryption' => 'tls',
                'options' => [
                    'protocol_version' => 3,
                    'referrals' => false
                ],
            ]);

.. _security-ldap-user-provider:

Fetching Users Using the LDAP User Provider
-------------------------------------------

If you want to fetch user information from an LDAP server, you may want to
use the ``ldap`` user provider.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
                my_ldap:
                    ldap:
                        service: Symfony\Component\Ldap\Ldap
                        base_dn: dc=example,dc=com
                        search_dn: "cn=read-only-admin,dc=example,dc=com"
                        search_password: password
                        default_roles: ROLE_USER
                        uid_key: uid
                        extra_fields: ['email']

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
                <provider name="my_ldap">
                    <ldap service="Symfony\Component\Ldap\Ldap"
                        base-dn="dc=example,dc=com"
                        search-dn="cn=read-only-admin,dc=example,dc=com"
                        search-password="password"
                        default-roles="ROLE_USER"
                        uid-key="uid"/>
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Ldap\Ldap;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->provider('ldap_users')
                ->ldap()
                    ->service(Ldap::class)
                    ->baseDn('dc=example,dc=com')
                    ->searchDn('cn=read-only-admin,dc=example,dc=com')
                    ->searchPassword('password')
                    ->defaultRoles(['ROLE_USER'])
                    ->uidKey('uid')
                    ->extraFields(['email'])
            ;
        };


.. caution::

    The Security component escapes provided input data when the LDAP user
    provider is used. However, the LDAP component itself does not provide
    any escaping yet. Thus, it's your responsibility to prevent LDAP injection
    attacks when using the component directly.

.. caution::

    The user configured above in the user provider is only used to retrieve
    data. It's a static user defined by its username and password (for improved
    security, define the password as an environment variable).

    If your LDAP server allows retrieval of information anonymously, you can
    set the ``search_dn`` and ``search_password`` options to ``null``.

The ``ldap`` user provider supports many different configuration options:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client. You can freely choose the
name, but it must be unique in your application and it cannot start with a
number or contain white spaces.

base_dn
.......

**type**: ``string`` **default**: ``null``

This is the base DN for the directory

search_dn
.........

**type**: ``string`` **default**: ``null``

This is your read-only user's DN, which will be used to authenticate
against the LDAP server to fetch the user's information.

search_password
...............

**type**: ``string`` **default**: ``null``

This is your read-only user's password, which will be used to authenticate
against the LDAP server to fetch the user's information.

default_roles
.............

**type**: ``array`` **default**: ``[]``

This is the default role you wish to give to a user fetched from the LDAP
server. If you do not configure this key, your users won't have any roles,
and will not be considered as authenticated fully.

uid_key
.......

**type**: ``string`` **default**: ``null``

This is the entry's key to use as its UID. Depends on your LDAP server
implementation. Commonly used values are:

* ``sAMAccountName`` (default)
* ``userPrincipalName``
* ``uid``

If you pass ``null`` as the value of this option, the default UID key is used
``sAMAccountName``.

extra_fields
............

**type**: ``array`` **default**: ``null``

Defines the custom fields to pull from the LDAP server. If any field does not
exist, an ``\InvalidArgumentException`` will be thrown.

filter
......

**type**: ``string`` **default**: ``null``

This key lets you configure which LDAP query will be used. The ``{uid_key}``
string will be replaced by the value of the ``uid_key`` configuration value
(by default, ``sAMAccountName``), and the ``{user_identifier}`` string will be
replaced by the user identified you are trying to load.

.. deprecated:: 6.2

    Starting from Symfony 6.2, the ``{username}`` string was deprecated in favor
    of ``{user_identifier}``.

For example, with a ``uid_key`` of ``uid``, and if you are trying to
load the user ``fabpot``, the final string will be: ``(uid=fabpot)``.

If you pass ``null`` as the value of this option, the default filter is used
``({uid_key}={user_identifier})``.

To prevent `LDAP injection`_, the username will be escaped.

The syntax for the ``filter`` key is defined by `RFC4515`_.

Authenticating against an LDAP server
-------------------------------------

Authenticating against an LDAP server can be done using either the form
login or the HTTP Basic authentication providers.

They are configured exactly as their non-LDAP counterparts, with the
addition of two configuration keys and one optional key:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client. You can freely choose the
name, but it must be unique in your application and it cannot start with a
number or contain white spaces.

dn_string
.........

**type**: ``string`` **default**: ``{user_identifier}``

This key defines the form of the string used to compose the
DN of the user, from the username. The ``{user_identifier}`` string is
replaced by the actual username of the person trying to authenticate.

For example, if your users have DN strings in the form
``uid=einstein,dc=example,dc=com``, then the ``dn_string`` will be
``uid={user_identifier},dc=example,dc=com``.

query_string
............

**type**: ``string`` **default**: ``null``

This (optional) key makes the user provider search for a user and then use the
found DN for the bind process. This is useful when using multiple LDAP user
providers with different ``base_dn``. The value of this option must be a valid
search string (e.g. ``uid="{user_identifier}"``). The placeholder value will be
replaced by the actual user identifier.

When this option is used, ``query_string`` will search in the DN specified by
``dn_string`` and the DN resulted of the ``query_string`` will be used to
authenticate the user with their password. Following the previous example, if
your users have the following two DN: ``dc=companyA,dc=example,dc=com`` and
``dc=companyB,dc=example,dc=com``, then ``dn_string`` should be
``dc=example,dc=com``.

Bear in mind that usernames must be unique across both DN, as the authentication
provider won't be able to select the correct user for the bind process if more
than one is found.

Examples are provided below, for both ``form_login_ldap`` and
``http_basic_ldap``.

Configuration example for form login
....................................

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login_ldap:
                        # ...
                        service: Symfony\Component\Ldap\Ldap
                        dn_string: 'uid={user_identifier},dc=example,dc=com'

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
                    <form-login-ldap service="Symfony\Component\Ldap\Ldap"
                        dn-string="uid={user_identifier},dc=example,dc=com"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Ldap\Ldap;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->formLoginLdap()
                    ->service(Ldap::class)
                    ->dnString('uid={user_identifier},dc=example,dc=com')
            ;
        };

Configuration example for HTTP Basic
....................................

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    stateless: true
                    http_basic_ldap:
                        service: Symfony\Component\Ldap\Ldap
                        dn_string: 'uid={user_identifier},dc=example,dc=com'

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

                <firewall name="main" stateless="true">
                    <http-basic-ldap service="Symfony\Component\Ldap\Ldap"
                        dn-string="uid={user_identifier},dc=example,dc=com"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Ldap\Ldap;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->stateless(true)
                ->formLoginLdap()
                    ->service(Ldap::class)
                    ->dnString('uid={user_identifier},dc=example,dc=com')
            ;
        };

Configuration example for form login and query_string
.....................................................

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login_ldap:
                        service: Symfony\Component\Ldap\Ldap
                        dn_string: 'dc=example,dc=com'
                        query_string: '(&(uid={user_identifier})(memberOf=cn=users,ou=Services,dc=example,dc=com))'
                        search_dn: '...'
                        search_password: 'the-raw-password'

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
                    <!-- ... -->
                    <form-login-ldap service="Symfony\Component\Ldap\Ldap"
                        dn-string="dc=example,dc=com"
                        query-string="(&amp;(uid={user_identifier})(memberOf=cn=users,ou=Services,dc=example,dc=com))"
                        search-dn="..."
                        search-password="the-raw-password"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Component\Ldap\Ldap;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                ->stateless(true)
                ->formLoginLdap()
                    ->service(Ldap::class)
                    ->dnString('dc=example,dc=com')
                    ->queryString('(&(uid={user_identifier})(memberOf=cn=users,ou=Services,dc=example,dc=com))')
                    ->searchDn('...')
                    ->searchPassword('the-raw-password')
            ;
        };

.. _`LDAP PHP extension`: https://www.php.net/manual/en/intro.ldap.php
.. _`RFC4515`: https://datatracker.ietf.org/doc/rfc4515/
.. _`LDAP injection`: http://projects.webappsec.org/w/page/13246947/LDAP%20Injection

