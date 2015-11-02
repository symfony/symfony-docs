.. index::
   single: Security; Data Permission Voters

How to Use Voters to Check User Permissions
===========================================

In Symfony, you can check the permission to access data by using the
:doc:`ACL module </cookbook/security/acl>`, which is a bit overwhelming
for many applications. A much easier solution is to work with custom voters,
which are like simple conditional statements.

.. seealso::

    Voters can also be used in other ways, like, for example, blacklisting IP
    addresses from the entire application: :doc:`/cookbook/security/voters`.

.. tip::

    Take a look at the
    :doc:`authorization </components/security/authorization>`
    chapter for an even deeper understanding on voters.

How Symfony Uses Voters
-----------------------

In order to use voters, you have to understand how Symfony works with them.
All voters are called each time you use the ``isGranted()`` method on Symfony's
authorization checker (i.e. the ``security.authorization_checker`` service). Each
one decides if the current user should have access to some resource.

Ultimately, Symfony takes the responses from all voters and makes the final
decision (to allow or deny access to the resource) according to the strategy defined
in the application, which can be: affirmative, consensus or unanimous.

For more information take a look at
:ref:`the section about access decision managers <components-security-access-decision-manager>`.

The Voter Interface
-------------------

A custom voter needs to implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`
or extend :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter`,
which makes creating a voter even easier.

.. code-block:: php

    abstract class AbstractVoter implements VoterInterface
    {
        abstract protected function getSupportedClasses();
        abstract protected function getSupportedAttributes();
        abstract protected function isGranted($attribute, $object, $user = null);
    }

In this example, the voter will check if the user has access to a specific
object according to your custom conditions (e.g. they must be the owner of
the object). If the condition fails, you'll return
``VoterInterface::ACCESS_DENIED``, otherwise you'll return
``VoterInterface::ACCESS_GRANTED``. In case the responsibility for this decision
does not belong to this voter, it will return ``VoterInterface::ACCESS_ABSTAIN``.

Creating the custom Voter
-------------------------

The goal is to create a voter that checks if a user has access to view or
edit a particular object. Here's an example implementation:

.. code-block:: php

    // src/AppBundle/Security/Authorization/Voter/PostVoter.php
    namespace AppBundle\Security\Authorization\Voter;

    use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
    use AppBundle\Entity\User;
    use Symfony\Component\Security\Core\User\UserInterface;

    class PostVoter extends AbstractVoter
    {
        const VIEW = 'view';
        const EDIT = 'edit';

        protected function getSupportedAttributes()
        {
            return array(self::VIEW, self::EDIT);
        }

        protected function getSupportedClasses()
        {
            return array('AppBundle\Entity\Post');
        }

        protected function isGranted($attribute, $post, $user = null)
        {
            // make sure there is a user object (i.e. that the user is logged in)
            if (!$user instanceof UserInterface) {
                return false;
            }

            // double-check that the User object is the expected entity (this
            // only happens when you did not configure the security system properly)
            if (!$user instanceof User) {
                throw new \LogicException('The user is somehow not our User class!');
            }

            switch($attribute) {
                case self::VIEW:
                    // the data object could have for example a method isPrivate()
                    // which checks the Boolean attribute $private
                    if (!$post->isPrivate()) {
                        return true;
                    }

                    break;
                case self::EDIT:
                    // this assumes that the data object has a getOwner() method
                    // to get the entity of the user who owns this data object
                    if ($user->getId() === $post->getOwner()->getId()) {
                        return true;
                    }

                    break;
            }

            return false;
        }
    }

That's it! The voter is done. The next step is to inject the voter into
the security layer.

To recap, here's what's expected from the three abstract methods:

:method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedClasses`
    It tells Symfony that your voter should be called whenever an object of one
    of the given classes is passed to ``isGranted()``. For example, if you return
    ``array('AppBundle\Model\Product')``, Symfony will call your voter when a
    ``Product`` object is passed to ``isGranted()``.

:method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedAttributes`
    It tells Symfony that your voter should be called whenever one of these
    strings is passed as the first argument to ``isGranted()``. For example, if
    you return ``array('CREATE', 'READ')``, then Symfony will call your voter
    when one of these is passed to ``isGranted()``.

:method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::isGranted`
    It implements the business logic that verifies whether or not a given user is
    allowed access to a given attribute (e.g. ``CREATE`` or ``READ``) on a given
    object. This method must return a boolean.

.. note::

    Currently, to use the ``AbstractVoter`` base class, you must be creating a
    voter where an object is always passed to ``isGranted()``.

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
from the authorization checker is called.

.. code-block:: php

    // src/AppBundle/Controller/PostController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class PostController extends Controller
    {
        public function showAction($id)
        {
            // get a Post instance
            $post = ...;

            // keep in mind that this will call all registered security voters
            $this->denyAccessUnlessGranted('view', $post, 'Unauthorized access!');

            return new Response('<h1>'.$post->getName().'</h1>');
        }
    }

.. versionadded:: 2.6
    The ``security.authorization_checker`` service was introduced in Symfony 2.6.
    Prior to Symfony 2.6, you had to use the ``isGranted()`` method of the
    ``security.context`` service.

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
