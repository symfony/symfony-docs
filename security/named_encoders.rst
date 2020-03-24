.. index::
    single: Security; Named Encoders

How to Use A Different Password Encoder Algorithm Per User
==========================================================

Usually, the same password encoder is used for all users by configuring it
to apply to all instances of a specific class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            encoders:
                App\Entity\User:
                    algorithm: auto
                    cost: 12

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
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
                <encoder class="App\Entity\User"
                    algorithm="auto"
                    cost=12
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', [
            // ...
            'encoders' => [
                User::class => [
                    'algorithm' => 'auto',
                    'cost' => 12,
                ],
            ],
        ]);

Another option is to use a "named" encoder and then select which encoder
you want to use dynamically.

In the previous example, you've set the ``auto`` algorithm for ``App\Entity\User``.
This may be secure enough for a regular user, but what if you want your admins
to have a stronger algorithm, for example ``auto`` with a higher cost. This can
be done with named encoders:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            encoders:
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
                <encoder class="harsh"
                    algorithm="auto"
                    cost="15"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            // ...
            'encoders' => [
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

This creates an encoder named ``harsh``. In order for a ``User`` instance
to use it, the class must implement
:class:`Symfony\\Component\\Security\\Core\\Encoder\\EncoderAwareInterface`.
The interface requires one method - ``getEncoderName()`` - which should return
the name of the encoder to use::

    // src/Acme/UserBundle/Entity/User.php
    namespace Acme\UserBundle\Entity;

    use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class User implements UserInterface, EncoderAwareInterface
    {
        public function getEncoderName()
        {
            if ($this->isAdmin()) {
                return 'harsh';
            }

            return null; // use the default encoder
        }
    }

If you created your own password encoder implementing the
:class:`Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface`,
you must register a service for it in order to use it as a named encoder:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            encoders:
                app_encoder:
                    id: 'App\Security\Encoder\MyCustomPasswordEncoder'

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
                <encoder class="app_encoder"
                    id="App\Security\Encoder\MyCustomPasswordEncoder"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        // ...
        use App\Security\Encoder\MyCustomPasswordEncoder;

        $container->loadFromExtension('security', [
            // ...
            'encoders' => [
                'app_encoder' => [
                    'id' => MyCustomPasswordEncoder::class,
                ],
            ],
        ]);

This creates an encoder named ``app_encoder`` from a service with the ID
``App\Security\Encoder\MyCustomPasswordEncoder``.

.. _`libsodium`: https://pecl.php.net/package/libsodium
