.. index::
   single: Security; Authenticating against an LDAP server

Authenticating against an LDAP server
=====================================

Symfony provides different means to work with an LDAP server.

The Security component offers:

* The ``ldap`` user provider, using the
  :class:`Symfony\\Component\\Security\\Core\\User\\LdapUserProvider`
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

An LDAP client can be simply configured using the built-in ``ldap`` PHP
extension with the following service definition:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            ldap:
                class: Symfony\Component\Ldap\Ldap
                arguments: ['@ext_ldap_adapter']
            ext_ldap_adapter:
                class: Symfony\Component\Ldap\Adapter\ExtLdap\Adapter
                arguments:
                    -   host: my-server
                        port: 389
                        encryption: tls
                        options:
                            protocol_version: 3
                            referrals: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="ldap" class="Symfony\Component\Ldap\Ldap">
                    <argument type="service" id="ext_ldap_adapter" />
                </service>
                <service id="ext_ldap_adapter" class="Symfony\Component\Ldap\Adapter\ExtLdap\Adapter">
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

        // app/config/services.php
        use Symfony\Component\Ldap\Ldap;
        use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
        use Symfony\Component\DependencyInjection\Definition;

        $container->register('ldap', Ldap::class)
            ->addArgument(new Reference('ext_ldap_adapter'));

        $container
            ->setDefinition('ext_ldap_adapter', new Definition(Adapter::class, array(
                'host' => 'my-server',
                'port' => 389,
                'encryption' => 'tls',
                'options' => array(
                    'protocol_version' => 3,
                    'referrals' => false
                )
            )));

Fetching Users Using the LDAP User Provider
-------------------------------------------

If you want to fetch user information from an LDAP server, you may want to
use the ``ldap`` user provider.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            providers:
                my_ldap:
                    ldap:
                        service: ldap
                        base_dn: dc=example,dc=com
                        search_dn: "cn=read-only-admin,dc=example,dc=com"
                        search_password: password
                        default_roles: ROLE_USER
                        uid_key: uid

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <provider name="my_ldap">
                    <ldap
                            service="ldap"
                            base-dn="dc=example,dc=com"
                            search-dn="cn=read-only-admin,dc=example,dc=com"
                            search-password="password"
                            default-roles="ROLE_USER"
                            uid-key="uid"
                    />
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        $container->loadFromExtension('security', array(
            'providers' => array(
                'ldap_users' => array(
                    'ldap' => array(
                        'service' => 'ldap',
                        'base_dn' => 'dc=example,dc=com',
                        'search_dn' => 'cn=read-only-admin,dc=example,dc=com',
                        'search_password' => 'password',
                        'default_roles' => 'ROLE_USER',
                        'uid_key' => 'uid',
                    ),
                ),
            ),
        );

.. caution::

    The Security component escapes provided input data when the LDAP user
    provider is used. However, the LDAP component itself does not provide
    any escaping yet. Thus, it's your responsibility to prevent LDAP injection
    attacks when using the component directly.

The ``ldap`` user provider supports many different configuration options:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client. You can freely chose the
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
against the LDAP server in order to fetch the user's information.

search_password
...............

**type**: ``string`` **default**: ``null``

This is your read-only user's password, which will be used to authenticate
against the LDAP server in order to fetch the user's information.

default_roles
.............

**type**: ``array`` **default**: ``[]``

This is the default role you wish to give to a user fetched from the LDAP
server. If you do not configure this key, your users won't have any roles,
and will not be considered as authenticated fully.

uid_key
.......

**type**: ``string`` **default**: ``sAMAccountName``

This is the entry's key to use as its UID. Depends on your LDAP server
implementation. Commonly used values are:

* ``sAMAccountName``
* ``userPrincipalName``
* ``uid``

filter
......

**type**: ``string`` **default**: ``({uid_key}={username})``

This key lets you configure which LDAP query will be used. The ``{uid_key}``
string will be replaced by the value of the ``uid_key`` configuration value
(by default, ``sAMAccountName``), and the ``{username}`` string will be
replaced by the username you are trying to load.

For example, with a ``uid_key`` of ``uid``, and if you are trying to
load the user ``fabpot``, the final string will be: ``(uid=fabpot)``.

Of course, the username will be escaped, in order to prevent `LDAP injection`_.

The syntax for the ``filter`` key is defined by `RFC4515`_.

Authenticating against an LDAP server
-------------------------------------

Authenticating against an LDAP server can be done using either the form
login or the HTTP Basic authentication providers.

They are configured exactly as their non-LDAP counterparts, with the
addition of two configuration keys:

service
.......

**type**: ``string`` **default**: ``ldap``

This is the name of your configured LDAP client. You can freely chose the
name, but it must be unique in your application and it cannot start with a
number or contain white spaces.

dn_string
.........

**type**: ``string`` **default**: ``{username}``

This key defines the form of the string used in order to compose the
DN of the user, from the username. The ``{username}`` string is
replaced by the actual username of the person trying to authenticate.

For example, if your users have DN strings in the form
``uid=einstein,dc=example,dc=com``, then the ``dn_string`` will be
``uid={username},dc=example,dc=com``.

Examples are provided below, for both ``form_login_ldap`` and
``http_basic_ldap``.

Configuration example for form login
....................................

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    form_login_ldap:
                        login_path: login
                        check_path: login_check
                        # ...
                        service: ldap
                        dn_string: 'uid={username},dc=example,dc=com'

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main">
                    <form-login-ldap
                            login-path="login"
                            check-path="login_check"
                            service="ldap"
                            dn-string="uid={username},dc=example,dc=com" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'form_login_ldap' => array(
                        'login_path' => 'login',
                        'check_path' => 'login_check',
                        'service' => 'ldap',
                        'dn_string' => 'uid={username},dc=example,dc=com',
                        // ...
                    ),
                ),
            )
        );

Configuration example for HTTP Basic
....................................

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    http_basic_ldap:
                        # ...
                        service: ldap
                        dn_string: 'uid={username},dc=example,dc=com'

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main" stateless="true">
                    <http-basic-ldap service="ldap" dn-string="uid={username},dc=example,dc=com" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    'http_basic_ldap' => array(
                        'service' => 'ldap',
                        'dn_string' => 'uid={username},dc=example,dc=com',
                        // ...
                    ),
                    'stateless' => true,
                ),
            ),
        );

.. _`RFC4515`: http://www.faqs.org/rfcs/rfc4515.html
.. _`LDAP injection`: http://projects.webappsec.org/w/page/13246947/LDAP%20Injection
