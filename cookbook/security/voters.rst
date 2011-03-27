.. index::
   single: Security, Voters

How to implement your own Voter
===============================

The Symfony2 security component provides several layers to authenticate users.
One of the layers is called a `voter`. A voter is a dedicated class that checks
if the user has the rights to be connected to the application. For instance,
Symfony2 provide a layer that checks if the user is fully authenticated or if
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

In this example, we will check if the user's IP address against a list of
blacklisted addresses. We will return ``VoterInterface::ACCESS_DENIED`` or
``VoterInterface::ACCESS_GRANTED`` depending on this criteria.

Creating a Custom Voter
-----------------------

To blacklist a user based on its IP, we can use the ``request`` service
and compare the IP address against a set of blacklisted IP addresses:

.. code-block:: php

    namespace Acme\DemoBundle\Security\Authorization\Voter;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    class ClientIpVoter implements VoterInterface
    {
        public function __construct(Request $request, array $blacklistedIp = array())
        {
            $this->request       = $request;
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
            if (in_array($this->request->getClientIp(), $this->blacklistedIp)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_GRANTED;
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
                class:      Acme\DemoBundle\Security\Authorization\Voter
                arguments:  [@request, [123.123.123.123, 171.171.171.171]]
                public:     false
                tags:
                    -       { name: security.voter }

    .. code-block:: xml

        <!-- src/Acme/AcmeBundle/Resources/config/services.xml -->

        <service id="security.access.blacklist_voter"
                 class="Acme\DemoBundle\Security\Authorization\Voter" public="false">
            <argument type="service" id="request" strict="false" />
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
            'Acme\DemoBundle\Security\Authorization\Voter',
            array(
                new Reference('request'),
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

Finally, we need to change the authentication strategy. By default, the
security component call each voter until one of them grants access to the
user. In our case, we want to force *all* voters to grant the user access
before deciding that the user should actually have access to the application.
To do that, we need to change the strategy by overriding the
``security.access.decision_manager.strategy`` parameter:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml
        parameters:
            security.access.decision_manager.strategy: unanimous

    .. code-block:: xml

        <!-- src/Acme/AcmeBundle/Resources/config/services.xml -->
        <parameter key="security.access.decision_manager.strategy">unanimous</parameter>

    .. code-block:: php

        // src/Acme/AcmeBundle/Resources/config/services.php

        $container->setParameter('security.access.decision_manager.strategy', 'unanimous');