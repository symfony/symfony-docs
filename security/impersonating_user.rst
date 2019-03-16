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
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

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
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main'=> [
                    // ...
                    'switch_user' => true,
                ],
            ],
        ]);

To switch to another user, add a query string with the ``_switch_user``
parameter and the username (or whatever field our user provider uses to load users)
as the value to the current URL:

.. code-block:: text

    http://example.com/somewhere?_switch_user=thomas

To switch back to the original user, use the special ``_exit`` username:

.. code-block:: text

    http://example.com/somewhere?_switch_user=_exit

This feature is only available to users with a special role called ``ROLE_ALLOWED_TO_SWITCH``.
Using :ref:`role_hierarchy <security-role-hierarchy>` is a great way to give this
role to the users that need it.

Knowing When Impersonation Is Active
------------------------------------

During impersonation, the user is provided with a special role called
``ROLE_PREVIOUS_ADMIN``. In a template, for instance, this role can be used
to show a link to exit impersonation:

.. code-block:: html+twig

    {% if is_granted('ROLE_PREVIOUS_ADMIN') %}
        <a href="{{ path('homepage', {'_switch_user': '_exit'}) }}">Exit impersonation</a>
    {% endif %}

Finding the Original User
-------------------------

.. versionadded:: 4.3

    The ``SwitchUserToken`` class was introduced in Symfony 4.3.

In some cases, you may need to get the object that represents the impersonator
user rather than the impersonated user. When a user is impersonated the token
stored in the token storage will be a ``SwitchUserToken`` instance. Use the
following snippet to obtain the original token which gives you access to
the impersonator user::

    use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
    use Symfony\Component\Security\Core\Security;
    // ...

    public class SomeService
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
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">
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
        $container->loadFromExtension('security', [
            // ...

            'firewalls' => [
                'main'=> [
                    // ...
                    'switch_user' => [
                        'role' => 'ROLE_ADMIN',
                        'parameter' => '_want_to_be_this_user',
                    ],
                ],
            ],
        ]);

Limiting User Switching
-----------------------

If you need more control over user switching, but don't require the complexity
of a full ACL implementation, you can use a security voter. For example, you
may want to allow employees to be able to impersonate a user with the
``ROLE_CUSTOMER`` role without giving them the ability to impersonate a more
elevated user such as an administrator.

Create the voter class::

    namespace App\Security\Voter;

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;
    use Symfony\Component\Security\Core\User\UserInterface;

    class SwitchToCustomerVoter extends Voter
    {
        protected function supports($attribute, $subject)
        {
            return in_array($attribute, ['ROLE_ALLOWED_TO_SWITCH'])
                && $subject instanceof UserInterface;
        }

        protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
        {
            $user = $token->getUser();
            // if the user is anonymous or if the subject is not a user, do not grant access
            if (!$user instanceof UserInterface || !$subject instanceof UserInterface) {
                return false;
            }

            if (in_array('ROLE_CUSTOMER', $subject->getRoles())
                && in_array('ROLE_SWITCH_TO_CUSTOMER', $token->getRoleNames(), true)) {
                return true;
            }

            return false;
        }
    }

.. versionadded:: 4.3

    The ``getRoleNames()`` method was introduced in Symfony 4.3.

To enable the new voter in the app, register it as a service and
:doc:`tag it </service_container/tags>` with the ``security.voter``
tag. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
this is already done for you, thanks to :ref:`autoconfiguration <services-autoconfigure>`.

Now a user who has the ``ROLE_SWITCH_TO_CUSTOMER`` role can switch to a user who
has the ``ROLE_CUSTOMER`` role, but not other users.

Events
------

The firewall dispatches the ``security.switch_user`` event right after the impersonation
is completed. The :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent` is
passed to the listener, and you can use this to get the user that you are now impersonating.

The :doc:`/session/locale_sticky_session` article does not update the locale
when you impersonate a user. If you *do* want to be sure to update the locale when
you switch users, add an event subscriber on this event::

    // src/EventListener/SwitchUserSubscriber.php
    namespace App\EventListener;

    use Symfony\Component\Security\Http\Event\SwitchUserEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\Security\Http\SecurityEvents;

    class SwitchUserSubscriber implements EventSubscriberInterface
    {
        public function onSwitchUser(SwitchUserEvent $event)
        {
            $request = $event->getRequest();

            if ($request->hasSession() && ($session = $request->getSession)) {
                $session->set(
                    '_locale',
                    // assuming your User has some getLocale() method
                    $event->getTargetUser()->getLocale()
                );
            }
        }

        public static function getSubscribedEvents()
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
