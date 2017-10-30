.. index::
    single: Security; Impersonating User

How to Impersonate a User
=========================

Sometimes, it's useful to be able to switch from one user to another without
having to log out and log in again (for instance when you are debugging or trying
to understand a bug a user sees that you can't reproduce).

.. caution::

    User impersonation is not compatible with
    :doc:`pre authenticated firewalls </security/pre_authenticated>`. The
    reason is that impersonation requires the authentication state to be maintained
    server-side, but pre-authenticated information (``SSL_CLIENT_S_DN_Email``,
    ``REMOTE_USER`` or other) is sent in each request.

Impersonating the user can be easily done by activating the ``switch_user``
firewall listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: true

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <switch-user />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main'=> array(
                    // ...
                    'switch_user' => true,
                ),
            ),
        ));

To switch to another user, just add a query string with the ``_switch_user``
parameter and the username as the value to the current URL:

.. code-block:: text

    http://example.com/somewhere?_switch_user=thomas

To switch back to the original user, use the special ``_exit`` username:

.. code-block:: text

    http://example.com/somewhere?_switch_user=_exit

During impersonation, the user is provided with a special role called
``ROLE_PREVIOUS_ADMIN``. In a template, for instance, this role can be used
to show a link to exit impersonation:

.. configuration-block::

    .. code-block:: html+twig

        {% if is_granted('ROLE_PREVIOUS_ADMIN') %}
            <a href="{{ path('homepage', {'_switch_user': '_exit'}) }}">Exit impersonation</a>
        {% endif %}

    .. code-block:: html+php

        <?php if ($view['security']->isGranted('ROLE_PREVIOUS_ADMIN')): ?>
            <a href="<?php echo $view['router']->path('homepage', array(
                '_switch_user' => '_exit',
            )) ?>">
                Exit impersonation
            </a>
        <?php endif ?>

In some cases you may need to get the object that represents the impersonator
user rather than the impersonated user. Use the following snippet to iterate
over the user's roles until you find one that a ``SwitchUserRole`` object::

    use Symfony\Component\Security\Core\Role\SwitchUserRole;

    $authChecker = $this->get('security.authorization_checker');
    $tokenStorage = $this->get('security.token_storage');

    if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
        foreach ($tokenStorage->getToken()->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $impersonatorUser = $role->getSource()->getUser();
                break;
            }
        }
    }

Of course, this feature needs to be made available to a small group of users.
By default, access is restricted to users having the ``ROLE_ALLOWED_TO_SWITCH``
role. The name of this role can be modified via the ``role`` setting. For
extra security, you can also change the query parameter name via the ``parameter``
setting:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...

            firewalls:
                main:
                    # ...
                    switch_user: { role: ROLE_ADMIN, parameter: _want_to_be_this_user }

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <!-- ... -->

                <firewall name="main">
                    <!-- ... -->
                    <switch-user role="ROLE_ADMIN" parameter="_want_to_be_this_user" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...

            'firewalls' => array(
                'main'=> array(
                    // ...
                    'switch_user' => array(
                        'role' => 'ROLE_ADMIN',
                        'parameter' => '_want_to_be_this_user',
                    ),
                ),
            ),
        ));

Events
------

The firewall dispatches the ``security.switch_user`` event right after the impersonation
is completed. The :class:`Symfony\\Component\\Security\\Http\\Event\\SwitchUserEvent` is
passed to the listener, and you can use this to get the user that you are now impersonating.

The :doc:`/session/locale_sticky_session` article does not update the locale
when you impersonate a user. If you *do* want to be sure to update the locale when
you switch users, add an event subscriber on this event::

        // src/EventListener/SwitchUserListener.php
        namespace App\EventListener;

        use Symfony\Component\Security\Http\Event\SwitchUserEvent;
        use Symfony\Component\EventDispatcher\EventSubscriberInterface;
        use Symfony\Component\Security\Http\SecurityEvents;

        class SwitchUserSubscriber implements EventSubscriberInterface
        {
            public function onSwitchUser(SwitchUserEvent $event)
            {
                $event->getRequest()->getSession()->set(
                    '_locale',
                    // assuming your User has some getLocale() method
                    $event->getTargetUser()->getLocale()
                );
            }

            public static function getSubscribedEvents()
            {
                return array(
                    // constant for security.switch_user
                    SecurityEvents::SWITCH_USER => 'onSwitchUser',
                );
            }
        }

That's it! If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
Symfony will automatically discover your service and call ``onSwitchUser`` whenever
a switch user occurs.

For more details about event subscribers, see :doc:`/event_dispatcher`.
