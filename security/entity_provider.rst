.. index::
   single: Security; User provider
   single: Security; Entity provider

How to Load Security Users from the Database (the Entity Provider)
==================================================================

Each User class in your app will usually need its own :doc:`user provider </security/user_provider>`.
If you're loading users from the database, you can use the built-in ``entity`` provider:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
            # ...

            providers:
                our_db_provider:
                    entity:
                        class: App\Entity\User
                        # the property to query by - e.g. username, email, etc
                        property: username
                        # if you're using multiple entity managers
                        # manager_name: customer

            # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <provider name="our_db_provider">
                    <!-- if you're using multiple entity managers, add:
                         manager-name="customer" -->
                    <entity class="App\Entity\User" property="username" />
                </provider>

                <!-- ... -->
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', array(
            'providers' => array(
                'our_db_provider' => array(
                    'entity' => array(
                        'class'    => User::class,
                        'property' => 'username',
                    ),
                ),
            ),

            // ...
        ));

The ``providers`` section creates a "user provider" called ``our_db_provider`` that
knows to query from your ``App\Entity\User`` entity by the ``username`` property.
The name ``our_db_provider`` isn't important: it's not used, unless you have multiple
user providers and need to specify which user provider to use via the ``provider``
key under your firewall.

.. _authenticating-someone-with-a-custom-entity-provider:

Using a Custom Query to Load the User
-------------------------------------

The ``entity`` provider can only query from one *specific* field, specified by the
``property`` config key. If you want a bit more control over this - e.g. you want
to find a user by ``email`` *or* ``username``, you can do that by making your
``UserRepository`` implement a special
:class:`Symfony\\Bridge\\Doctrine\\Security\\User\\UserLoaderInterface`. This
interface only requires one method: ``loadUserByUsername($username)``::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
    use Doctrine\ORM\EntityRepository;

    class UserRepository extends EntityRepository implements UserLoaderInterface
    {
        public function loadUserByUsername($username)
        {
            return $this->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult();
        }
    }

To finish this, remove the ``property`` key from the user provider in
``security.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            providers:
                our_db_provider:
                    entity:
                        class: App\Entity\User

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <provider name="our_db_provider">
                    <entity class="App\Entity\User" />
                </provider>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Entity\User;

        $container->loadFromExtension('security', array(
            // ...

            'providers' => array(
                'our_db_provider' => array(
                    'entity' => array(
                        'class' => User::class,
                    ),
                ),
            ),
        ));

This tells Symfony to *not* query automatically for the User. Instead, when needed
(e.g. because ``switch_user``, ``remember_me`` or some other security feature is
activated), the ``loadUserByUsername()`` method on ``UserRepository`` will be called.
