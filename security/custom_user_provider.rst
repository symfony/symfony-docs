Creating a Custom User Provider
===============================

Most applications don't need to create a custom provider. If you store users in
a database, a LDAP server or a configuration file, Symfony supports that.
However, if you're loading users from a custom location (e.g. via an API or
legacy database connection), you'll need to create a custom user provider.

First, make sure you've followed the :doc:`Security Guide </security>` to create
your ``User`` class.

If you used the ``make:user`` command to create your ``User`` class (and you
answered the questions indicating that you need a custom user provider), that
command will generate a nice skeleton to get you started::

    // src/Security/UserProvider.php
    namespace App\Security;

    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UserNotFoundException;
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
    {
        /**
         * The loadUserByIdentifier() method was introduced in Symfony 5.3.
         * In previous versions it was called loadUserByUsername()
         *
         * Symfony calls this method if you use features like switch_user
         * or remember_me. If you're not using these features, you do not
         * need to implement this method.
         *
         * @throws UserNotFoundException if the user is not found
         */
        public function loadUserByIdentifier(string $identifier): UserInterface
        {
            // Load a User object from your data source or throw UserNotFoundException.
            // The $identifier argument is whatever value is being returned by the
            // getUserIdentifier() method in your User class.
            throw new \Exception('TODO: fill in loadUserByIdentifier() inside '.__FILE__);
        }

        /**
         * Refreshes the user after being reloaded from the session.
         *
         * When a user is logged in, at the beginning of each request, the
         * User object is loaded from the session and then this method is
         * called. Your job is to make sure the user's data is still fresh by,
         * for example, re-querying for fresh User data.
         *
         * If your firewall is "stateless: true" (for a pure API), this
         * method is not called.
         *
         * @return UserInterface
         */
        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
            }

            // Return a User object after making sure its data is "fresh".
            // Or throw a UserNotFoundException if the user no longer exists.
            throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
        }

        /**
         * Tells Symfony to use this provider for this User class.
         */
        public function supportsClass(string $class)
        {
            return User::class === $class || is_subclass_of($class, User::class);
        }

        /**
         * Upgrades the encoded password of a user, typically for using a better hash algorithm.
         */
        public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
        {
            // TODO: when encoded passwords are in use, this method should:
            // 1. persist the new password in the user storage
            // 2. update the $user object with $user->setPassword($newEncodedPassword);
        }
    }

Most of the work is already done! Read the comments in the code and update the
TODO sections to finish the user provider. When you're done, tell Symfony about
the user provider by adding it in ``security.yaml``:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        providers:
            # the name of your user provider can be anything
            your_custom_user_provider:
                id: App\Security\UserProvider

Lastly, update the ``config/packages/security.yaml`` file to set the
``provider`` key to ``your_custom_user_provider`` in all the firewalls which
will use this custom user provider.
