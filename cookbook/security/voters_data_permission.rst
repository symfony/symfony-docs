.. index::
   single: Security; Data Permission Voters

How to implement your own Voter to check the permission for a object agains a user
==================================================================================

In Symfony2 you can check the permission to access data by the 
:doc:`ACL module </cookbook/security/acl>` which is a bit overhelming 
for many applications. A much easier solution is working with custom 
voters, which are like simple conditional statements. Voters can be 
also used to check for permission as a part or even the whole 
application: :doc:`cookbook/security/voters`.

.. tip::

    It is good to understand the basics about what and how
    :doc:`authorization </components/security/authorization>` works.

How symfony works with voters
-----------------------------

In order to use voters you have to understand how symfony works with them. 
In general all registered custom voters will be called every time you ask 
symfony about permission (ACL). In general there are three different 
approaches on how to handle the feedback from all voters: 
:ref:`components-security-access-decision-manager`.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which requires the following three methods:

.. code-block:: php

    interface VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, $object, array $attributes);
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

In this example, you'll check if the user will have access to a specific object according to your custom conditions (e.g. he must be the owner of the object). If the condition fails, you'll return
``VoterInterface::ACCESS_DENIED``, otherwise you'll return
``VoterInterface::ACCESS_GRANTED``. In case the responsebility for this decision belong not to this voter, he will return
``VoterInterface::ACCESS_ABSTAIN``.

Creating the Custom Voter
-------------------------

You could store your Voter for the view and edit method of a post within ACME/DemoBundle/Security/Authorization/Document/PostVoter.php.

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authorization/Document/PostVoter.php
    namespace Acme\DemoBundle\Security\Authorization\Document;

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    class PostVoter implements VoterInterface
    {
        private $container;
    
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }
        
        public function supportsAttribute($attribute) 
        {
           return in_array($attribute, array(
              'view',
              'edit'
           ));
        }
        
        public function supportsClass($class)
        {
            // could be "ACME\DemoBundle\Entity\Post" as well
            $array = array("ACME\DemoBundle\Document\Post");
         
            foreach ($array as $item) {
                // check with stripos in case doctrine is using a proxy class for this object
                if (stripos($s, $item) !== FALSE) {
                    return true;
                }
            }
            return false;
        }
        
        public function vote(TokenInterface $token, $object, array $attributes) 
        {
            // get current logged in user
            $user = $token->getUser();
                    
            // check if class of this object is supported by this voter
            if ( !($this->supportsClass(get_class($object))) ) {
                return VoterInterface::ACCESS_ABSTAIN;
            }
    
            // check if the given attribute is covered by this voter
            foreach ($attributes as $attribute) {
                if ( !$this->supportsAttribute($attribute) ) {
                    return VoterInterface::ACCESS_ABSTAIN;
                }
            }
    
            // check if given user is instance of user interface
            if ( !($user instanceof UserInterface) ) {
                return VoterInterface::ACCESS_DENIED;
            }
            
            switch($this->attributes[0]) {
            
                case 'view':
                    if($object->isPrivate() === false) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                    
                case 'edit':
                    if($object->getOwner()->getId() === $user->getId()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
                    
                default:
                    // otherwise denied access
                    return VoterInterface::ACCESS_DENIED;
            }

        }
    }

That's it! The voter is done. The next step is to inject the voter into
the security layer. This can be done easily through the service container.

Declaring the Voter as a Service
--------------------------------

To inject the voter into the security layer, you must declare it as a service,
and tag it as a "security.voter":

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/AcmeBundle/Resources/config/services.yml
        services:
            security.access.post_document_voter:
                class:      Acme\DemoBundle\Security\Authorization\Document\PostVoter
                public:     false
                arguments: [@service_container]
                # we need to assign this service to be a security voter
                tags:
                   - { name: security.voter }