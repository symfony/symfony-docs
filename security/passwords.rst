Password Hashing and Verification
=================================

Most applications use passwords to login users. These passwords should be
hashed to securely store them. Symfony's PasswordHasher component provides
all utilities to safely hash and verify passwords.

Make sure it is installed by running:

.. code-block:: terminal

   $ composer require symfony/password-hasher

.. versionadded:: 5.3

   The PasswordHasher component was introduced in 5.3. Prior to this
   version, password hashing functionality was provided by the Security
   component.

Configuring a Password Hasher
-----------------------------

Before hashing passwords, you must configure a hasher using the
``password_hashers`` option. You must configure the *hashing algorithm* and
optionally some *algorithm options*:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            password_hashers:
                # auto hasher with default options for the User class (and children)
                App\Entity\User: 'auto'

                # auto hasher with custom options for all PasswordAuthenticatedUserInterface instances
                Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:
                    algorithm: 'auto'
                    cost:      15

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
                <!-- auto hasher with default options for the User class (and children) -->
                <security:password-hasher
                    class="App\Entity\User"
                    algorithm="auto"
                />

                <!-- auto hasher with custom options for all PasswordAuthenticatedUserInterface instances -->
                <security:password-hasher
                    class="Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface"
                    algorithm="auto"
                    cost="15"
                />
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;
        use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...

            // auto hasher with default options for the User class (and children)
            $security->passwordHasher(User::class)
                ->algorithm('auto');

            // auto hasher with custom options for all PasswordAuthenticatedUserInterface instances
            $security->passwordHasher(PasswordAuthenticatedUserInterface::class)
                ->algorithm('auto')
                ->cost(15);
        };

    .. code-block:: php-standalone

        use App\Entity\User;
        use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
        use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

        $passwordHasherFactory = new PasswordHasherFactory([
            // auto hasher with default options for the User class (and children)
            User::class => ['algorithm' => 'auto'],

            // auto hasher with custom options for all PasswordAuthenticatedUserInterface instances
            PasswordAuthenticatedUserInterface::class => [
                'algorithm' => 'auto',
                'cost' => 15,
            ],
        ]);

.. versionadded:: 5.3

    The ``password_hashers`` option was introduced in Symfony 5.3. In previous
    versions it was called ``encoders``.

In this example, the "auto" algorithm is used. This hasher automatically
selects the most secure algorithm available on your system. Combined with
:ref:`password migration <security-password-migration>`, this allows you to
always secure passwords in the safest way possible (even when new
algorithms are introduced in future PHP releases).

Further in this article, you can find a
:ref:`full reference of all supported algorithms <passwordhasher-supported-algorithms>`.

.. tip::

    Hashing passwords is resource intensive and takes time in order to
    generate secure password hashes. In general, this makes your password
    hashing more secure.

    In tests however, secure hashes are not important, so you can change
    the password hasher configuration in ``test`` environment to run tests
    faster:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/test/security.yaml
            security:
                # ...

                password_hashers:
                    # Use your user class name here
                    App\Entity\User:
                        algorithm: plaintext # disable hashing (only do this in tests!)

                    # or use the lowest possible values
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
                    <!-- algorithm: disable hashing (only do this in tests!) -->
                    <security:password-hasher
                        class="App\Entity\User"
                        algorithm="plaintext"
                    />

                    <!-- or use the lowest possible values -->
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
                    ->algorithm('plaintext'); // disable hashing (only do this in tests!)

                // or use the lowest possible values
                $security->passwordHasher(User::class)
                    ->algorithm('auto') // This should be the same value as in config/packages/security.yaml
                    ->cost(4) // Lowest possible value for bcrypt
                    ->timeCost(2) // Lowest possible value for argon
                    ->memoryCost(10) // Lowest possible value for argon
                ;
            };

Hashing the Password
--------------------

After configuring the correct algorithm, you can use the
``UserPasswordHasherInterface`` to hash and verify the passwords:

.. configuration-block::

    .. code-block:: php-symfony

        // src/Controller/RegistrationController.php
        namespace App\Controller;

        // ...
        use
        Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
        use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

        class UserController extends AbstractController
        {
            public function registration(UserPasswordHasherInterface $passwordHasher)
            {
                // ... e.g. get the user data from a registration form
                $user = new User(...);
                $plaintextPassword = ...;

                // hash the password (based on the security.yaml config for the $user class)
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);

                // ...
            }

            public function delete(UserPasswordHasherInterface $passwordHasher, UserInterface $user): void
            {
                // ... e.g. get the password from a "confirm deletion" dialog
                $plaintextPassword = ...;

                if (!$passwordHasher->isPasswordValid($user, $plaintextPassword)) {
                    throw new AccessDeniedHttpException();
                }
            }
        }

    .. code-block:: php-standalone

        // ...
        $passwordHasher = new UserPasswordHasher($passwordHasherFactory);

        // Get the user password (e.g. from a registration form)
        $user = new User(...);
        $plaintextPassword = ...;

        // hash the password (based on the password hasher factory config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        // In another action (e.g. to confirm deletion), you can verify the password
        $plaintextPassword = ...;
        if (!$passwordHasher->isPasswordValid($user, $plaintextPassword)) {
            throw new \Exception('Bad credentials, cannot delete this user.');
        }

Reset Password
--------------

Using `MakerBundle`_ and `SymfonyCastsResetPasswordBundle`_, you can create
a secure out of the box solution to handle forgotten passwords. First,
install the SymfonyCastsResetPasswordBundle:

.. code-block:: terminal

    $ composer require symfonycasts/reset-password-bundle

Then, use the ``make:reset-password`` command. This asks you a few
questions about your app and generates all the files you need! After,
you'll see a success message and a list of any other steps you need to do.

.. code-block:: terminal

    $ php bin/console make:reset-password

You can customize the reset password bundle's behavior by updating the
``reset_password.yaml`` file. For more information on the configuration,
check out the `SymfonyCastsResetPasswordBundle`_  guide.

.. _security-password-migration:

Password Migration
------------------

In order to protect passwords, it is recommended to store them using the latest
hash algorithms. This means that if a better hash algorithm is supported on your
system, the user's password should be *rehashed* using the newer algorithm and
stored. That's possible with the ``migrate_from`` option:

#. `Configure a new Hasher Using "migrate_from"`_
#. `Upgrade the Password`_
#. Optionally, `Trigger Password Migration From a Custom Hasher`_

Configure a new Hasher Using "migrate_from"
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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

    .. code-block:: php-standalone

        // ...
        $passwordHasherFactory = new PasswordHasherFactory([
            'legacy' => [
                'algorithm' => 'sha256',
                'encode_as_base64' => true,
                'iterations' => 1,
            ],

            User::class => [
                // the new hasher, along with its options
                'algorithm' => 'sodium',
                'migrate_from' => [
                    'bcrypt', // uses the "bcrypt" hasher with the default options
                    'legacy', // uses the "legacy" hasher configured above
                ],
            ],
        ]);

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
~~~~~~~~~~~~~~~~~~~~

Upon successful login, the Security system checks whether a better algorithm
is available to hash the user's password. If it is, it'll hash the correct
password using the new hash. When using a custom authenticator, you must
use the ``PasswordCredentials`` in the :ref:`security passport <security-passport>`.

You can enable the upgrade behavior by implementing how this newly hashed
password should be stored:

* :ref:`When using Doctrine's entity user provider <upgrade-the-password-doctrine>`
* :ref:`When using a custom user provider <upgrade-the-password-custom-provider>`

After this, you're done and passwords are always hashed as securely as possible!

.. note::

    When using the PasswordHasher component outside a Symfony application,
    you must manually use the ``PasswordHasherInterface::needsRehash()``
    method to check if a rehash is needed and ``PasswordHasherInterface::hash()``
    method to rehash the plaintext password using the new algorithm.

.. _upgrade-the-password-doctrine:

Upgrade the Password when using Doctrine
........................................

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
......................................................

If you're using a :ref:`custom user provider <security-custom-user-provider>`, implement the
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

Trigger Password Migration From a Custom Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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

Named Password Hashers
----------------------

Usually, the same password hasher is used for all users by configuring it
to apply to all instances of a specific class. Another option is to use a
"named" hasher and then select which hasher you want to use dynamically.

By default (as shown at the start of the article), the ``auto`` algorithm
is used for ``App\Entity\User``.

This may be secure enough for a regular user, but what if you want your
admins to have a stronger algorithm, for example ``auto`` with a higher
cost. This can be done with named hashers:

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

    .. code-block:: php-standalone

        use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

        $passwordHasherFactory = new PasswordHasherFactory([
            // ...
            'harsh' => [
                'algorithm' => 'auto',
                'cost' => 15
            ],
        ]);

This creates a hasher named ``harsh``. In order for a ``User`` instance
to use it, the class must implement
:class:`Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherAwareInterface`.
The interface requires one method - ``getPasswordHasherName()`` - which should return
the name of the hasher to use::

    // src/Entity/User.php
    namespace App\Entity;

    use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class User implements
        UserInterface,
        PasswordAuthenticatedUserInterface,
        PasswordHasherAwareInterface
    {
        // ...

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

Hashing a Stand-Alone String
----------------------------

The password hasher can be used to hash strings independently
of users. By using the
:class:`Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherFactory`,
you can declare multiple hashers, retrieve any of them with
its name and create hashes. You can then verify that a string matches the given
hash::

    use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

    // configure different hashers via the factory
    $factory = new PasswordHasherFactory([
        'common' => ['algorithm' => 'bcrypt'],
        'sodium' => ['algorithm' => 'sodium'],
    ]);

    // retrieve the hasher using bcrypt
    $hasher = $factory->getPasswordHasher('common');
    $hash = $hasher->hash('plain');

    // verify that a given string matches the hash calculated above
    $hasher->verify($hash, 'invalid'); // false
    $hasher->verify($hash, 'plain'); // true

.. _passwordhasher-supported-algorithms:

Supported Algorithms
--------------------

* :ref:`auto <reference-security-encoder-auto>`
* :ref:`bcrypt <reference-security-encoder-bcrypt>`
* :ref:`sodium <reference-security-sodium>`
* :ref:`PBKDF2 <reference-security-pbkdf2>`

* :ref:`Or create a custom password hasher <custom-password-hasher>`

.. TODO missing:
..  * :ref:`Message Digest <reference-security-message-digest>`
..  * :ref:`Native <reference-security-native>`
..  * :ref:`Plaintext <reference-security-plaintext>`

.. _reference-security-encoder-auto:

The "auto"  Hasher
~~~~~~~~~~~~~~~~~~

It automatically selects the best available hasher. Starting from Symfony 5.3,
it uses the Bcrypt hasher. If PHP or Symfony adds new password hashers in the
future, it might select a different hasher.

Because of this, the length of the hashed passwords may change in the future, so
make sure to allocate enough space for them to be persisted (``varchar(255)``
should be a good setting).

.. _reference-security-encoder-bcrypt:

The Bcrypt Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~

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

The Sodium Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~

It uses the `Argon2 key derivation function`_. Argon2 support was introduced
in PHP 7.2 by bundling the `libsodium`_ extension.

The hashed passwords are ``96`` characters long, but due to the hashing
requirements saved in the resulting hash this may change in the future, so make
sure to allocate enough space for them to be persisted. Also, passwords include
the `cryptographic salt`_ inside them (it's generated automatically for each new
password) so you don't have to deal with it.

.. _reference-security-pbkdf2:

The PBKDF2 Hasher
~~~~~~~~~~~~~~~~~

Using the `PBKDF2`_ hasher is no longer recommended since PHP added support for
Sodium and BCrypt. Legacy application still using it are encouraged to upgrade
to those newer hashing algorithms.

.. _custom-password-hasher:

Creating a custom Password Hasher
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to create your own, it needs to follow these rules:

#. The class must implement :class:`Symfony\\Component\\PasswordHasher\\PasswordHasherInterface`
   (you can also implement :class:`Symfony\\Component\\PasswordHasher\\LegacyPasswordHasherInterface` if your hash algorithm uses a separate salt);

#. The implementations of
   :method:`Symfony\\Component\\PasswordHasher\\PasswordHasherInterface::hash`
   and :method:`Symfony\\Component\\PasswordHasher\\PasswordHasherInterface::verify`
   **must validate that the password length is no longer than 4096
   characters.** This is for security reasons (see `CVE-2013-5750`_).

   You can use the :method:`Symfony\\Component\\PasswordHasher\\Hasher\\CheckPasswordLengthTrait::isPasswordTooLong`
   method for this check.

.. code-block:: php

    // src/Security/Hasher/CustomVerySecureHasher.php
    namespace App\Security\Hasher;

    use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
    use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;
    use Symfony\Component\PasswordHasher\PasswordHasherInterface;

    class CustomVerySecureHasher implements PasswordHasherInterface
    {
        use CheckPasswordLengthTrait;

        public function hash(string $plainPassword): string
        {
            if ($this->isPasswordTooLong($plainPassword)) {
                throw new InvalidPasswordException();
            }

            // ... hash the plain password in a secure way

            return $hashedPassword;
        }

        public function verify(string $hashedPassword, string $plainPassword): bool
        {
            if ('' === $plainPassword || $this->isPasswordTooLong($plainPassword)) {
                return false;
            }

            // ... validate if the password equals the user's password in a secure way

            return $passwordIsValid;
        }

        public function needsRehash(string $hashedPassword): bool
        {
            // Check if a password hash would benefit from rehashing
            return $needsRehash;
        }
    }

Now, define a password hasher using the ``id`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            password_hashers:
                app_hasher:
                    # the service ID of your custom hasher (the FQCN using the default services.yaml)
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
                <!-- id: the service ID of your custom hasher (the FQCN using the default services.yaml) -->
                <security:password_hasher class="app_hasher"
                    id="App\Security\Hasher\CustomVerySecureHasher"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\Hasher\CustomVerySecureHasher;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->passwordHasher('app_hasher')
                // the service ID of your custom hasher (the FQCN using the default services.yaml)
                ->id(CustomVerySecureHasher::class)
            ;
        };

.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`PBKDF2`: https://en.wikipedia.org/wiki/PBKDF2
.. _`libsodium`: https://pecl.php.net/package/libsodium
.. _`Argon2 key derivation function`: https://en.wikipedia.org/wiki/Argon2
.. _`bcrypt password hashing function`: https://en.wikipedia.org/wiki/Bcrypt
.. _`cryptographic salt`: https://en.wikipedia.org/wiki/Salt_(cryptography)
.. _`the Doctrine docs for information`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/working-with-objects.html#custom-repositories
.. _`SymfonyCastsResetPasswordBundle`: https://github.com/symfonycasts/reset-password-bundle
.. _`CVE-2013-5750`: https://symfony.com/blog/cve-2013-5750-security-issue-in-fosuserbundle-login-form
