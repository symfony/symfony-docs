How to Create and Enable Custom User Checkers
=============================================

During the authentication of a user, additional checks might be required to verify
if the identified user is allowed to log in. By defining a custom user checker, you
can define per firewall which checker should be used.

Creating a Custom User Checker
------------------------------

User checkers are classes that must implement the
:class:`Symfony\\Component\\Security\\Core\\User\\UserCheckerInterface`. This interface
defines two methods called ``checkPreAuth()`` and ``checkPostAuth()`` to
perform checks before and after user authentication. If one or more conditions
are not met, throw an exception which extends the
:class:`Symfony\\Component\\Security\\Core\\Exception\\AccountStatusException` class.
Consider using :class:`Symfony\\Component\\Security\\Core\\Exception\\CustomUserMessageAccountStatusException`,
which extends ``AccountStatusException`` and allows to customize the error message
displayed to the user::

    namespace App\Security;

    use App\Entity\User as AppUser;
    use Symfony\Component\Security\Core\Exception\AccountExpiredException;
    use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
    use Symfony\Component\Security\Core\User\UserCheckerInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class UserChecker implements UserCheckerInterface
    {
        public function checkPreAuth(UserInterface $user): void
        {
            if (!$user instanceof AppUser) {
                return;
            }

            if ($user->isDeleted()) {
                // the message passed to this exception is meant to be displayed to the user
                throw new CustomUserMessageAccountStatusException('Your user account no longer exists.');
            }
        }

        public function checkPostAuth(UserInterface $user): void
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

Next, make sure your user checker is registered as a service. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the service is registered automatically.

All that's left to do is add the checker to the desired firewall where the value
is the service id of your user checker:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                main:
                    pattern: ^/
                    user_checker: App\Security\UserChecker
                    # ...

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
                <firewall name="main"
                        pattern="^/"
                        user-checker="App\Security\UserChecker">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\UserChecker;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('main')
                ->pattern('^/')
                ->userChecker(UserChecker::class)
                // ...
            ;
        };

Using Multiple User Checkers
----------------------------

.. versionadded:: 6.2

    The ``ChainUserChecker`` class was added in Symfony 6.2.

It is common for applications to have multiple authentication entry points (such as
traditional form based login and an API) which may have unique checker rules for each
entry point as well as common rules for all entry points. To allow using multiple user
checkers on a firewall, a service for the :class:`Symfony\\Component\\Security\\Core\\User\\ChainUserChecker`
class is created for each firewall.

To use the chain user checker, first you will need to tag your user checker services with the
``security.user_checker.<firewall>`` tag (where ``<firewall>`` is the name of the firewall
in your security configuration). The service tag also supports the priority attribute, allowing you to define the
order in which user checkers are called::

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        # ...
        services:
            App\Security\AccountEnabledUserChecker:
                tags:
                    - { name: security.user_checker.api, priority: 10 }
                    - { name: security.user_checker.main, priority: 10 }

            App\Security\APIAccessAllowedUserChecker:
                tags:
                    - { name: security.user_checker.api, priority: 5 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Security\AccountEnabledUserChecker">
                    <tag name="security.user_checker.api" priority="10"/>
                    <tag name="security.user_checker.main" priority="10"/>
                </service>

                <service id="App\Security\APIAccessAllowedUserChecker">
                    <tag name="security.user_checker.api" priority="5"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccountEnabledUserChecker;
        use App\Security\APIAccessAllowedUserChecker;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(AccountEnabledUserChecker::class)
                ->tag('security.user_checker.api', ['priority' => 10])
                ->tag('security.user_checker.main', ['priority' => 10]);

            $services->set(APIAccessAllowedUserChecker::class)
                ->tag('security.user_checker.api', ['priority' => 5]);
        };

Once your checker services are tagged, next you will need configure your firewalls to use the
``security.user_checker.chain.<firewall>`` service::

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml

        # ...
        security:
            firewalls:
                api:
                    pattern: ^/api
                    user_checker: security.user_checker.chain.api
                    # ...
                main:
                    pattern: ^/
                    user_checker: security.user_checker.chain.main
                    # ...

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
                <firewall name="api"
                        pattern="^/api"
                        user-checker="security.user_checker.chain.api">
                    <!-- ... -->
                </firewall>
                <firewall name="main"
                        pattern="^/"
                        user-checker="security.user_checker.chain.main">
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            // ...
            $security->firewall('api')
                ->pattern('^/api')
                ->userChecker('security.user_checker.chain.api')
                // ...
            ;

            $security->firewall('main')
                ->pattern('^/')
                ->userChecker('security.user_checker.chain.main')
                // ...
            ;
        };
