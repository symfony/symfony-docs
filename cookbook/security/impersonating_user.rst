.. index::
    single: Security; Impersonating User

How to Impersonate a User
=========================

Sometimes, it's useful to be able to switch from one user to another without
having to log out and log in again (for instance when you are debugging or trying
to understand a bug a user sees that you can't reproduce). This can be easily
done by activating the ``switch_user`` firewall listener:

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
            <!-- The path() method was introduced in Symfony 2.8. Prior to 2.8, you
                had to use generate(). -->
            <a href="<?php echo $view['router']->path('homepage', array(
                '_switch_user' => '_exit',
            ) ?>">
                Exit impersonation
            </a>
        <?php endif ?>

In some cases you may need to get the object that represents the impersonating
user rather than the impersonated user. Use the following snippet to iterate
over the user's roles until you find one that a ``SwitchUserRole`` object::

    use Symfony\Component\Security\Core\Role\SwitchUserRole;

    $authChecker = $this->get('security.authorization_checker');
    $tokenStorage = $this->get('security.token_storage');

    if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
        foreach ($tokenStorage->getToken()->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $impersonatingUser = $role->getSource()->getUser();
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

The cookbook article about
:doc:`Making the Locale "Sticky" during a User's Session </cookbook/session/locale_sticky_session>`
does not update the locale when you impersonate a user. The following code sample will show
how to change the sticky locale:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.switch_user_listener:
                class: AppBundle\EventListener\SwitchUserListener
                tags:
                    - { name: kernel.event_listener, event: security.switch_user, method: onSwitchUser }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <service id="app.switch_user_listener"
                    class="AppBundle\EventListener\SwitchUserListener"
                >
                    <tag name="kernel.event_listener"
                        event="security.switch_user"
                        method="onSwitchUser"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        $container
            ->register('app.switch_user_listener', 'AppBundle\EventListener\SwitchUserListener')
            ->addTag('kernel.event_listener', array('event' => 'security.switch_user', 'method' => 'onSwitchUser'))
        ;

.. caution::

    The listener implementation assumes your ``User`` entity has a ``getLocale()`` method.

.. code-block:: php

        // src/AppBundle/EventListener/SwitchUserListener.pnp
        namespace AppBundle\EventListener;

        use Symfony\Component\Security\Http\Event\SwitchUserEvent;

        class SwitchUserListener
        {
            public function onSwitchUser(SwitchUserEvent $event)
            {
                $event->getRequest()->getSession()->set(
                    '_locale',
                    $event->getTargetUser()->getLocale()
                );
            }
        }
