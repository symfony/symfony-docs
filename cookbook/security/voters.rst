.. index::
   single: Security; Data Permission Voters

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
or extend :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\Voter`,
which makes creating a voter even easier.

.. code-block:: php

    abstract class Voter implements VoterInterface
    {
        abstract protected function supports($attribute, $subject);
        abstract protected function voteOnAttribute($attribute, $subject, TokenInterface $token);
    }

.. versionadded:: 2.8
    The ``Voter`` helper class was added in Symfony 2.8. In earlier versions, an
    ``AbstractVoter`` class with similar behavior was available.

.. _how-to-use-the-voter-in-a-controller:

Setup: Checking for Access in a Controller
------------------------------------------

Suppose you have a ``Post`` object and you need to decide whether or not the current
user can *edit* or *view* the object. In your controller, you'll check access with
code like this::

    // src/AppBundle/Controller/PostController.php
    // ...

    class PostController extends Controller
    {
        /**
         * @Route("/posts/{id}", name="post_show")
         */
        public function showAction($id)
        {
            // get a Post object - e.g. query for it
            $post = ...;

            // check for "view" access: calls all voters
            $this->denyAccessUnlessGranted('view', $post);

            // ...
        }

        /**
         * @Route("/posts/{id}/edit", name="post_edit")
         */
        public function editAction($id)
        {
            // get a Post object - e.g. query for it
            $post = ...;

            // check for "edit" access: calls all voters
            $this->denyAccessUnlessGranted('edit', $post);

            // ...
        }
    }

The ``denyAccessUnlessGranted()`` method (and also, the simpler ``isGranted()`` method)
calls out to the "voter" system. Right now, no voters will vote on whether or not
the user can "view" or "edit" a ``Post``. But you can create your *own* voter that
decides this using whatever logic you want.

.. tip::

    The ``denyAccessUnlessGranted()`` function and the ``isGranted()`` functions
    are both just shortcuts to call ``isGranted()`` on the ``security.authorization_checker``
    service.

Creating the custom Voter
-------------------------

Suppose the logic to decide if a user can "view" or "edit" a ``Post`` object is
pretty complex. For example, a ``User`` can always edit or view a ``Post`` they created.
And if a ``Post`` is marked as "public", anyone can view it. A voter for this situation
would look like this::

    // src/AppBundle/Security/PostVoter.php
    namespace AppBundle\Security;

    use AppBundle\Entity\Post;
    use AppBundle\Entity\User;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;

    class PostVoter extends Voter
    {
        // these strings are just invented: you can use anything
        const VIEW = 'view';
        const EDIT = 'edit';

        protected function supports($attribute, $subject)
        {
            // if the attribute isn't one we support, return false
            if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
                return false;
            }

            // only vote on Post objects inside this voter
            if (!$subject instanceof Post) {
                return false;
            }

            return true;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
        {
            $user = $token->getUser();

            if (!$user instanceof User) {
                // the user must be logged in; if not, deny access
                return false;
            }

            // you know $subject is a Post object, thanks to supports
            /** @var Post $post */
            $post = $subject;

            switch($attribute) {
                case self::VIEW:
                    return $this->canView($post, $user);
                case self::EDIT:
                    return $this->canEdit($post, $user);
            }

            throw new \LogicException('This code should not be reached!');
        }

        private function canView(Post $post, User $user)
        {
            // if they can edit, they can view
            if ($this->canEdit($post, $user)) {
                return true;
            }

            // the Post object could have, for example, a method isPrivate()
            // that checks a boolean $private property
            return !$post->isPrivate();
        }

        private function canEdit(Post $post, User $user)
        {
            // this assumes that the data object has a getOwner() method
            // to get the entity of the user who owns this data object
            return $user === $post->getOwner();
        }
    }

That's it! The voter is done! Next, :ref:`configure it <declaring-the-voter-as-a-service>`.

To recap, here's what's expected from the two abstract methods:

``Voter::supports($attribute, $subject)``
    When ``isGranted()`` (or ``denyAccessUnlessGranted()``) is called, the first
    argument is passed here as ``$attribute`` (e.g. ``ROLE_USER``, ``edit``) and
    the second argument (if any) is passed as ``$subject`` (e.g. ``null``, a ``Post``
    object). Your job is to determine if your voter should vote on the attribute/subject
    combination. If you return true, ``voteOnAttribute()`` will be called. Otherwise,
    your voter is done: some other voter should process this. In this example, you
    return ``true`` if the attribue is ``view`` or ``edit`` and if the object is
    a ``Post`` instance.

``voteOnAttribute($attribute, $subject, TokenInterface $token)``
    If you return ``true`` from ``supports()``, then this method is called. Your
    job is simple: return ``true`` to allow access and ``false`` to deny access.
    The ``$token`` can be used to find the current user object (if any). In this
    example, all of the complex business logic is included to determine access.

.. _declaring-the-voter-as-a-service:

Configuring the Voter
---------------------

To inject the voter into the security layer, you must declare it as a service
and tag it with ``security.voter``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.post_voter:
                class: AppBundle\Security\PostVoter
                tags:
                    - { name: security.voter }
                # small performance boost
                public: false

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.post_voter"
                    class="AppBundle\Security\PostVoter"
                    public="false"
                >

                    <tag name="security.voter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->register('app.post_voter', 'AppBundle\Security\PostVoter')
            ->setPublic(false)
            ->addTag('security.voter')
        ;

You're done! Now, when you :ref:`call isGranted() with view/edit and a Post object <how-to-use-the-voter-in-a-controller>`,
your voter will be executed and you can control access.

Checking for Roles inside a Voter
---------------------------------

.. versionadded:: 2.8
    The ability to inject the ``AccessDecisionManager`` is new in 2.8: it caused
    a CircularReferenceException before. In earlier versions, you must inject the
    ``service_container`` itself and fetch out the ``security.authorization_checker``
    to use ``isGranted()``.

What if you want to call ``isGranted()`` from *inside* your voter - e.g. you want
to see if the current user has ``ROLE_SUPER_ADMIN``. That's possible by injecting
the :class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManager`
into your voter. You can use this to, for example, *always* allow access to a user
with ``ROLE_SUPER_ADMIN``::

    // src/AppBundle/Security/PostVoter.php

    // ...
    use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

    class PostVoter extends Voter
    {
        // ...

        private $decisionManager;

        public function __construct(AccessDecisionManagerInterface $decisionManager)
        {
            $this->decisionManager = $decisionManager;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
        {
            // ...

            // ROLE_SUPER_ADMIN can do anything! The power!
            if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
                return true;
            }

            // ... all the normal voter logic
        }
    }

Next, update ``services.yml`` to inject the ``security.access.decision_manager``
service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.post_voter:
                class: AppBundle\Security\PostVoter
                arguments: ['@security.access.decision_manager']
                public: false
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
                <service id="app.post_voter"
                    class="AppBundle\Security\PostVoter"
                    public="false"
                >
                    <argument type="service" id="security.access.decision_manager"/>

                    <tag name="security.voter" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('app.post_voter', 'AppBundle\Security\PostVoter')
            ->addArgument(new Reference('security.access.decision_manager'))
            ->setPublic(false)
            ->addTag('security.voter')
        ;

That's it! Calling ``decide()`` on the ``AccessDecisionManager`` is essentially
the same as calling ``isGranted()`` from a controller or other places 
(it's just a little lower-level, which is necessary for a voter).

.. note::

    The ``security.access.decision_manager`` is private. This means you can't access
    it directly from a controller: you can only inject it into other services. That's
    ok: use ``security.authorization_checker`` instead in all cases except for voters.

.. _security-voters-change-strategy:

Changing the Access Decision Strategy
-------------------------------------

Normally, only one voter will vote at any given time (the rest will "abstain", which
means they return ``false`` from ``supports()``). But in theory, you could make multiple
voters vote for one action and object. For instance, suppose you have one voter that
checks if the user is a member of the site and a second one that checks if the user
is older than 18.

To handle these cases, the access decision manager uses an access decision
strategy. You can configure this to suit your needs. There are three
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
