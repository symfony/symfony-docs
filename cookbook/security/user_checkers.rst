.. index::
    single: Security; Creating and Enabling Custom User Checkers

How to Create and Enable Custom User Checkers
=============================================

During the authentication of a user, additional checks might be required to verify
if the identified user is allowed to log in. By defining custom user checkers, you
can define per firewall which checkers should be used.

.. versionadded:: 2.8
Adding custom user checkers was introduced in Symfony 2.8.


Creating a Custom User Checker
------------------------------

User checkers are defined in PHP classes that must implement the
:class:`UserCheckerInterface Symfony\\Component\\Security\\Core\\UserCheckerInterface`.
This interface defines two methods called ``checkPreAuth()`` and ``checkPostAuth()``
to perform checks before and after user authentication. If one or more
conditions are not met, an exception should be thrown which extends the
:class:`AccountStatusException Symfony\\Component\\Security\\Core\\Exception\\AccountStatusException`

.. code-block:: php

    namespace App\Security;

    use Symfony\Component\Security\Core\User\UserCheckInterface;

    class UserChecker implements UserCheckerInterface
    {
        public function checkPreAuth(UserInterface $user)
        {
            if (!$user instanceof AppUser) {
                return;
            }

            // user is deleted, show a generic Account Not Found message.
            if ($user->isDeleted()) {
                throw new AccountDeletedException('...');
            }
        }

        public function checkPostAuth(UserInterface $user)
        {
            if (!$user instanceof AppUser) {
                return;
            }

            // user account is expired, the user may be notified
            if ($user->isExpired()) {
                throw new AccountExpiredException('...');
            }
        }
    }

Enabling the Custom User Checker
--------------------------------

All that's left to be done is creating a service definition and configuring
this in the firewall configuration. Configuring the service is done like any
other service:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.user_checker:
                class: App\Security\UserChecker

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.user_checker" class="App\Security\UserChecker">
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $userChecker = new Definition('App\Security\UserChecker');
        $container->setDefinition('app.user_checker', $userChecker);

All that's left to do is add the checker to the desired firewall where the value
is the service id of your user checker:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        security:
            firewalls:
                secured_area:
                    pattern: ^/
                    user_checkers: ["app.user_checker"]
                    # ...

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
                <firewall name="secured_area" pattern="^/">
                    <user-checkers>app.user_checker</user-checkers>
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'secured_area' => array(
                    'pattern' => '^/',
                    'user_checkers' => array('app.user_checker'),
                    // ...
                ),
            ),
        ));


Additional Configurations
-------------------------

It's possible to add multiple user checkers to one firewall while
configuring only one user checker for another firewall. When adding
multiple user checkers, they are executed in the same sequence as
defined in your configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml

        # ...
        security:
            firewalls:
                admin:
                    pattern: ^/admin
                    user_checkers: ["app.user_checker", "app.admin_checker"]
                    # ...
                secured_area:
                    pattern: ^/
                    user_checkers: ["app.user_checker"]

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
                <firewall name="admin" pattern="^/admin">
                    <user-checkers>app.user_checker</user-checkers>
                    <user-checkers>app.admin_checker</user-checkers>
                    <!-- ... -->
                </firewall>
                <firewall name="secured_area" pattern="^/">
                    <user-checkers>app.user_checker</user-checkers>
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php

        // ...
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'admin' => array(
                    'pattern' => '^/admin',
                    'user_checkers' => array(
                        'app.user_checker',
                        'app.admin_checker',
                    ),
                    // ...
                ),
                'secured_area' => array(
                    'pattern' => '^/',
                    'user_checkers' => array('app.user_checker'),
                    // ...
                ),
            ),
        ));
