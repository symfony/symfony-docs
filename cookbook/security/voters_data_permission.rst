.. index::
   single: Security; Data Permission Voters

How to implement your own Voter to check User Permissions for accessing a Given Object
======================================================================================

In Symfony2 you can check the permission to access data by the
:doc:`ACL module </cookbook/security/acl>`, which is a bit overwhelming
for many applications. A much easier solution is to work with custom voters,
which are like simple conditional statements. Voters can be
also be used to check for permission as a part or even the whole
application: :doc:`"/cookbook/security/voters"`.

.. tip::

    Have a look at the referenced page if you are not familiar with
    :doc:`authorization </components/security/authorization>`.

How Symfony uses Voters
-----------------------

In order to use voters, you have to understand how Symfony works with them.
In general, all registered custom voters will be called every time you ask
Symfony about permissions (ACL). You can use one of three different
approaches on how to handle the feedback from all voters: affirmative,
consensus and unanimous. For more information have a look at
:ref:`"components-security-access-decision-manager"`.

The Voter Interface
-------------------

A custom voter must implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which has this structure:

// how to put this following snippet (to line 56) in a single file an embed it? as it is used in voters.rst as well.

.. code-block:: php

    interface VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, $post, array $attributes);
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

In this example, you'll check if the user will have access to a specific
object according to your custom conditions (e.g. he must be the owner of
the object). If the condition fails, you'll return
``VoterInterface::ACCESS_DENIED``, otherwise you'll return
``VoterInterface::ACCESS_GRANTED``. In case the responsibility for this decision
does not belong to this voter, it will return ``VoterInterface::ACCESS_ABSTAIN``.

Creating the Custom Voter
-------------------------

You could store your Voter to check permission for the view and edit action like following.

.. code-block:: php

    // src/Acme/DemoBundle/Security/Authorization/Entity/PostVoter.php
    namespace Acme\DemoBundle\Security\Authorization\Entity;

    use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Doctrine\Common\Util\ClassUtils;

    class PostVoter implements VoterInterface
    {
        public function supportsAttribute($attribute)
        {
            return in_array($attribute, array(
                'view',
                'edit',
            ));
        }

        public function supportsClass($obj)
        {
            $array = array('Acme\DemoBundle\Entity\Post');

            foreach ($array as $item) {
                if ($obj instanceof $item))

                    return true;
                }
            }

            return false;
        }

        /** @var \Acme\DemoBundle\Entity\Post $post */
        public function vote(TokenInterface $token, $post, array $attributes)
        {
            // check if voter is used correct, only allow one attribute for a check
            if(count($attributes) !== 1 || !is_string($attributes[0])) {
                throw new PreconditionFailedHttpException('The Attribute was not set correct. Maximum 1 attribute.');
            }

            // set the attribute to check against
            $attribute = $attributes[0];

            // get current logged in user
            $user = $token->getUser();

            // check if class of this object is supported by this voter
            if (!($this->supportsClass($post))) {

                return VoterInterface::ACCESS_ABSTAIN;
            }

            // check if the given attribute is covered by this voter
            if (!$this->supportsAttribute($attribute)) {

                return VoterInterface::ACCESS_ABSTAIN;
            }

            // check if given user is instance of user interface
            if (!($user instanceof UserInterface)) {

                return VoterInterface::ACCESS_DENIED;
            }

            switch($attribute) {
                case 'view':
                    // the data object could have for e.g. a method isPrivate() which checks the the boolean attribute $private
                    if (!$post->isPrivate()) {

                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;

                case 'edit':
                    // we assume that our data object has a method getOwner() to get the current owner user entity for this data object
                    if ($user->getId() === $post->getOwner()->getId()) {

                        return VoterInterface::ACCESS_GRANTED;
                    }
                    break;

                default:
                    // otherwise throw an exception, which will break the request
                    throw new PreconditionFailedHttpException('The Attribute "'.$attribute.'" was not found.')
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
            security.access.post_voter:
                class:      Acme\DemoBundle\Security\Authorization\Entity\PostVoter
                public:     false
                tags:
                   - { name: security.voter }

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <services>
                <service id="security.access.post_document_voter"
                    class="Acme\DemoBundle\Security\Authorization\Document\PostVoter"
                    public="false">
                    <tag name="security.voter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        $container
            ->register('security.access.post_document_voter', 'Acme\DemoBundle\Security\Authorization\Document\PostVoter')
            ->addTag('security.voter')
        ;

How to use the Voter in a Controller
------------------------------------

.. code-block:: php

    // src/Acme/DemoBundle/Controller/PostController.php
    namespace Acme\DemoBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class PostController
    {
        public function showAction($id)
        {
            // keep in mind, this will call all registered security voters
            if (false === $this->get('security.context')->isGranted('view')) {
                throw new AccessDeniedException('Unauthorised access!');
            }

            $product = $this->getDoctrine()
                ->getRepository('AcmeStoreBundle:Post')
                ->find($id);

            return new Response('<html><body>Headline for Post: '.$post->getName().'</body></html>');
        }
    }
