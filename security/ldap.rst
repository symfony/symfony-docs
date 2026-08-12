LDAP
====

`LDAP`_ (Lightweight Directory Access Protocol) is a standard protocol to
access directory services over a network. Organizations often use LDAP
directories to store information about their users and to manage their
credentials.

Symfony provides several utilities to work with LDAP servers such as OpenLDAP
and Active Directory. The Ldap component includes a client to connect to the
LDAP server, query its contents and manage its entries. You can use this
client in any PHP application.

In Symfony applications, the Security component also integrates with LDAP to
authenticate users and load their information. It offers:

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

* The ``json_login_ldap`` authentication provider, for authenticating
  against an LDAP server using JSON login. Like all other
  authentication providers, it can be used with any user provider.

This means that the following scenarios will work:

* Checking a user's password and fetching user information against an
  LDAP server. This can be done using both the LDAP user provider and
  either the LDAP form login or LDAP HTTP Basic authentication providers.

* Checking a user's password against an LDAP server while fetching user
  information from another source (like your main database for
  example).

* Loading user information from an LDAP server, while using another
  authentication strategy (token-based pre-authentication, for example).

See :doc:`/reference/configuration/security` for the full LDAP
configuration reference (``form_login_ldap``, ``http_basic_ldap``,
``json_login_ldap`` and ``ldap``). Some of the more interesting options
are explained below.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the Ldap component before using it:

.. code-block:: terminal

    $ composer require symfony/ldap

Configuring the LDAP Client
---------------------------

The :class:`Symfony\\Component\\Ldap\\Ldap` class uses an
:class:`Symfony\\Component\\Ldap\\Adapter\\AdapterInterface` to communicate
with the LDAP server. The :class:`adapter <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\Adapter>`
for PHP's built-in `LDAP PHP extension`_ can be configured using the
following options:

``host``
    IP or hostname of the LDAP server

``port``
    Port used to access the LDAP server

``version``
    The version of the LDAP protocol to use

``encryption``
    The encryption protocol: ``ssl``, ``tls`` or ``none`` (default)

``connection_string``
    You may use this option instead of ``host`` and ``port`` to connect to the
    LDAP server

``optReferrals``
    Specifies whether to automatically follow referrals returned by the LDAP server

``options``
    LDAP server's options as defined in
    :class:`ConnectionOptions <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\ConnectionOptions>`

All the security mechanisms shown later in this article need a previously
configured LDAP client. In Symfony applications, define the client as a
service; the security providers use a service named ``ldap`` by default,
but you can override this setting in the security component's configuration.
In standalone applications, create the client with the ``Ldap::create()``
factory method:

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
        use Symfony\Component\Ldap\Ldap;

        return App::config([
            'services' => [
                Ldap::class => [
                    'arguments' => [service(Adapter::class)],
                    'tags' => ['ldap'],
                ],
                Adapter::class => [
                    'arguments' => [[
                        'host' => 'my-server',
                        'port' => 389,
                        'encryption' => 'tls',
                        'options' => [
                            'protocol_version' => 3,
                            'referrals' => false,
                        ],
                    ]],
                ],
            ],
        ]);

    .. code-block:: php-standalone

        use Symfony\Component\Ldap\Ldap;

        // the first argument is the adapter; 'ext_ldap' uses the LDAP PHP extension
        $ldap = Ldap::create('ext_ldap', [
            'host' => 'my-server',
            'encryption' => 'ssl',
        ]);

        // you can use a connection string instead of the host and port options
        $ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldaps://my-server:636']);

Connecting and Querying
-----------------------

The :method:`Symfony\\Component\\Ldap\\Ldap::bind` method authenticates a
previously configured connection using both the distinguished name (DN) and
the password of a user:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Service/LdapManager.php
        namespace App\Service;

        use Symfony\Component\Ldap\Ldap;

        class LdapManager
        {
            public function __construct(
                private Ldap $ldap,
            ) {
            }

            public function authenticate(string $dn, string $password): void
            {
                $this->ldap->bind($dn, $password);

                // ...
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\Ldap\Ldap;

        $ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldaps://my-server:636']);
        $ldap->bind($dn, $password);

.. danger::

    When the LDAP server allows unauthenticated binds, a blank password will always be valid.

You can also use the :method:`Symfony\\Component\\Ldap\\Ldap::saslBind` method
for binding to an LDAP server using `SASL`_::

    // this method defines other optional arguments like $mech, $realm, $authcId, etc.
    $ldap->saslBind($dn, $password);

After binding to the LDAP server, you can use the :method:`Symfony\\Component\\Ldap\\Ldap::whoami`
method to get the distinguished name (DN) of the authenticated and authorized user.

.. versionadded:: 7.2

    The ``saslBind()`` and ``whoami()`` methods were introduced in Symfony 7.2.

Once bound (or if you enabled anonymous authentication on your
LDAP server), you may query the LDAP server using the
:method:`Symfony\\Component\\Ldap\\Ldap::query` method::

    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $results = $query->execute();

    foreach ($results as $entry) {
        // Do something with the results
    }

.. danger::

    The Security component escapes provided input data when the LDAP user
    provider is used. However, the LDAP component itself does not provide
    any escaping yet. Thus, it's your responsibility to prevent LDAP injection
    attacks when using the component directly.

By default, LDAP entries are lazy-loaded. If you wish to fetch
all entries in a single call and do something with the results'
array, you may use the
:method:`Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\Collection::toArray` method::

    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $results = $query->execute()->toArray();

    // Do something with the results array

By default, LDAP queries use the ``Symfony\Component\Ldap\Adapter\QueryInterface::SCOPE_SUB``
scope, which corresponds to the ``LDAP_SCOPE_SUBTREE`` scope of the
:phpfunction:`ldap_search` function. You can also use ``SCOPE_BASE`` (related
to the ``LDAP_SCOPE_BASE`` scope of :phpfunction:`ldap_read`) and ``SCOPE_ONE``
(related to the ``LDAP_SCOPE_ONELEVEL`` scope of :phpfunction:`ldap_list`)::

    use Symfony\Component\Ldap\Adapter\QueryInterface;

    $query = $ldap->query('dc=symfony,dc=com', '...', ['scope' => QueryInterface::SCOPE_ONE]);

Use the ``filter`` option to only retrieve some specific attributes::

    $query = $ldap->query('dc=symfony,dc=com', '...', ['filter' => ['cn', 'mail']]);

Creating and Updating Entries
-----------------------------

The Ldap component provides means to create new LDAP entries, update or even
delete existing ones::

    use Symfony\Component\Ldap\Entry;
    use Symfony\Component\Ldap\Ldap;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', [
        'sn' => ['fabpot'],
        'objectClass' => ['inetOrgPerson'],
    ]);

    $entryManager = $ldap->getEntryManager();

    // creating a new entry
    $entryManager->add($entry);

    // finding and updating an existing entry
    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $result = $query->execute();
    $entry = $result[0];

    $phoneNumber = $entry->getAttribute('phoneNumber');
    $isContractor = $entry->hasAttribute('contractorCompany');
    // attribute names in getAttribute() and hasAttribute() methods are case-sensitive
    // pass FALSE as the second method argument to make them case-insensitive
    $isContractor = $entry->hasAttribute('contractorCompany', false);

    $entry->setAttribute('email', ['fabpot@symfony.com']);
    $entryManager->update($entry);

    // adding or removing values to a multi-valued attribute is more efficient than using update()
    $entryManager->addAttributeValues($entry, 'telephoneNumber', ['+1.111.222.3333', '+1.222.333.4444']);
    $entryManager->removeAttributeValues($entry, 'telephoneNumber', ['+1.111.222.3333', '+1.222.333.4444']);

    // removing an existing entry
    $entryManager->remove(new Entry('cn=Test User,dc=symfony,dc=com'));

Batch Updating
..............

Use the entry manager's :method:`Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\EntryManager::applyOperations`
method to update multiple attributes at once::

    use Symfony\Component\Ldap\Adapter\ExtLdap\UpdateOperation;
    use Symfony\Component\Ldap\Entry;
    use Symfony\Component\Ldap\Ldap;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', [
        'sn' => ['fabpot'],
        'objectClass' => ['inetOrgPerson'],
    ]);

    $entryManager = $ldap->getEntryManager();

    // adding multiple email addresses at once
    $entryManager->applyOperations($entry->getDn(), [
        new UpdateOperation(LDAP_MODIFY_BATCH_ADD, 'mail', 'new1@example.com'),
        new UpdateOperation(LDAP_MODIFY_BATCH_ADD, 'mail', 'new2@example.com'),
    ]);

Possible operation types are ``LDAP_MODIFY_BATCH_ADD``, ``LDAP_MODIFY_BATCH_REMOVE``,
``LDAP_MODIFY_BATCH_REMOVE_ALL``, ``LDAP_MODIFY_BATCH_REPLACE``. Parameter
``$values`` must be ``NULL`` when using ``LDAP_MODIFY_BATCH_REMOVE_ALL``
operation type.

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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Ldap\Ldap;

        return App::config([
            'security' => [
                // ...
                'providers' => [
                    'my_ldap' => [
                        'ldap' => [
                            'service' => Ldap::class,
                            'base_dn' => 'dc=example,dc=com',
                            'search_dn' => 'cn=read-only-admin,dc=example,dc=com',
                            'search_password' => 'password',
                            'default_roles' => ['ROLE_USER'],
                            'uid_key' => 'uid',
                            'extra_fields' => ['email'],
                        ],
                    ],
                ],
            ],
        ]);

.. warning::

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

role_fetcher
............

**Type**: ``string`` **Default**: ``null``

When your LDAP service provides user roles, this option allows you to define
the service that retrieves these roles. The role fetcher service must implement
the ``Symfony\Component\Ldap\Security\RoleFetcherInterface``. When this option
is set, the ``default_roles`` option is ignored.

Symfony provides ``Symfony\Component\Ldap\Security\MemberOfRoles``, a concrete
implementation of the interface that fetches roles from the ``ismemberof``
attribute (configurable via its ``$attributeName`` constructor argument).
See `Using MemberOfRoles`_ below for an example.

uid_key
.......

**type**: ``string`` **default**: ``null``

This is the entry's key to use as its UID. Depends on your LDAP server
implementation. Commonly used values are:

* ``sAMAccountName`` (default)
* ``userPrincipalName``
* ``uid``

If you pass ``null`` as the value of this option, the default UID key
``sAMAccountName`` is used.

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
replaced by the user identifier you are trying to load.

For example, with a ``uid_key`` of ``uid``, and if you are trying to
load the user ``fabpot``, the final string will be: ``(uid=fabpot)``.

If you pass ``null`` as the value of this option, the default filter is used
``({uid_key}={user_identifier})``.

To prevent `LDAP injection`_, the username will be escaped.

The syntax for the ``filter`` key is defined by `RFC4515`_.

Using MemberOfRoles
~~~~~~~~~~~~~~~~~~~

Register ``MemberOfRoles`` as a service with your role mapping, then point
``role_fetcher`` at it and add the LDAP attribute to ``extra_fields``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Component\Ldap\Security\MemberOfRoles:
                arguments:
                    $mapping:
                        admins: 'ROLE_ADMIN'
                        users: 'ROLE_USER'

        # config/packages/security.yaml
        security:
            providers:
                my_ldap:
                    ldap:
                        service: Symfony\Component\Ldap\Ldap
                        base_dn: dc=example,dc=com
                        uid_key: uid
                        extra_fields: ['ismemberof']
                        role_fetcher: Symfony\Component\Ldap\Security\MemberOfRoles

    .. code-block:: php

        // config/services.php
        use Symfony\Component\Ldap\Security\MemberOfRoles;

        return function (ContainerConfigurator $container): void {
            $container->services()
                ->set(MemberOfRoles::class)
                    ->args([
                        '$mapping' => [
                            'admins' => 'ROLE_ADMIN',
                            'users' => 'ROLE_USER',
                        ],
                    ]);
        };

``MemberOfRoles`` extracts group names from LDAP attribute values matching a
default pattern (``CN=<group_name>,ou=...``); pass a custom ``$groupNameRegex``
constructor argument to override it.

Authenticating against an LDAP server
-------------------------------------

Authenticating against an LDAP server can be done using the form login, the
HTTP Basic or the JSON login authentication providers, via the
``form_login_ldap``, ``http_basic_ldap`` and ``json_login_ldap`` options.

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

Note that usernames must be unique across both DN, as the authentication
provider won't be able to select the correct user for the bind process if more
than one is found.

Examples are provided below, for both ``form_login_ldap`` and
``http_basic_ldap``. The ``json_login_ldap`` provider is configured in the
same way (see :ref:`the LDAP configuration reference <reference-security-ldap>`).

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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Ldap\Ldap;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login_ldap' => [
                            'service' => Ldap::class,
                            'dn_string' => 'uid={user_identifier},dc=example,dc=com',
                        ],
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Ldap\Ldap;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'stateless' => true,
                        'http_basic_ldap' => [
                            'service' => Ldap::class,
                            'dn_string' => 'uid={user_identifier},dc=example,dc=com',
                        ],
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\Ldap\Ldap;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'form_login_ldap' => [
                            'service' => Ldap::class,
                            'dn_string' => 'dc=example,dc=com',
                            'query_string' => '(&(uid={user_identifier})(memberOf=cn=users,ou=Services,dc=example,dc=com))',
                            'search_dn' => '...',
                            'search_password' => 'the-raw-password',
                        ],
                    ],
                ],
            ],
        ]);

.. _`LDAP`: https://en.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol
.. _`LDAP PHP extension`: https://www.php.net/manual/en/intro.ldap.php
.. _`RFC4515`: https://datatracker.ietf.org/doc/rfc4515/
.. _`LDAP injection`: http://projects.webappsec.org/w/page/13246947/LDAP%20Injection
.. _`SASL`: https://en.wikipedia.org/wiki/Simple_Authentication_and_Security_Layer
