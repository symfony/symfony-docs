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

.. versionadded:: 5.1

    The ``CustomUserMessageAccountStatusException`` class was introduced in Symfony 5.1.

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

        return static function (SecurityConfig $security) {
            // ...
            $security->firewall('main')
                ->pattern('^/')
                ->userChecker(UserChecker::class)
                // ...
            ;
        };
