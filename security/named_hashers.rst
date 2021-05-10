.. index::
    single: Security; Named Encoders

How to Use A Different Password Hasher Algorithm Per User
=========================================================

Usually, the same password hasher is used for all users by configuring it
to apply to all instances of a specific class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                App\Entity\User:
                    algorithm: auto
                    cost: 12

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd"
        >
            <config>
                <!-- ... -->
                <security:password-hasher class="App\Entity\User"
                    algorithm="auto"
                    cost="12"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', [
            // ...
            'password_hashers' => [
                User::class => [
                    'algorithm' => 'auto',
                    'cost' => 12,
                ],
            ],
        ]);

Another option is to use a "named" hasher and then select which hasher
you want to use dynamically.

In the previous example, you've set the ``auto`` algorithm for ``App\Entity\User``.
This may be secure enough for a regular user, but what if you want your admins
to have a stronger algorithm, for example ``auto`` with a higher cost. This can
be done with named hashers:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                harsh:
                    algorithm: auto
                    cost: 15

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd"
        >

            <config>
                <!-- ... -->
                <security:password-hasher class="harsh"
                    algorithm="auto"
                    cost="15"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...
            'password_hashers' => [
                'harsh' => [
                    'algorithm' => 'auto',
                    'cost'      => '15',
                ],
            ],
        ]);

.. note::

    If you are running PHP 7.2+ or have the `libsodium`_ extension installed,
    then the recommended hashing algorithm to use is
    :ref:`Sodium <reference-security-sodium>`.

This creates a hasher named ``harsh``. In order for a ``User`` instance
to use it, the class must implement
:class:`Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherAwareInterface`.
The interface requires one method - ``getPasswordHasherName()`` - which should return
the name of the hasher to use::

    // src/Entity/User.php
    namespace App\Entity;

    use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class User implements UserInterface, PasswordHasherAwareInterface
    {
        public function getPasswordHasherName(): ?string
        {
            if ($this->isAdmin()) {
                return 'harsh';
            }

            return null; // use the default hasher
        }
    }

If you created your own password hasher implementing the
:class:`Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface`,
you must register a service for it in order to use it as a named hasher:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                app_hasher:
                    id: 'App\Security\Hasher\MyCustomPasswordHasher'

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd"
        >

            <config>
                <!-- ... -->
                <security:password_hasher class="app_hasher"
                    id="App\Security\Hasher\MyCustomPasswordHasher"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        // ...
        use App\Security\Hasher\MyCustomPasswordHasher;

        $container->loadFromExtension('security', [
            // ...
            'password_hashers' => [
                'app_hasher' => [
                    'id' => MyCustomPasswordHasher::class,
                ],
            ],
        ]);

This creates a hasher named ``app_hasher`` from a service with the ID
``App\Security\Hasher\MyCustomPasswordHasher``.

.. _`libsodium`: https://pecl.php.net/package/libsodium
