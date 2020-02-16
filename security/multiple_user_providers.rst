How to Use multiple User Providers
==================================

.. note::

    It's always better to use a specific user provider for each authentication
    mechanism. Chaining user providers should be avoided in most applications
    and used only to solve edge cases.

Each authentication mechanism (e.g. HTTP Authentication, form login, etc)
uses exactly one user provider, and will use the first declared user provider
by default. But what if you want to specify a few users via configuration
and the rest of your users in the database? This is possible by creating
a new provider that chains the two together:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            providers:
                users:
                    chain:
                        providers: [users_in_memory, users_in_db]
                users_in_memory:
                    memory:
                        users:
                            foo: { password: test }
                users_in_db:
                    entity: { class: AppBundle\Entity\User, property: username }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <provider name="users">
                    <chain>
                        <provider>users_in_memory</provider>
                        <provider>users_in_db</provider>
                    </chain>
                </provider>

                <provider name="users_in_memory">
                    <memory>
                        <user name="foo" password="test"/>
                    </memory>
                </provider>

                <provider name="users_in_db">
                    <entity class="AppBundle\Entity\User" property="username"/>
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        use AppBundle\Entity\User;

        $container->loadFromExtension('security', [
            'providers' => [
                'users' => [
                    'chain' => [
                        'providers' => ['in_memory', 'user_db'],
                    ],
                ],
                'users_in_memory' => [
                    'memory' => [
                        'users' => [
                            'foo' => ['password' => 'test'],
                        ],
                    ],
                ],
                'users_in_db' => [
                    'entity' => [
                        'class'    => User::class,
                        'property' => 'username',
                    ],
                ],
            ],
        ]);

Now, all firewalls that explicitly define ``users`` as their user
provider will, in turn, try to load the user from both the ``users_in_memory`` then
``users_in_db`` providers.

.. deprecated:: 3.4

    In previous Symfony versions, firewalls that didn't define their user provider
    explicitly, used the first existing provider (``users`` in this
    example). However, auto-selecting the first user provider has been deprecated
    in Symfony 3.4 and will throw an exception in 4.0. Always define the provider
    used by the firewall when there are multiple providers.

You can also configure the firewall or individual authentication mechanisms
to use a specific provider. Again, unless a provider is specified explicitly,
the first provider is always used:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            firewalls:
                secured_area:
                    # ...
                    pattern: ^/
                    provider: users_in_db
                    http_basic:
                        realm: 'Secured Demo Area'
                        provider: users_in_memory
                    form_login: ~

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="secured_area" pattern="^/" provider="users_in_db">
                    <!-- ... -->
                    <http-basic realm="Secured Demo Area" provider="users_in_memory"/>
                    <form-login/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', [
            'firewalls' => [
                'secured_area' => [
                    // ...
                    'pattern' => '^/',
                    'provider' => 'users_in_db',
                    'http_basic' => [
                        'realm' => 'Secured Demo Area',
                        'provider' => 'users_in_memory',
                    ],
                    'form_login' => [],
                ],
            ],
        ]);

In this example, if a user tries to log in via HTTP authentication, the authentication
system will use the ``users_in_memory`` user provider. But if the user tries to
log in via the form login, the ``users_in_db`` provider will be used (since it's
the default for the firewall as a whole).

If you need to check that the user being returned by  your provider is a allowed
to authenticate, check the returned user object::

    use Symfony\Component\Security\Core\User;
    // ...

    public function loadUserByUsername($username)
    {
        // ...

        // you can, for example, test that the returned user is an object of a
        // particular class or check for certain attributes of your user objects
        if ($user instance User) {
            // the user was loaded from the main security config file. Do something.
            // ...
        }

        return $user;
    }

For more information about user provider and firewall configuration, see
the :doc:`/reference/configuration/security`.
