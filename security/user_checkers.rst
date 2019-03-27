.. index::
    single: Security; Creating and Enabling Custom User Checkers

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
are not met, an exception should be thrown which extends the
:class:`Symfony\\Component\\Security\\Core\\Exception\\AccountStatusException`::

    namespace App\Security;

    use App\Exception\AccountDeletedException;
    use App\Security\User as AppUser;
    use Symfony\Component\Security\Core\Exception\AccountExpiredException;
    use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
    use Symfony\Component\Security\Core\User\UserCheckerInterface;
    use Symfony\Component\Security\Core\User\UserInterface;

    class UserChecker implements UserCheckerInterface
    {
        public function checkPreAuth(UserInterface $user)
        {
            if (!$user instanceof AppUser) {
                return;
            }

            // user is deleted, show a generic Account Not Found message.
            if ($user->isDeleted()) {
                throw new AccountDeletedException();
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
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="main" pattern="^/">
                    <user-checker>App\Security\UserChecker</user-checker>
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php

        // ...
        use App\Security\UserChecker;

        $container->loadFromExtension('security', [
            'firewalls' => [
                'main' => [
                    'pattern' => '^/',
                    'user_checker' => UserChecker::class,
                    // ...
                ],
            ],
        ]);
