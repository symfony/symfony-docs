.. index::
   single: Security, Authorization

Authorization
=============

When any of the authentication providers (see :ref:`authentication_providers`)
has verified the still-unauthenticated token, an authenticated token will
be returned. The authentication listener should set this token directly
in the :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface`
using its :method:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface::setToken`
method.

From then on, the user is authenticated, i.e. identified. Now, other parts
of the application can use the token to decide whether or not the user may
request a certain URI, or modify a certain object. This decision will be made
by an instance of :class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManagerInterface`.

An authorization decision will always be based on a few things:

* The current token
    For instance, the token's :method:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface::getRoleNames`
    method may be used to retrieve the roles of the current user (e.g.
    ``ROLE_SUPER_ADMIN``), or a decision may be based on the class of the token.
* A set of attributes
    Each attribute stands for a certain right the user should have, e.g.
    ``ROLE_ADMIN`` to make sure the user is an administrator.
* An object (optional)
    Any object for which access control needs to be checked, like
    an article or a comment object.

.. _components-security-access-decision-manager:

Access Decision Manager
-----------------------

Since deciding whether or not a user is authorized to perform a certain
action can be a complicated process, the standard :class:`Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManager`
itself depends on multiple voters, and makes a final verdict based on all
the votes (either positive, negative or neutral) it has received. It
recognizes several strategies:

``affirmative`` (default)
    grant access as soon as there is one voter granting access;

``consensus``
    grant access if there are more voters granting access than there are denying;

``unanimous``
    only grant access if none of the voters has denied access;

Usage of the available options in detail::

    use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

    // instances of Symfony\Component\Security\Core\Authorization\Voter\VoterInterface
    $voters = [...];

    // one of "affirmative", "consensus", "unanimous"
    $strategy = ...;

    // whether or not to grant access when all voters abstain
    $allowIfAllAbstainDecisions = ...;

    // whether or not to grant access when there is no majority (applies only to the "consensus" strategy)
    $allowIfEqualGrantedDeniedDecisions = ...;

    $accessDecisionManager = new AccessDecisionManager(
        $voters,
        $strategy,
        $allowIfAllAbstainDecisions,
        $allowIfEqualGrantedDeniedDecisions
    );

.. seealso::

    You can change the default strategy in the
    :ref:`configuration <security-voters-change-strategy>`.

Voters
------

Voters are instances
of :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which means they have to implement a few methods which allows the decision
manager to use them:

``vote(TokenInterface $token, $object, array $attributes)``
    this method will do the actual voting and return a value equal to one
    of the class constants of :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
    i.e. ``VoterInterface::ACCESS_GRANTED``, ``VoterInterface::ACCESS_DENIED``
    or ``VoterInterface::ACCESS_ABSTAIN``;

The Security component contains some standard voters which cover many use
cases:

AuthenticatedVoter
~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter`
voter supports the attributes ``IS_AUTHENTICATED_FULLY``, ``IS_AUTHENTICATED_REMEMBERED``,
and ``IS_AUTHENTICATED_ANONYMOUSLY`` and grants access based on the current
level of authentication, i.e. is the user fully authenticated, or only based
on a "remember-me" cookie, or even authenticated anonymously?::

    use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
    use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
    use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;

    $trustResolver = new AuthenticationTrustResolver(AnonymousToken::class, RememberMeToken::class);

    $authenticatedVoter = new AuthenticatedVoter($trustResolver);

    // instance of Symfony\Component\Security\Core\Authentication\Token\TokenInterface
    $token = ...;

    // any object
    $object = ...;

    $vote = $authenticatedVoter->vote($token, $object, ['IS_AUTHENTICATED_FULLY']);

RoleVoter
~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleVoter`
supports attributes starting with ``ROLE_`` and grants access to the user
when the required ``ROLE_*`` attributes can all be found in the array of
roles returned by the token's :method:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface::getRoleNames`
method::

    use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

    $roleVoter = new RoleVoter('ROLE_');

    $roleVoter->vote($token, $object, ['ROLE_ADMIN']);

RoleHierarchyVoter
~~~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleHierarchyVoter`
extends :class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleVoter`
and provides some additional functionality: it knows how to handle a
hierarchy of roles. For instance, a ``ROLE_SUPER_ADMIN`` role may have subroles
``ROLE_ADMIN`` and ``ROLE_USER``, so that when a certain object requires the
user to have the ``ROLE_ADMIN`` role, it grants access to users who in fact
have the ``ROLE_ADMIN`` role, but also to users having the ``ROLE_SUPER_ADMIN``
role::

    use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
    use Symfony\Component\Security\Core\Role\RoleHierarchy;

    $hierarchy = [
        'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN', 'ROLE_USER'],
    ];

    $roleHierarchy = new RoleHierarchy($hierarchy);

    $roleHierarchyVoter = new RoleHierarchyVoter($roleHierarchy);

.. note::

    When you make your own voter, you can use its constructor
    to inject any dependencies it needs to come to a decision.

Roles
-----

Roles are strings that give expression to a certain right the user has (e.g.
*"edit a blog post"*, *"create an invoice"*). You can freely choose those
strings. The only requirement is that they must start with the ``ROLE_`` prefix
(e.g. ``ROLE_POST_EDIT``, ``ROLE_INVOICE_CREATE``).

Using the Decision Manager
--------------------------

The Access Listener
~~~~~~~~~~~~~~~~~~~

The access decision manager can be used at any point in a request to decide whether
or not the current user is entitled to access a given resource. One optional,
but useful, method for restricting access based on a URL pattern is the
:class:`Symfony\\Component\\Security\\Http\\Firewall\\AccessListener`,
which is one of the firewall listeners (see :ref:`firewall_listeners`) that
is triggered for each request matching the firewall map (see :ref:`firewall`).

It uses an access map (which should be an instance of :class:`Symfony\\Component\\Security\\Http\\AccessMapInterface`)
which contains request matchers and a corresponding set of attributes that
are required for the current user to get access to the application::

    use Symfony\Component\Security\Http\AccessMap;
    use Symfony\Component\HttpFoundation\RequestMatcher;
    use Symfony\Component\Security\Http\Firewall\AccessListener;

    $accessMap = new AccessMap();
    $requestMatcher = new RequestMatcher('^/admin');
    $accessMap->add($requestMatcher, ['ROLE_ADMIN']);

    $accessListener = new AccessListener(
        $securityContext,
        $accessDecisionManager,
        $accessMap,
        $authenticationManager
    );

Authorization Checker
~~~~~~~~~~~~~~~~~~~~~

The access decision manager is also available to other parts of the application
via the :method:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationChecker::isGranted`
method of the :class:`Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationChecker`.
A call to this method will directly delegate the question to the access
decision manager::

    use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    $authorizationChecker = new AuthorizationChecker(
        $tokenStorage,
        $authenticationManager,
        $accessDecisionManager
    );

    if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
        throw new AccessDeniedException();
    }

