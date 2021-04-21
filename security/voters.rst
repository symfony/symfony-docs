.. index::
   single: Security; Data Permission Voters

.. _security/custom-voter:

How to Use Voters to Check User Permissions
===========================================

Voters are Symfony's most powerful way of managing permissions. They allow you
to centralize all permission logic, then reuse them in many places.

However, if you don't reuse permissions or your rules are basic, you can always
put that logic directly into your controller instead. Here's an example how
this could look like, if you want to make a route accessible to the "owner" only::

    // src/Controller/PostController.php
    // ...

    // inside your controller action
    if ($post->getOwner() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

In that sense, the following example used throughout this page is a minimal
example for voters.

.. tip::

    Take a look at the
    :doc:`authorization </components/security/authorization>`
    article for an even deeper understanding on voters.

Here's how Symfony works with voters:
All voters are called each time you use the ``isGranted()`` method on Symfony's
authorization checker or call ``denyAccessUnlessGranted()`` in a controller (which
uses the authorization checker), or by
:ref:`access controls <security-access-control-enforcement-options>`.

Ultimately, Symfony takes the responses from all voters and makes the final
decision (to allow or deny access to the resource) according to the strategy defined
in the application, which can be: affirmative, consensus, unanimous or priority.

For more information take a look at
:ref:`the section about access decision managers <components-security-access-decision-manager>`.

The Voter Interface
-------------------

A custom voter needs to implement
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`
or extend :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\Voter`,
which makes creating a voter even easier::

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

    abstract class Voter implements VoterInterface
    {
        abstract protected function supports(string $attribute, $subject);
        abstract protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token);
    }

.. _how-to-use-the-voter-in-a-controller:

Setup: Checking for Access in a Controller
------------------------------------------

Suppose you have a ``Post`` object and you need to decide whether or not the current
user can *edit* or *view* the object. In your controller, you'll check access with
code like this::

    // src/Controller/PostController.php

    // ...
    class PostController extends AbstractController
    {
        /**
         * @Route("/posts/{id}", name="post_show")
         */
        public function show($id): Response
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
        public function edit($id): Response
        {
            // get a Post object - e.g. query for it
            $post = ...;

            // check for "edit" access: calls all voters
            $this->denyAccessUnlessGranted('edit', $post);

            // ...
        }
    }

The ``denyAccessUnlessGranted()`` method (and also the ``isGranted()`` method)
calls out to the "voter" system. Right now, no voters will vote on whether or not
the user can "view" or "edit" a ``Post``. But you can create your *own* voter that
decides this using whatever logic you want.

Creating the custom Voter
-------------------------

Suppose the logic to decide if a user can "view" or "edit" a ``Post`` object is
pretty complex. For example, a ``User`` can always edit or view a ``Post`` they created.
And if a ``Post`` is marked as "public", anyone can view it. A voter for this situation
would look like this::

    // src/Security/PostVoter.php
    namespace App\Security;

    use App\Entity\Post;
    use App\Entity\User;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;

    class PostVoter extends Voter
    {
        // these strings are just invented: you can use anything
        const VIEW = 'view';
        const EDIT = 'edit';

        protected function supports(string $attribute, $subject): bool
        {
            // if the attribute isn't one we support, return false
            if (!in_array($attribute, [self::VIEW, self::EDIT])) {
                return false;
            }

            // only vote on `Post` objects
            if (!$subject instanceof Post) {
                return false;
            }

            return true;
        }

        protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
        {
            $user = $token->getUser();

            if (!$user instanceof User) {
                // the user must be logged in; if not, deny access
                return false;
            }

            // you know $subject is a Post object, thanks to `supports()`
            /** @var Post $post */
            $post = $subject;

            switch ($attribute) {
                case self::VIEW:
                    return $this->canView($post, $user);
                case self::EDIT:
                    return $this->canEdit($post, $user);
            }

            throw new \LogicException('This code should not be reached!');
        }

        private function canView(Post $post, User $user): bool
        {
            // if they can edit, they can view
            if ($this->canEdit($post, $user)) {
                return true;
            }

            // the Post object could have, for example, a method `isPrivate()`
            return !$post->isPrivate();
        }

        private function canEdit(Post $post, User $user): bool
        {
            // this assumes that the Post object has a `getOwner()` method
            return $user === $post->getOwner();
        }
    }

That's it! The voter is done! Next, :ref:`configure it <declaring-the-voter-as-a-service>`.

To recap, here's what's expected from the two abstract methods:

``Voter::supports(string $attribute, $subject)``
    When ``isGranted()`` (or ``denyAccessUnlessGranted()``) is called, the first
    argument is passed here as ``$attribute`` (e.g. ``ROLE_USER``, ``edit``) and
    the second argument (if any) is passed as ``$subject`` (e.g. ``null``, a ``Post``
    object). Your job is to determine if your voter should vote on the attribute/subject
    combination. If you return true, ``voteOnAttribute()`` will be called. Otherwise,
    your voter is done: some other voter should process this. In this example, you
    return ``true`` if the attribute is ``view`` or ``edit`` and if the object is
    a ``Post`` instance.

``voteOnAttribute(string $attribute, $subject, TokenInterface $token)``
    If you return ``true`` from ``supports()``, then this method is called. Your
    job is to return ``true`` to allow access and ``false`` to deny access.
    The ``$token`` can be used to find the current user object (if any). In this
    example, all of the complex business logic is included to determine access.

.. _declaring-the-voter-as-a-service:

Configuring the Voter
---------------------

To inject the voter into the security layer, you must declare it as a service
and tag it with ``security.voter``. But if you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
that's done automatically for you! When you
:ref:`call isGranted() with view/edit and pass a Post object <how-to-use-the-voter-in-a-controller>`,
your voter will be called and you can control access.

Checking for Roles inside a Voter
---------------------------------

What if you want to call ``isGranted()`` from *inside* your voter - e.g. you want
to see if the current user has ``ROLE_SUPER_ADMIN``. That's possible by injecting
the :class:`Symfony\\Component\\Security\\Core\\Security`
into your voter. You can use this to, for example, *always* allow access to a user
with ``ROLE_SUPER_ADMIN``::

    // src/Security/PostVoter.php

    // ...
    use Symfony\Component\Security\Core\Security;

    class PostVoter extends Voter
    {
        // ...

        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
        {
            // ...

            // ROLE_SUPER_ADMIN can do anything! The power!
            if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
                return true;
            }

            // ... all the normal voter logic
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically pass the ``security.helper``
service when instantiating your voter (thanks to autowiring).

.. _security-voters-change-strategy:

Changing the Access Decision Strategy
-------------------------------------

Normally, only one voter will vote at any given time (the rest will "abstain", which
means they return ``false`` from ``supports()``). But in theory, you could make multiple
voters vote for one action and object. For instance, suppose you have one voter that
checks if the user is a member of the site and a second one that checks if the user
is older than 18.

To handle these cases, the access decision manager uses a "strategy" which you can configure.
There are three strategies available:

``affirmative`` (default)
    This grants access as soon as there is *one* voter granting access;

``consensus``
    This grants access if there are more voters granting access than
    denying. In case of a tie the decision is based on the
    ``allow_if_equal_granted_denied`` config option (defaulting to ``true``);

``unanimous``
    This only grants access if there is no voter denying access.

``priority``
    This grants or denies access by the first voter that does not abstain,
    based on their service priority;

    .. versionadded:: 5.1

        The ``priority`` version strategy was introduced in Symfony 5.1.

Regardless the chosen strategy, if all voters abstained from voting, the
decision is based on the ``allow_if_all_abstain`` config option (which
defaults to ``false``).

In the above scenario, both voters should grant access in order to grant access
to the user to read the post. In this case, the default strategy is no longer
valid and ``unanimous`` should be used instead. You can set this in the
security configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            access_decision_manager:
                strategy: unanimous
                allow_if_all_abstain: false

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd"
        >

            <config>
                <access-decision-manager strategy="unanimous" allow-if-all-abstain="false"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', [
            'access_decision_manager' => [
                'strategy' => 'unanimous',
                'allow_if_all_abstain' => false,
            ],
        ]);

Custom Access Decision Strategy
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If none of the built-in strategies fits your use case, define the ``service``
option to use a custom service as the Access Decision Manager (your service
must implement the :class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManagerInterface`):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            access_decision_manager:
                service: App\Security\MyCustomAccessDecisionManager
                # ...

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >

            <config>
                <access-decision-manager
                    service="App\Security\MyCustomAccessDecisionManager"/>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\MyCustomAccessDecisionManager;

        $container->loadFromExtension('security', [
            'access_decision_manager' => [
                'service' => MyCustomAccessDecisionManager::class,
                // ...
            ],
        ]);
