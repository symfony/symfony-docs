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

        // config/packages/security.php
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

    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
    use Symfony\Component\Security\Core\Role\SwitchUserRole;

    private $authChecker;
    private $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function someMethod()
    {
        // ...

        if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatorUser = $role->getSource()->getUser();
                    break;
                }
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

        // config/packages/security.php
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

If you need more control over user switching, but don't require the complexity 
of a full ACL implementation, you can use a security voter.  For example, you 
may want to allow employees to be able to impersonate a user with the 
``ROLE_CUSTOMER`` role without giving them the ability to impersonate a more 
elevated user such as an administrator.

First, create the voter class::

    namespace AppBundle\Security\Voter;

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\Voter;
    use Symfony\Component\Security\Core\Role\RoleHierarchy;
    use Symfony\Component\Security\Core\User\UserInterface;

    class SwitchToCustomerVoter extends Voter
    {
        private $roleHierarchy;

        public function __construct(RoleHierarchy $roleHierarchy)
        {
            $this->roleHierarchy = $roleHierarchy;
        }

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
                && $this->hasSwitchToCustomerRole($token)) {
                return self::ACCESS_GRANTED;
            }

            return false;
        }

        private function hasSwitchToCustomerRole(TokenInterface $token)
        {
            $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
            foreach ($roles as $role) {
                if ($role->getRole() === 'ROLE_SWITCH_TO_CUSTOMER') {
                    return true;
                }
            }
            
            return false;
        }
    }

.. caution::

    Notice that when checking for the ``ROLE_CUSTOMER`` role on the target user, only the roles 
    explicitly assigned to the user are checked rather than checking all reachable roles from
    the role hierarchy.  The reason for this is to avoid accidentally granting access to an 
    elevated user that may have inherited the role via the hierarchy.  This logic is specific 
    to the example, but keep this in mind when writing your own voter.

Next, add the roles to the security configuration:

.. configuration-block::

    .. code-block:: yaml
        
        # config/packages/security.yaml
        security:
            # ...

            role_hierarchy:   
                ROLE_CUSTOMER:    [ROLE_USER]
                ROLE_EMPLOYEE:    [ROLE_USER, ROLE_SWITCH_TO_CUSTOMER]
                ROLE_SUPER_ADMIN: [ROLE_EMPLOYEE, ROLE_ALLOWED_TO_SWITCH]

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <config>
                <!-- ... -->

                <role id="ROLE_CUSTOMER">ROLE_USER</role>
                <role id="ROLE_EMPLOYEE">ROLE_USER, ROLE_SWITCH_TO_CUSTOMER</role>
                <role id="ROLE_SUPER_ADMIN">ROLE_EMPLOYEE, ROLE_ALLOWED_TO_SWITCH</role>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        $container->loadFromExtension('security', array(
            // ...

            'role_hierarchy' => array(
                'ROLE_CUSTOMER'    => 'ROLE_USER',
                'ROLE_EMPLOYEE'    => 'ROLE_USER, ROLE_SWITCH_TO_CUSTOMER',
                'ROLE_SUPER_ADMIN' => array(
                    'ROLE_EMPLOYEE',
                    'ROLE_ALLOWED_TO_SWITCH',
                ),
            ),
        ));

Thanks to autowiring, we only need to configure the role hierarchy argument when registering 
the voter as a service:

.. configuration-block::

    .. code-block:: yaml

        // config/services.yaml
        services:
        # ...

            App\Security\Voter\SwitchToCustomerVoter:
                arguments:
                    $roleHierarchy: "@security.role_hierarchy"

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">               

            <services>
                <!-- ... -->
                <service id="App\Security\Voter\SwitchToCustomerVoter">
                    <argument key="$roleHierarchy">"@security.role_hierarchy"</argument>
                </service>
            </services>
        </container>     

    .. code-block:: php

        // config/services.php
        use App\Security\Voter\SwitchToCustomerVoter;
        use Symfony\Component\DependencyInjection\Definition;

        // Same as before
        $definition = new Definition();

        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;

        $this->registerClasses($definition, 'App\\', '../src/*', '../src/{Entity,Migrations,Tests}');

        // Explicitly configure the service
        $container->getDefinition(SwitchToCustomerVoter::class)
            ->setArgument('$roleHierarchy', '@security.role_hierarchy');   

Now a user who has the ``ROLE_SWITCH_TO_CUSTOMER`` role can switch to a user who explicitly has the 
``ROLE_CUSTOMER`` role, but not other users.

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
