.. index::
    single: Security; Impersonating User

How to Impersonate a User
=========================

Sometimes, it's useful to be able to switch from one user to another without
having to log out and log in again (for instance when you are debugging something
a user sees that you can't reproduce).

.. caution::

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
                    switch_user: true

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <switch-user/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->firewall('main')
                // ...
                ->switchUser()
            ;
        };

To switch to another user, add a query string with the ``_switch_user``
parameter and the username (or whatever field our user provider uses to load users)
as the value to the current URL:

.. code-block:: text

    http://example.com/somewhere?_switch_user=thomas

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

        .. code-block:: xml

            <!-- config/packages/security.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <srv:container xmlns="http://symfony.com/schema/dic/security"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:srv="http://symfony.com/schema/dic/services"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/security
                    https://symfony.com/schema/dic/security/security-1.0.xsd">
                <config>
                    <!-- ... -->
                    <firewall name="main">
                        <!-- ... -->
                        <switch-user parameter="X-Switch-User"/>
                    </firewall>
                </config>
            </srv:container>

        .. code-block:: php

            // config/packages/security.php
            use Symfony\Config\SecurityConfig;
            return static function (SecurityConfig $security) {
                // ...
                $security->firewall('main')
                    // ...
                    ->switchUser()
                        ->parameter('X-Switch-User')
                ;
            };

To switch back to the original user, use the special ``_exit`` username:

.. code-block:: text

    http://example.com/somewhere?_switch_user=_exit

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
        <a href="{{ impersonation_exit_path(path('homepage') ) }}">Exit impersonation</a>
    {% endif %}

.. versionadded:: 5.1

    The ``IS_IMPERSONATOR`` was introduced in Symfony 5.1. Use
    ``ROLE_PREVIOUS_ADMIN`` prior to Symfony 5.1.

Finding the Original User
-------------------------

In some cases, you may need to get the object that represents the impersonator
user rather than the impersonated user. When a user is impersonated the token
stored in the token storage will be a ``SwitchUserToken`` instance. Use the
following snippet to obtain the original token which gives you access to
the impersonator user::

    // src/Service/SomeService.php
    namespace App\Service;

    use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
    use Symfony\Component\Security\Core\Security;
    // ...

    class SomeService
    {
        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
        }

        public function someMethod()
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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">
            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <switch-user role="ROLE_ADMIN" parameter="_want_to_be_this_user"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->firewall('main')
                // ...
                ->switchUser()
                    ->role('ROLE_ADMIN')
                    ->parameter('_want_to_be_this_user')
            ;
        };

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

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/security
                https://symfony.com/schema/dic/security/security-1.0.xsd">
            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <switch-user role="CAN_SWITCH_USER"/>
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security) {
            // ...
            $security->firewall('main')
                // ...
                ->switchUser()
                    ->role('CAN_SWITCH_USER')
            ;
        };

Then, create a voter class that responds to this role and includes whatever custom
logic you want::

    // src/Security/Voter/SwitchToCustomerVoter.php
    namespace App\Security\Voter;

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;
    use Symfony\Component\Security\Core\Security;
    use Symfony\Component\Security\Core\User\UserInterface;

    class SwitchToCustomerVoter extends Voter
    {
        private $security;

        public function __construct(Security $security)
        {
            $this->security = $security;
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
            if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
                return true;
            }

            // check for any roles you want
            if ($this->security->isGranted('ROLE_TECH_SUPPORT')) {
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

Events
------

The firewall dispatches the ``security.switch_user`` event right after the impersonation
is completed. The :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent` is
passed to the listener, and you can use this to get the user that you are now impersonating.

The :ref:`locale-sticky-session` section does not update the locale when you
impersonate a user. If you *do* want to be sure to update the locale when you
switch users, add an event subscriber on this event::

    // src/EventListener/SwitchUserSubscriber.php
    namespace App\EventListener;

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
