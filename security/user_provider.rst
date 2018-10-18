All about User Providers
========================

Each User class in your app will usually need its own "user provider": a class
that has two jobs:

**Reload the User from the Session**
    At the beginning of each request (unless your firewall is ``stateless``), Symfony
    loads the ``User`` object from the session. To make sure it's not out-of-date,
    the user provider "refreshes it". The Doctrine user provider, for example,
    queries the database for fresh data. Symfony then checks to see if the user
    has "changed" and de-authenticates the user if they have (see :ref:`user_session_refresh`).

**Load the User for some Feature**
    Some features, like ``switch_user``, ``remember_me`` and many of the built-in
    :doc:`authentication providers </security/auth_providers>`, use the user provider
    to load a User object via is "username" (or email, or whatever field you want).

Symfony comes with several built-in user providers:

.. toctree::
    :hidden:

    entity_provider

* :doc:`entity: (load users from the database) </security/entity_provider>`
* :doc:`ldap </security/ldap>`
* ``memory`` (users are hardcoded in config)
* ``chain`` (try multiple user providers)

Or you can create a :ref:`custom user provider <custom-user-provider>`.

User providers are configured in ``config/packages/security.yaml`` under the
``providers`` key, and each has different configuration options:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...
        providers:
            # this becomes the internal name of the provider
            # not usually important, but can be used to specify which
            # provider you want for which firewall (advanced case) or
            # for a specific authentication provider
            some_provider_key:

                # provider type - one of the above
                memory:
                    # custom options for that provider
                    users:
                        user:  { password: '%env(USER_PASSWORD)%', roles: [ 'ROLE_USER' ] }
                        admin: { password: '%env(ADMIN_PASSWORD)%', roles: [ 'ROLE_ADMIN' ] }

            a_chain_provider:
                chain:
                    providers: [some_provider_key, another_provider_key]

.. _custom-user-provider:

Creating a Custom User Provider
-------------------------------

If you're loading users from a custom location (e.g. via an API or legacy database
connection), you'll need to create a custom user provider class. First, make sure
you've followed the :doc:`Security Guide </security>` to create your ``User`` class.

If you used the ``make:user`` command to create your ``User`` class (and you answered
the questions indicating that you need a custom user provider), that command will
generate a nice skeleton to get you started::

    // .. src/Security/UserProvider.php
    namespace App\Security;

    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\User\UserProviderInterface;

    class UserProvider implements UserProviderInterface
    {
        /**
         * Symfony calls this method if you use features like switch_user
         * or remember_me.
         *
         * If you're not using these features, you do not need to implement
         * this method.
         *
         * @return UserInterface
         *
         * @throws UsernameNotFoundException if the user is not found
         */
        public function loadUserByUsername($username)
        {
            // Load a User object from your data source or throw UsernameNotFoundException.
            // The $username argument may not actually be a username:
            // it is whatever value is being returned by the getUsername()
            // method in your User class.
            throw new \Exception('TODO: fill in loadUserByUsername() inside '.__FILE__);
        }

        /**
         * Refreshes the user after being reloaded from the session.
         *
         * When a user is logged in, at the beginning of each request, the
         * User object is loaded from the session and then this method is
         * called. Your job is to make sure the user's data is still fresh by,
         * for example, re-querying for fresh User data.
         *
         * If your firewall is "stateless: false" (for a pure API), this
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
            // Or throw a UsernameNotFoundException if the user no longer exists.
            throw new \Exception('TODO: fill in refreshUser() inside '.__FILE__);
        }

        /**
         * Tells Symfony to use this provider for this User class.
         */
        public function supportsClass($class)
        {
            return User::class === $class;
        }
    }

Most of the work is already done! Read the comments in the code and update the TODO
sections to finish the user provider.

When you're done, tell Symfony about the user provider by adding it in ``security.yaml``:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        providers:
            # internal name - can be anything
            your_custom_user_provider:
                id: App\Security\UserProvider

That's it! When you use any of the features that require a user provider, your
provider will be used! If you have multiple firewalls and multiple providers,
you can specify *which* provider to use by adding a ``provider`` key under your
firewall and setting it to the internal name you gave to your user provider.

.. _user_session_refresh:

Understanding how Users are Refreshed from the Session
------------------------------------------------------

At the end of every request (unless your firewall is ``stateless``), your ``User``
object is serialized to the session. At the beginning of the next request, it's
deserialized and then passed to your user provider to "refresh" it (e.g. Doctrine
queries for a fresh user).

Then, the two User objects (the original from the session and the refreshed User
object) are "compared" to see if they are "equal". By default, the core
``AbstractToken`` class compares the return values of the ``getPassword()``,
``getSalt()`` and ``getUsername()`` methods. If any of these are different, your
user will be logged out. This is a security measure to make sure that malicious
users can be de-authenticated if core user data changes.

However, in some cases, this process can cause unexpected authentication problems.
If you're having problems authenticating, it could be that you *are* authenticating
successfully, but you immediately lose authentication after the first redirect.

In that case, review the serialization logic (e.g. ``SerializableInterface``) if
you have any, to make sure that all the fields necessary are serialized.

Comparing Users Manually with EquatableInterface
------------------------------------------------

Or, if you need more control over the "compare users" process, make your User class
implement :class:`Symfony\\Component\\Security\\Core\\User\\EquatableInterface`.
Then, your ``isEqualTo()`` method will be called when comparing users.

