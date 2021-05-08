.. index::
    single: Security; How to Migrate a Password Hash

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
        $container->loadFromExtension('security', [
            // ...

            'password_hashers' => [
                'legacy' => [
                    'algorithm' => 'sha256',
                    'encode_as_base64' => false,
                    'iterations' => 1,
                ],

                'App\Entity\User' => [
                    // the new hasher, along with its options
                    'algorithm' => 'sodium',
                    'migrate_from' => [
                        'bcrypt', // uses the "bcrypt" hasher with the default options
                        'legacy', // uses the "legacy" hasher configured above
                    ],
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

.. _`the Doctrine docs for information`: https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/working-with-objects.html#custom-repositories
