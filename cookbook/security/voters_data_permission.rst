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

Ultimately, Symfony uses one of three different approaches on what to do
with the feedback from all voters: affirmative, consensus and unanimous.

For more information take a look at
:ref:`the section about access decision managers <components-security-access-decision-manager>`.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`
and an :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter`
class is provided with following structure:

.. include:: /cookbook/security/abstract_voter.rst.inc

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

            // the data object could have for example a method isPrivate()
            // which checks the Boolean attribute $private
            if ($attribute == self::VIEW && !$post->isPrivate()) {
                return true;
            }

            // we assume that our data object has a method getOwner() to
            // get the current owner user entity for this data object
            if ($attribute == self::EDIT && $user->getId() === $post->getOwner()->getId()) {
                return true;
            }

            return false;
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

        # src/AppBundle/Resources/config/services.yml
        services:
            security.access.post_voter:
                class:      AppBundle\Security\Authorization\Voter\PostVoter
                public:     false
                tags:
                   - { name: security.voter }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <services>
                <service id="security.access.post_document_voter"
                    class="AppBundle\Security\Authorization\Voter\PostVoter"
                    public="false">
                    <tag name="security.voter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        $container
            ->register(
                    'security.access.post_document_voter',
                    'AppBundle\Security\Authorization\Voter\PostVoter'
            )
            ->addTag('security.voter')
        ;

How to Use the Voter in a Controller
------------------------------------

The registered voter will then always be asked as soon as the method ``isGranted()``
from the authorization checker is called.

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

            // keep in mind, this will call all registered security voters
            if (false === $this->get('security.authorization_checker')->isGranted('view', $post)) {
                throw new AccessDeniedException('Unauthorised access!');
            }

            return new Response('<h1>'.$post->getName().'</h1>');
        }
    }

.. versionadded:: 2.6
    The ``security.authorization_checker`` service was introduced in Symfony 2.6. Prior
    to Symfony 2.6, you had to use the ``isGranted()`` method of the ``security.context`` service.

It's that easy!
