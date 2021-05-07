.. TODO from reference/configuration/security.rst

hashers
-------

This option defines the algorithm used to *hash* the password of the users
(which in previous Symfony versions was wrongly called *"password encoding"*).

If your app defines more than one user class, each of them can define its own
hashing algorithm. Also, each algorithm defines different config options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            password_hashers:
                # auto hasher with default options
                App\Entity\User: 'auto'

                # auto hasher with custom options
                App\Entity\User:
                    algorithm: 'auto'
                    cost:      15

                # Sodium hasher with default options
                App\Entity\User: 'sodium'

                # Sodium hasher with custom options
                App\Entity\User:
                    algorithm:   'sodium'
                    memory_cost:  16384 # Amount in KiB. (16384 = 16 MiB)
                    time_cost:    2     # Number of iterations

                # MessageDigestPasswordHasher hasher using SHA512 hashing with default options
                App\Entity\User: 'sha512'

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
                <!-- auto hasher with default options -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="auto"
                />

                <!-- auto hasher with custom options -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="auto"
                    cost="15"
                />

                <!-- Sodium hasher with default options -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="sodium"
                />

                <!-- Sodium hasher with custom options -->
                <!-- memory_cost: amount in KiB. (16384 = 16 MiB)
                     time_cost: number of iterations -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="sodium"
                    memory_cost="16384"
                    time_cost="2"
                />

                <!-- MessageDigestPasswordHasher hasher using SHA512 hashing with default options -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="sha512"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            // auto hasher with default options
            $security->passwordHasher(User::class)
                ->algorithm('auto');

            // auto hasher with custom options
            $security->passwordHasher(User::class)
                ->algorithm('auto')
                ->cost(15);

            // Sodium hasher with default options
            $security->passwordHasher(User::class)
                ->algorithm('sodium');

            // Sodium hasher with custom options
            $security->passwordHasher(User::class)
                ->algorithm('sodium')
                ->memoryCost(16384) // Amount in KiB. (16384 = 16 MiB)
                ->timeCost(2);      // Number of iterations

            // MessageDigestPasswordHasher hasher using SHA512 hashing with default options
            $security->passwordHasher(User::class)
                ->algorithm('sha512');
        };

.. versionadded:: 5.3

    The ``password_hashers`` option was introduced in Symfony 5.3. In previous
    versions it was called ``encoders``.

.. tip::

    You can also create your own password hashers as services and you can even
    select a different password hasher for each user instance. Read
    :doc:`this article </security/named_hashers>` for more details.

.. tip::

    Hashing passwords is resource intensive and takes time in order to generate
    secure password hashes. In tests however, secure hashes are not important, so
    you can change the password hasher configuration in ``test`` environment to
    run tests faster:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/test/security.yaml
            password_hashers:
                # Use your user class name here
                App\Entity\User:
                    algorithm: auto # This should be the same value as in config/packages/security.yaml
                    cost: 4 # Lowest possible value for bcrypt
                    time_cost: 3 # Lowest possible value for argon
                    memory_cost: 10 # Lowest possible value for argon

        .. code-block:: xml

            <!-- config/packages/test/security.xml -->
            <?xml version="1.0" encoding="UTF-8"?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <config>
                    <!-- class: Use your user class name here -->
                    <!-- algorithm: This should be the same value as in config/packages/security.yaml -->
                    <!-- cost: Lowest possible value for bcrypt -->
                    <!-- time_cost: Lowest possible value for argon -->
                    <!-- memory_cost: Lowest possible value for argon -->
                    <security:password-hasher
                        class="App\Entity\User"
                        algorithm="auto"
                        cost="4"
                        time_cost="3"
                        memory_cost="10"
                    />
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/test/security.php
            use App\Entity\User;
            use Symfony\Config\SecurityConfig;

            return static function (SecurityConfig $security) {
                // ...

                // Use your user class name here
                $security->passwordHasher(User::class)
                    ->algorithm('auto') // This should be the same value as in config/packages/security.yaml
                    ->cost(4) // Lowest possible value for bcrypt
                    ->timeCost(2) // Lowest possible value for argon
                    ->memoryCost(10) // Lowest possible value for argon
                ;
            };


.. _reference-security-encoder-auto:
.. _using-the-auto-password-encoder:

Using the "auto" Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It automatically selects the best available hasher. Starting from Symfony 5.3,
it uses the Bcrypt hasher. If PHP or Symfony adds new password hashers in the
future, it might select a different hasher.

Because of this, the length of the hashed passwords may change in the future, so
make sure to allocate enough space for them to be persisted (``varchar(255)``
should be a good setting).

.. _reference-security-encoder-bcrypt:

Using the Bcrypt Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It produces hashed passwords with the `bcrypt password hashing function`_.
Hashed passwords are ``60`` characters long, so make sure to
allocate enough space for them to be persisted. Also, passwords include the
`cryptographic salt`_ inside them (it's generated automatically for each new
password) so you don't have to deal with it.

Its only configuration option is ``cost``, which is an integer in the range of
``4-31`` (by default, ``13``). Each single increment of the cost **doubles the
time** it takes to hash a password. It's designed this way so the password
strength can be adapted to the future improvements in computation power.

You can change the cost at any time — even if you already have some passwords
hashed using a different cost. New passwords will be hashed using the new
cost, while the already hashed ones will be validated using a cost that was
used back when they were hashed.

.. tip::

    A simple technique to make tests much faster when using BCrypt is to set
    the cost to ``4``, which is the minimum value allowed, in the ``test``
    environment configuration.

.. _reference-security-sodium:
.. _using-the-argon2i-password-encoder:
.. _using-the-sodium-password-encoder:

Using the Sodium Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It uses the `Argon2 key derivation function`_. Argon2 support was introduced
in PHP 7.2 by bundeling the `libsodium`_ extension.

The hashed passwords are ``96`` characters long, but due to the hashing
requirements saved in the resulting hash this may change in the future, so make
sure to allocate enough space for them to be persisted. Also, passwords include
the `cryptographic salt`_ inside them (it's generated automatically for each new
password) so you don't have to deal with it.

.. _reference-security-pbkdf2:
.. _using-the-pbkdf2-encoder:

Using the PBKDF2 Hasher
~~~~~~~~~~~~~~~~~~~~~~~

Using the `PBKDF2`_ hasher is no longer recommended since PHP added support for
Sodium and BCrypt. Legacy application still using it are encouraged to upgrade
to those newer hashing algorithms.

.. TODO from security/password_migration.rst

How to Migrate a Password Hash
==============================

In order to protect passwords, it is recommended to store them using the latest
hash algorithms. This means that if a better hash algorithm is supported on your
system, the user's password should be *rehashed* using the newer algorithm and
stored. That's possible with the ``migrate_from`` option:

#. `Configure a new Hasher Using "migrate_from"`_
#. `Upgrade the Password`_
#. Optionally, `Trigger Password Migration From a Custom Hasher`_

.. _configure-a-new-encoder-using migrate_from:

Configure a new Hasher Using "migrate_from"
-------------------------------------------

When a better hashing algorithm becomes available, you should keep the existing
hasher(s), rename it, and then define the new one. Set the ``migrate_from`` option
on the new hasher to point to the old, legacy hasher(s):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            password_hashers:
                # a hasher used in the past for some users
                legacy:
                    algorithm: sha256
                    encode_as_base64: false
                    iterations: 1

                App\Entity\User:
                    # the new hasher, along with its options
                    algorithm: sodium
                    migrate_from:
                        - bcrypt # uses the "bcrypt" hasher with the default options
                        - legacy # uses the "legacy" hasher configured above

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:security="http://symfony.com/schema/dic/security"
            xsi:schemaLocation="http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <security:config>
                <!-- ... -->

                <security:password-hasher class="legacy"
                    algorithm="sha256"
                    encode-as-base64="false"
                    iterations="1"
                />

                <!-- algorithm: the new hasher, along with its options -->
                <security:password-hasher class="App\Entity\User"
                    algorithm="sodium"
                >
                    <!-- uses the bcrypt hasher with the default options -->
                    <security:migrate-from>bcrypt</security:migrate-from>

                    <!-- uses the legacy hasher configured above -->
                    <security:migrate-from>legacy</security:migrate-from>
                </security:password-hasher>
            </security:config>
        </container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->passwordHasher('legacy')
                ->algorithm('sha256')
                ->encodeAsBase64(true)
                ->iterations(1)
            ;

            $security->passwordHasher('App\Entity\User')
                // the new hasher, along with its options
                ->algorithm('sodium')
                ->migrateFrom([
                    'bcrypt', // uses the "bcrypt" hasher with the default options
                    'legacy', // uses the "legacy" hasher configured above
                ])
            ;
        };

With this setup:

* New users will be hashed with the new algorithm;
* Whenever a user logs in whose password is still stored using the old algorithm,
  Symfony will verify the password with the old algorithm and then rehash
  and update the password using the new algorithm.

.. tip::

    The *auto*, *native*, *bcrypt* and *argon* hashers automatically enable
    password migration using the following list of ``migrate_from`` algorithms:

    #. :ref:`PBKDF2 <reference-security-pbkdf2>` (which uses :phpfunction:`hash_pbkdf2`);
    #. Message digest (which uses :phpfunction:`hash`)

    Both use the ``hash_algorithm`` setting as the algorithm. It is recommended to
    use ``migrate_from`` instead of ``hash_algorithm``, unless the *auto*
    hasher is used.

Upgrade the Password
--------------------

Upon successful login, the Security system checks whether a better algorithm
is available to hash the user's password. If it is, it'll hash the correct
password using the new hash. If you use a Guard authenticator, you first need to
:ref:`provide the original password to the Security system <provide-the-password-guard>`.

You can enable the upgrade behavior by implementing how this newly hashed
password should be stored:

* :ref:`When using Doctrine's entity user provider <upgrade-the-password-doctrine>`
* :ref:`When using a custom user provider <upgrade-the-password-custom-provider>`

After this, you're done and passwords are always hashed as secure as possible!

.. _provide-the-password-guard:

Provide the Password when using Guard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When you're using a custom :doc:`guard authenticator </security/guard_authentication>`,
you need to implement :class:`Symfony\\Component\\Security\\Guard\\PasswordAuthenticatedInterface`.
This interface defines a ``getPassword()`` method that returns the password
for this login request. This password is used in the migration process::

    // src/Security/CustomAuthenticator.php
    namespace App\Security;

    use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
    // ...

    class CustomAuthenticator extends AbstractGuardAuthenticator implements PasswordAuthenticatedInterface
    {
        // ...

        public function getPassword($credentials): ?string
        {
            return $credentials['password'];
        }
    }

.. _upgrade-the-password-doctrine:

Upgrade the Password when using Doctrine
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the :ref:`entity user provider <security-entity-user-provider>`, implement
:class:`Symfony\\Component\\Security\\Core\\User\\PasswordUpgraderInterface` in
the ``UserRepository`` (see `the Doctrine docs for information`_ on how to
create this class if it's not already created). This interface implements
storing the newly created password hash::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    // ...
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

    class UserRepository extends EntityRepository implements PasswordUpgraderInterface
    {
        // ...

        public function upgradePassword(UserInterface $user, string $newHashedPassword): void
        {
            // set the new hashed password on the User object
            $user->setPassword($newHashedPassword);

            // execute the queries on the database
            $this->getEntityManager()->flush();
        }
    }

.. _upgrade-the-password-custom-provider:

Upgrade the Password when using a Custom User Provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using a :ref:`custom user provider <custom-user-provider>`, implement the
:class:`Symfony\\Component\\Security\\Core\\User\\PasswordUpgraderInterface` in
the user provider::

    // src/Security/UserProvider.php
    namespace App\Security;

    // ...
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

    class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
    {
        // ...

        public function upgradePassword(UserInterface $user, string $newHashedPassword): void
        {
            // set the new hashed password on the User object
            $user->setPassword($newHashedPassword);

            // ... store the new password
        }
    }

.. _trigger-password-migration-from-a-custom-encoder:

Trigger Password Migration From a Custom Hasher
-----------------------------------------------

If you're using a custom password hasher, you can trigger the password
migration by returning ``true`` in the ``needsRehash()`` method::

    // src/Security/CustomPasswordHasher.php
    namespace App\Security;

    // ...
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    class CustomPasswordHasher implements UserPasswordHasherInterface
    {
        // ...

        public function needsRehash(string $hashed): bool
        {
            // check whether the current password is hashed using an outdated hasher
            $hashIsOutdated = ...;

            return $hashIsOutdated;
        }
    }

.. todo from security/named_hashers.rst

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
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->passwordHasher(User::class)
                ->algorithm('auto')
                ->cost(12)
            ;
        };

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
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->passwordHasher('harsh')
                ->algorithm('auto')
                ->cost(15)
            ;
        };

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
:class:`Symfony\\Component\\PasswordHasher\\PasswordHasherInterface`,
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
        use App\Security\Hasher\MyCustomPasswordHasher;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->passwordHasher('app_hasher')
                ->id(MyCustomPasswordHasher::class)
            ;
        };

This creates a hasher named ``app_hasher`` from a service with the ID
``App\Security\Hasher\MyCustomPasswordHasher``.

.. _`libsodium`: https://pecl.php.net/package/libsodium

.. _`the Doctrine docs for information`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/working-with-objects.html#custom-repositories
