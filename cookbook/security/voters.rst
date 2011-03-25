.. index::
   single: Security, Voters

How to implement your own voter
===============================

The Symfony2 security component provide several layers to authenticate users.
One of this layer is called a `voter`. A voter is a dedicated class that check
if the user has the rights to be connected to the application. For instance,
Symfony2 provide some layer that check if the user is fully authenticated or if
it has the expected roles.

It sometimes usefull to create a custom voter to handle a specific case not handled
by the framework. In this section, we will discover how to create a voter which
allow to blacklist users by theirs IP.

The voter interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`::
which requires to create three methods:

.. code-block:: php

    interface VoterInterface
    {
        function supportsAttribute($attribute);
        function supportsClass($class);
        function vote(TokenInterface $token, $object, array $attributes);
    }


The ``supportsAttribute()`` method is used to check if the voter supports the given
user attribute (i.e: a role, an acl, etc.)

The ``supportsClass()`` method is used to check if the voter supports the current user
token class.

The ``vote()`` method must implement the business logic that verify whether the user
granted or not. This method must return one of the following value:

* ``VoterInterface::ACCESS_GRANTED``: The user is allowed to access the application
* ``VoterInterface::ACCESS_ABSTAIN``: The voter cannot decide if the user is granted or not
* ``VoterInterface::ACCESS_DENIED``: The user is not allowed to access the application

In our case, we will check if the user's ip is not blacklisted. We will return
``VoterInterface::ACCESS_ABSTAIN`` or ``VoterInterface::ACCESS_GRANTED`` depending of this
criteria.


Creating a custom voter
-----------------------

To blacklist a user based on its current IP, we will use the ``request`` service,
and check if the IP address is not in a given set of blacklisted IP.


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


That's it! The voter is done. We must now inject it into the security layer. This can be
done easily throught the dependency injection container.

Declaring the voter as a service
--------------------------------

To inject the voter into the security layer, we must declare it as a service, and tag it as
a "security.voter":


.. configuration-block:

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

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml

        security.access.blacklist_voter:
            class: Acme\DemoBundle\Security\Authorization\Voter
            arguments: [@request, [123.123.123.123, 171.171.171.171]]


.. tip::

    You can create your own extension to allow the blacklisting configuration to be done
    in the application config.yml file.

At last, we need to change the authentication strategy: by default, the security component
call each voter until one of them grants the user. In our case, we want all voters to
grant the user to authorized it to access the application. To do that, we need to change
the strategy by overriding the ``security.access.decision_manager.strategy`` parameter:



.. configuration-block::

    .. code-block:: xml

        <!-- src/Acme/AcmeBundle/Resources/config/services.xml -->
        <parameter key="security.access.decision_manager.strategy">unanimous</parameter>

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml
        parameters:
            security.access.decision_manager.strategy: unanimous
