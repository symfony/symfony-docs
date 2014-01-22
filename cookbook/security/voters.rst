.. index::
   single: Security; Voters

How to implement your own Voter to blacklist IP Addresses
=========================================================

The Symfony2 Security component provides several layers to authorize users.
One of the layers is called a "voter". A voter is a dedicated class that checks
if the user has the rights to connect to the application or access a specific
resource/URL. For instance, Symfony2 provides a layer that checks if the user
is fully authorized or if it has some expected roles.

It is sometimes useful to create a custom voter to handle a specific case not
handled by the framework. In this section, you'll learn how to create a voter
that will allow you to blacklist users by their IP.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which requires the following three methods:

.. include:: /cookbook/security/voter_interface.rst.inc

In this example, you'll check if the user's IP address matches against a list of
blacklisted addresses and "something" will be the application. If the user's IP is blacklisted, you'll return
``VoterInterface::ACCESS_DENIED``, otherwise you'll return
``VoterInterface::ACCESS_ABSTAIN`` as this voter's purpose is only to deny
access, not to grant access.

Creating a Custom Voter
-----------------------

To blacklist a user based on its IP, you can use the ``request`` service
and compare the IP address against a set of blacklisted IP addresses:

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authorization/Voter/ClientIpVoter.php
    namespace Acme\DemoBundle\Security\Authorization\Voter;

    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    class ClientIpVoter implements VoterInterface
    {
        protected $requestStack;
        private $blacklistedIp;

        public function __construct(RequestStack $requestStack, array $blacklistedIp = array())
        {
            $this->requestStack  = $requestStack;
            $this->blacklistedIp = $blacklistedIp;
        }

        public function supportsAttribute($attribute)
        {
            // you won't check against a user attribute, so return true
            return true;
        }

        public function supportsClass($class)
        {
            // your voter supports all type of token classes, so return true
            return true;
        }

        public function vote(TokenInterface $token, $object, array $attributes)
        {
            $request = $this->requestStack->getCurrentRequest();
            if (in_array($request->getClientIp(), $this->blacklistedIp)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_ABSTAIN;
        }
    }

That's it! The voter is done. The next step is to inject the voter into
the security layer. This can be done easily through the service container.

.. tip::

    Your implementation of the methods
    :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsAttribute`
    and :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsClass`
    are not being called internally by the framework. Once you have registered your
    voter the ``vote()`` method will always be called, regardless of whether
    or not these two methods return true. Therefore you need to call those
    methods in your implementation of the ``vote()`` method and return ``ACCESS_ABSTAIN``
    if your voter does not support the class or attribute.

Declaring the Voter as a Service
--------------------------------

To inject the voter into the security layer, you must declare it as a service,
and tag it as a ``security.voter``:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml
        services:
            security.access.blacklist_voter:
                class:      Acme\DemoBundle\Security\Authorization\Voter\ClientIpVoter
                arguments:  ["@request_stack", [123.123.123.123, 171.171.171.171]]
                public:     false
                tags:
                    - { name: security.voter }

    .. code-block:: xml

        <!-- src/Acme/AcmeBundle/Resources/config/services.xml -->
        <service id="security.access.blacklist_voter"
                 class="Acme\DemoBundle\Security\Authorization\Voter\ClientIpVoter" public="false">
            <argument type="service" id="request_stack" strict="false" />
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
                new Reference('request_stack'),
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

.. _security-voters-change-strategy:

Changing the Access Decision Strategy
-------------------------------------

In order for the new voter to take effect, you need to change the default access
decision strategy, which, by default, grants access if *any* voter grants
access.

In this case, choose the ``unanimous`` strategy. Unlike the ``affirmative``
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
                # strategy can be: affirmative, unanimous or consensus
                strategy: unanimous

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <config>
            <!-- strategy can be: affirmative, unanimous or consensus -->
            <access-decision-manager strategy="unanimous">
        </config>

    .. code-block:: php

        // app/config/security.xml
        $container->loadFromExtension('security', array(
            // strategy can be: affirmative, unanimous or consensus
            'access_decision_manager' => array(
                'strategy' => 'unanimous',
            ),
        ));

That's it! Now, when deciding whether or not a user should have access,
the new voter will deny access to any user in the list of blacklisted IPs.

.. seealso::

    For a more advanced usage see
    :ref:`components-security-access-decision-manager`.
