.. index::
   single: Security; Login
   single: Security; Logout
   single: Security; Handler

How to Customize the Success and Failure Login Handlers
=======================================================

After the users successfully log in to your application, they are redirected to
the proper URL according to the security configuration options. This is done in
the :class:`Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationSuccessHandler`
class and Symfony defines a similar class called
:class:`Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationFailureHandler`
to handle the login failures.

This article explains **how to define your own login handlers** to execute
custom logic after the user has logged in successfully or failed to do that.

Creating a Success Login Handler
--------------------------------

First, create a class that implements :class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationSuccessHandlerInterface`
and add your own logic::

    namespace AppBundle\Security;

    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

    class SuccesfulLoginHandler implements AuthenticationSuccessHandlerInterface
    {
        public function onAuthenticationSuccess(Request $request, TokenInterface $token)
        {
            // do something...

            // you can inherit from the DefaultAuthenticationSuccessHandler
            // class to reuse the logic that decides the URL to redirect to
            return new RedirectResponse(...);
        }
    }

Then, define a new service for this login handler:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.security.success_login:
                class: AppBundle\Security\SuccesfulLoginHandler

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.security.success_login"
                         class="AppBundle\Security\SuccesfulLoginHandler">
                </service>
            </services>
        </container>

    .. code-block:: php

        // src/AppBundle/Resources/config/services.php
        $container->register(
            'app.security.success_login',
            'AppBundle\Security\SuccesfulLoginHandler'
        );

Lastly, add a new ``success_handler`` option under the configuration of the
firewalls where this handler will be enabled and pass the ``id`` of the service
as its value:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            firewalls:
                main:
                    # ...
                    form_login:
                        success_handler: app.security.successful_login

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="main">
                    <!-- ... -->
                    <form-login success-handler="app.security.successful_login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        'success_handler' => 'app.security.successful_login',
                    ),
                ),
            ),
        ));

Creating a Failure Login Handler
--------------------------------

The steps to follow are identical to the ones explained in the previous section.
First, define your own logic in a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`
and create a new service for it. Then, add the ``failure_handler`` configuration
option in your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            # ...
            firewalls:
                main:
                    # ...
                    form_login:
                        failure_handler: app.security.failure_login

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <!-- ... -->
                <firewall name="main">
                    <!-- ... -->
                    <form-login failure-handler="app.security.failure_login" />
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            // ...
            'firewalls' => array(
                'main' => array(
                    // ...
                    'form_login' => array(
                        'failure_handler' => 'app.security.failure_login',
                    ),
                ),
            ),
        ));

When Should Login Handlers Be Used?
-----------------------------------

These security handlers are closely related to the ``security.authentication.success``
and ``security.authentication.failure`` events, but Symfony also defines an event
called ``security.interactive_login`` that lets you customize the behavior of
the login process.

The success/failure handlers should be used when you need to change the login
behavior on success/failure by changing the returned ``Response`` object.

The listener hooked into ``security.interactive_login`` should be used when you
need to execute some code on login success/failure but without altering the
``Response`` object being sent. For example, to store in a Redis cache the number
of failed login attempts to protect against brute-force attacks.
