.. index::
   single: Security; Data Permission
   single: Security: Voters

How to Use Voters to Check User Permissions
===========================================

In Symfony, you can check the permission to access data by using the
:doc:`ACL module </cookbook/security/acl>`, which is a bit overwhelming
for many applications. A much easier solution is to work with custom voters,
which are like simple conditional statements.

.. tip::

    Take a look at the
    :doc:`authorization </components/security/authorization>`
    chapter for an even deeper understanding on voters.

How Symfony Uses Voters
-----------------------

In order to use voters, you have to understand how Symfony works with them.
All voters are called each time you use the ``isGranted()`` method on Symfony's
security context (i.e. the ``security.context`` service). Each one decides
if the current user should have access to some resource.

Ultimately, Symfony takes the responses from all voters and makes the final
decision (to allow or deny access to the resource) according to the strategy defined
in the application, which can be: affirmative, consensus or unanimous.

For more information take a look at
:ref:`the section about access decision managers <components-security-access-decision-manager>`.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which has this structure::

    interface VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, $object, array $attributes);
    }

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsAttribute`
method is used to check if the voter supports the given user attribute (i.e:
a role like ``ROLE_USER``, an ACL ``EDIT``, etc.).

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsClass`
method checks whether the voter supports the class of the object whose
access is being checked.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::vote`
method must implement the business logic that verifies whether or not the
user has access. This method must return one of the following values:

* ``VoterInterface::ACCESS_GRANTED``: The authorization will be granted by this voter;
* ``VoterInterface::ACCESS_ABSTAIN``: The voter cannot decide if authorization should be granted;
* ``VoterInterface::ACCESS_DENIED``: The authorization will be denied by this voter.

In this example, the voter will check if the user has access to a specific
object according to your custom conditions (e.g. they must be the owner of
the object). If the condition fails, you'll return
``VoterInterface::ACCESS_DENIED``, otherwise you'll return
``VoterInterface::ACCESS_GRANTED``. In case the responsibility for this decision
does not belong to this voter, it will return ``VoterInterface::ACCESS_ABSTAIN``.

Creating the custom Voter
-------------------------

The goal is to create a voter that checks if a user has access to view or
edit a particular object. Here's an example implementation::

    // src/AppBundle/Security/Authorization/Voter/PostVoter.php
    namespace AppBundle\Security\Authorization\Voter;

    use AppBundle\Entity\User;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class PostVoter implements VoterInterface
    {
        const VIEW = 'view';
        const EDIT = 'edit';

        public function supportsAttribute($attribute)
        {
            return in_array($attribute, array(self::VIEW, self::EDIT));
        }

        public function supportsClass($class)
        {
            $supportedClass = 'AppBundle\Entity\Post';

            return $supportedClass === $class || is_subclass_of($class, $supportedClass);
        }

        /**
         * @var \AppBundle\Entity\Post $post
         */
        public function vote(TokenInterface $token, $post, array $attributes)
        {
            // check if the class of this object is supported by this voter
            if (!$this->supportsClass(get_class($post))) {
                return VoterInterface::ACCESS_ABSTAIN;
            }

            // check if the voter is used correctly, only allow one attribute
            // this isn't a requirement, it's just one easy way for you to
            // design your voter
            if (1 !== count($attributes)) {
                throw new \InvalidArgumentException(
                    'Only one attribute is allowed for VIEW or EDIT'
                );
            }

            // set the attribute to check against
            $attribute = $attributes[0];

            // check if the given attribute is covered by this voter
            if (!$this->supportsAttribute($attribute)) {
                return VoterInterface::ACCESS_ABSTAIN;
            }

            // get current logged in user
            $user = $token->getUser();

            // make sure there is a user object (i.e. that the user is logged in)
            if (!$user instanceof UserInterface) {
                return VoterInterface::ACCESS_DENIED;
            }

            // double-check that the User object is the expected entity (this
            // only happens when you did not configure the security system properly)
            if (!$user instanceof User) {
                throw new \LogicException('The user is somehow not our User class!');
            }

            switch($attribute) {
                case self::VIEW:
                    // the data object could have for example a method isPrivate()
                    // which checks the boolean attribute $private
                    if (!$post->isPrivate()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;

                case self::EDIT:
                    // we assume that our data object has a method getOwner() to
                    // get the current owner user entity for this data object
                    if ($user->getId() === $post->getOwner()->getId()) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }

That's it! The voter is done. The next step is to inject the voter into
the security layer.

Declaring the Voter as a Service
--------------------------------

To inject the voter into the security layer, you must declare it as a service
and tag it with ``security.voter``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            security.access.post_voter:
                class:      AppBundle\Security\Authorization\Voter\PostVoter
                public:     false
                tags:
                    - { name: security.voter }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="security.access.post_voter"
                    class="AppBundle\Security\Authorization\Voter\PostVoter"
                    public="false"
                >

                    <tag name="security.voter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('AppBundle\Security\Authorization\Voter\PostVoter');
        $definition
            ->setPublic(false)
            ->addTag('security.voter')
        ;

        $container->setDefinition('security.access.post_voter', $definition);

How to Use the Voter in a Controller
------------------------------------

The registered voter will then always be asked as soon as the method ``isGranted()``
from the security context is called.

.. code-block:: php

    // src/AppBundle/Controller/PostController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class PostController extends Controller
    {
        public function showAction($id)
        {
            // get a Post instance
            $post = ...;

            // keep in mind that this will call all registered security voters
            if (false === $this->get('security.context')->isGranted('view', $post)) {
                throw new AccessDeniedException('Unauthorized access!');
            }

            return new Response('<h1>'.$post->getName().'</h1>');
        }
    }

It's that easy!

.. _security-voters-change-strategy:

Changing the Access Decision Strategy
-------------------------------------

Imagine you have multiple voters for one action for an object. For instance,
you have one voter that checks if the user is a member of the site and a second
one checking if the user is older than 18.

To handle these cases, the access decision manager uses an access decision
strategy. You can configure this to suite your needs. There are three
strategies available:

``affirmative`` (default)
    This grants access as soon as there is *one* voter granting access;

``consensus``
    This grants access if there are more voters granting access than denying;

``unanimous``
    This only grants access once *all* voters grant access.

In the above scenario, both voters should grant access in order to grant access
to the user to read the post. In this case, the default strategy is no longer
valid and ``unanimous`` should be used instead. You can set this in the
security configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            access_decision_manager:
                strategy: unanimous

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
        >

            <config>
                <access-decision-manager strategy="unanimous">
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'access_decision_manager' => array(
                'strategy' => 'unanimous',
            ),
        ));
