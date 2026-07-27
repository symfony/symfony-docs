How to Impersonate a User
=========================

Sometimes, it's useful to be able to switch from one user to another without
having to log out and log in again (for instance when you are debugging something
a user sees that you can't reproduce).

.. warning::

    User impersonation is not compatible with some authentication mechanisms
    (e.g. ``REMOTE_USER``) where the authentication information is expected to be
    sent on each request.

Impersonating the user can be done by activating the ``switch_user`` firewall
listener:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: []

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'switch_user' => [],
                    ],
                ],
            ],
        ]);

To switch to another user, add a query string with the ``_switch_user``
parameter and the username (or whatever field your user provider uses to load users)
as the value to the current URL:

.. code-block:: text

    http://example.com/somewhere?_switch_user=thomas

.. tip::

    You can use the Twig function ``impersonation_path('thomas')``

.. tip::

    Instead of adding a ``_switch_user`` query string parameter, you can pass
    the username in a custom HTTP header by adjusting the ``parameter`` setting.
    For example, to use ``X-Switch-User`` header (available in PHP as
    ``HTTP_X_SWITCH_USER``) add this configuration:

    .. configuration-block::

        .. code-block:: yaml

            # config/packages/security.yaml
            security:
                # ...
                firewalls:
                    main:
                        # ...
                        switch_user: { parameter: X-Switch-User }

        .. code-block:: php

            // config/packages/security.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return App::config([
                'security' => [
                    // ...
                    'firewalls' => [
                        'main' => [
                            'switch_user' => [
                                'parameter' => 'X-Switch-User',
                            ],
                        ],
                    ],
                ],
            ]);

To switch back to the original user, use the special ``_exit`` username:

.. code-block:: text

    http://example.com/somewhere?_switch_user=_exit

.. tip::

    You can leverage the Twig function ``impersonation_exit_path('/somewhere')``

This feature is only available to users with a special role called ``ROLE_ALLOWED_TO_SWITCH``.
Using :ref:`role_hierarchy <security-role-hierarchy>` is a great way to give this
role to the users that need it.

Knowing When Impersonation Is Active
------------------------------------

You can use the special attribute ``IS_IMPERSONATOR`` to check if the
impersonation is active in this session. Use this special role, for
instance, to show a link to exit impersonation in a template:

.. code-block:: html+twig

    {% if is_granted('IS_IMPERSONATOR') %}
        <a href="{{ impersonation_exit_path(path('homepage')) }}">Exit impersonation</a>
    {% endif %}

Finding the Original User
-------------------------

In some cases, you may need to get the object that represents the impersonator
user rather than the impersonated user. When a user is impersonated the token
stored in the token storage will be a ``SwitchUserToken`` instance. Use the
following snippet to obtain the original token which gives you access to
the impersonator user::

    // src/Service/SomeService.php
    namespace App\Service;

    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
    // ...

    class SomeService
    {
        public function __construct(
            private Security $security,
        ) {
        }

        public function someMethod(): void
        {
            // ...

            $token = $this->security->getToken();

            if ($token instanceof SwitchUserToken) {
                $impersonatorUser = $token->getOriginalToken()->getUser();
            }

            // ...
        }
    }

Controlling the Query Parameter
-------------------------------

This feature needs to be available only to a restricted group of users.
By default, access is restricted to users having the ``ROLE_ALLOWED_TO_SWITCH``
role. The name of this role can be modified via the ``role`` setting. You can
also adjust the query parameter name via the ``parameter`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: { role: ROLE_ADMIN, parameter: _want_to_be_this_user }

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
            // ...
                'firewalls' => [
                    'main' => [
                        'switch_user' => ['role' => 'ROLE_ADMIN', 'parameter' => '_want_to_be_this_user'],
                    ],
                ],
            ],
        ]);

Redirecting to a Specific Target Route
--------------------------------------

.. note::

    It works only in a stateful firewall.

This feature allows you to control the redirection target route via ``target_route``.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: { target_route: app_user_dashboard }

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'switch_user' => ['target_route' => 'app_user_dashboard'],
                    ],
                ],
            ],
        ]);

Limiting User Switching
-----------------------

If you need more control over user switching, you can use a security voter. First,
configure ``switch_user`` to check for some new, custom attribute. This can be
anything, but *cannot* start with ``ROLE_`` (to enforce that only your voter will
be called):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: { role: CAN_SWITCH_USER }

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'security' => [
                // ...
                'firewalls' => [
                    'main' => [
                        'switch_user' => ['role' => 'CAN_SWITCH_USER'],
                    ],
                ],
            ],
        ]);

Then, create a voter class that responds to this role and includes whatever custom
logic you want::

    // src/Security/Voter/SwitchToCustomerVoter.php
    namespace App\Security\Voter;

    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;
    use Symfony\Component\Security\Core\User\UserInterface;

    class SwitchToCustomerVoter extends Voter
    {
        public function __construct(
            private AccessDecisionManagerInterface $accessDecisionManager,
        ) {
        }

        protected function supports($attribute, $subject): bool
        {
            return in_array($attribute, ['CAN_SWITCH_USER'])
                && $subject instanceof UserInterface;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
        {
            $user = $token->getUser();
            // if the user is anonymous or if the subject is not a user, do not grant access
            if (!$user instanceof UserInterface || !$subject instanceof UserInterface) {
                return false;
            }

            // you can still check for ROLE_ALLOWED_TO_SWITCH
            if ($this->accessDecisionManager->decide($token, ['ROLE_ALLOWED_TO_SWITCH'])) {
                return true;
            }

            // check for any roles you want
            if ($this->accessDecisionManager->decide($token, ['ROLE_TECH_SUPPORT'])) {
                return true;
            }

            /*
             * or use some custom data from your User object
            if ($user->isAllowedToSwitch()) {
                return true;
            }
            */

            return false;
        }
    }

That's it! When switching users, your voter now has full control over whether or
not this is allowed. If your voter isn't called, see :ref:`declaring-the-voter-as-a-service`.

Impersonating Users Across Multiple Firewalls
---------------------------------------------

When your application uses multiple firewalls that share the same security
context (via the ``context`` option), you need to pay special attention to
the ``switch_user`` provider configuration.

By default, ``switch_user`` uses the user provider configured for its firewall.
This becomes a problem when the impersonator and the impersonated user come from
different user providers: exiting impersonation fails because the listener tries
to load the original user using the wrong provider.

To solve this, configure ``switch_user`` with a
:ref:`chain user provider <security-chain-user-provider>` that includes both the
impersonator's provider and the impersonated user's provider:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            providers:
                admin_provider:
                    entity:
                        class: App\Entity\Admin
                        property: username
                user_provider:
                    entity:
                        class: App\Entity\User
                        property: email
                all_users:
                    chain:
                        providers: ['admin_provider', 'user_provider']

            firewalls:
                admin:
                    pattern: ^/admin
                    context: my_context
                    provider: admin_provider
                    switch_user:
                        provider: all_users
                    # ...
                main:
                    pattern: ^/
                    context: my_context
                    provider: user_provider
                    # ...

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Entity\Admin;
        use App\Entity\User;

        return App::config([
            'security' => [
                // ...
                'providers' => [
                    'admin_provider' => [
                        'entity' => [
                            'class' => Admin::class,
                            'property' => 'username',
                        ],
                    ],
                    'user_provider' => [
                        'entity' => [
                            'class' => User::class,
                            'property' => 'email',
                        ],
                    ],
                    'all_users' => [
                        'chain' => [
                            'providers' => ['admin_provider', 'user_provider'],
                        ],
                    ],
                ],

                'firewalls' => [
                    'admin' => [
                        'pattern' => '^/admin',
                        'provider' => 'admin_provider',
                        'context' => 'my_context',
                        'switch_user' => [
                            'provider' => 'all_users',
                        ],
                    ],
                    'main' => [
                        'pattern' => '^/',
                        'provider' => 'user_provider',
                        'context' => 'my_context',
                    ],
                ],
            ],
        ]);

The chain provider ``all_users`` allows the ``switch_user`` listener to load
both admin users (when exiting impersonation) and regular users (when starting
impersonation).

Events
------

The ``security.switch_user`` event is dispatched just before the impersonation
is fully completed. Your :doc:`listener or subscriber </event_dispatcher>` will
receive a :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent`,
which you can use to get the user that you are now impersonating.

This event is also dispatched just before impersonation is fully exited. You can
use it to get the original impersonator user.

The :ref:`locale-sticky-session` section does not update the locale when you
impersonate a user. If you *do* want to be sure to update the locale when you
switch users, add an event subscriber on this event::

    // src/EventSubscriber/SwitchUserSubscriber.php
    namespace App\EventSubscriber;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Security\Http\Event\SwitchUserEvent;
    use Symfony\Component\Security\Http\SecurityEvents;

    class SwitchUserSubscriber implements EventSubscriberInterface
    {
        public function onSwitchUser(SwitchUserEvent $event): void
        {
            $request = $event->getRequest();

            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->set(
                    '_locale',
                    // assuming your User has some getLocale() method
                    $event->getTargetUser()->getLocale()
                );
            }
        }

        public static function getSubscribedEvents(): array
        {
            return [
                // constant for security.switch_user
                SecurityEvents::SWITCH_USER => 'onSwitchUser',
            ];
        }
    }

That's it! If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
Symfony will automatically discover your service and call ``onSwitchUser`` whenever
a switch user occurs.

For more details about event subscribers, see :doc:`/event_dispatcher`.
