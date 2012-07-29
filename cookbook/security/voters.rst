.. index::
   single: Security; Voters

How to implement your own Voter to blacklist IP Addresses
=========================================================

The Symfony2 security component provides several layers to authenticate users.
One of the layers is called a `voter`. A voter is a dedicated class that checks
if the user has the rights to be connected to the application. For instance,
Symfony2 provides a layer that checks if the user is fully authenticated or if
it has some expected roles.

It is sometimes useful to create a custom voter to handle a specific case not
handled by the framework. In this section, you'll learn how to create a voter
that will allow you to blacklist users by their IP.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which requires the following three methods:

.. code-block:: php

    interface VoterInterface
    {
        function supportsAttribute($attribute);
        function supportsClass($class);
        function vote(TokenInterface $token, $object, array $attributes);
    }


The ``supportsAttribute()`` method is used to check if the voter supports
the given user attribute (i.e: a role, an acl, etc.).

The ``supportsClass()`` method is used to check if the voter supports the
current user token class.

The ``vote()`` method must implement the business logic that verifies whether
or not the user is granted access. This method must return one of the following
values:

* ``VoterInterface::ACCESS_GRANTED``: The user is allowed to access the application
* ``VoterInterface::ACCESS_ABSTAIN``: The voter cannot decide if the user is granted or not
* ``VoterInterface::ACCESS_DENIED``: The user is not allowed to access the application

In this example, we will check if the user's IP address matches against a list of
blacklisted addresses. If the user's IP is blacklisted, we will return 
``VoterInterface::ACCESS_DENIED``, otherwise we will return 
``VoterInterface::ACCESS_ABSTAIN`` as this voter's purpose is only to deny
access, not to grant access.

Creating a Custom Voter
-----------------------

To blacklist a user based on its IP, we can use the ``request`` service
and compare the IP address against a set of blacklisted IP addresses:

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authorization/Voter/ClientIpVoter.php
    namespace Acme\DemoBundle\Security\Authorization\Voter;

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    class ClientIpVoter implements VoterInterface
    {
        public function __construct(ContainerInterface $container, array $blacklistedIp = array())
        {
            $this->container     = $container;
            $this->blacklistedIp = $blacklistedIp;
        }

        public function supportsAttribute($attribute)
        {
            // we won't check against a user attribute, so we return true
            return true;
        }

        public function supportsClass($class)
        {
            // our voter supports all type of token classes, so we return true
            return true;
        }

        function vote(TokenInterface $token, $object, array $attributes)
        {
            $request = $this->container->get('request');
            if (in_array($request->getClientIp(), $this->blacklistedIp)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_ABSTAIN;
        }
    }

That's it! The voter is done. The next step is to inject the voter into
the security layer. This can be done easily through the service container.

Declaring the Voter as a Service
--------------------------------

To inject the voter into the security layer, we must declare it as a service,
and tag it as a "security.voter":

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml
        services:
            security.access.blacklist_voter:
                class:      Acme\DemoBundle\Security\Authorization\Voter\ClientIpVoter
                arguments:  [@service_container, [123.123.123.123, 171.171.171.171]]
                public:     false
                tags:
                    -       { name: security.voter }

    .. code-block:: xml

        <!-- src/Acme/AcmeBundle/Resources/config/services.xml -->
        <service id="security.access.blacklist_voter"
                 class="Acme\DemoBundle\Security\Authorization\Voter\ClientIpVoter" public="false">
            <argument type="service" id="service_container" strict="false" />
            <argument type="collection">
                <argument>123.123.123.123</argument>
                <argument>171.171.171.171</argument>
            </argument>
            <tag name="security.voter" />
        </service>

    .. code-block:: php

        // src/Acme/AcmeBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $definition = new Definition(
            'Acme\DemoBundle\Security\Authorization\Voter\ClientIpVoter',
            array(
                new Reference('service_container'),
                array('123.123.123.123', '171.171.171.171'),
            ),
        );
        $definition->addTag('security.voter');
        $definition->setPublic(false);

        $container->setDefinition('security.access.blacklist_voter', $definition);

.. tip::

   Be sure to import this configuration file from your main application
   configuration file (e.g. ``app/config/config.yml``). For more information
   see :ref:`service-container-imports-directive`. To read more about defining
   services in general, see the :doc:`/book/service_container` chapter.

Changing the Access Decision Strategy
-------------------------------------

In order for the new voter to take effect, we need to change the default access
decision strategy, which, by default, grants access if *any* voter grants
access.

In our case, we will choose the ``unanimous`` strategy. Unlike the ``affirmative``
strategy (the default), with the ``unanimous`` strategy, if only one voter
denies access (e.g. the ``ClientIpVoter``), access is not granted to the
end user.

To do that, override the default ``access_decision_manager`` section of your
application configuration file with the following code.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            access_decision_manager:
                # Strategy can be: affirmative, unanimous or consensus
                strategy: unanimous

That's it! Now, when deciding whether or not a user should have access,
the new voter will deny access to any user in the list of blacklisted IPs.
