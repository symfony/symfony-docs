.. index::
   single: Security; Login
   single: Security; Logout
   single: Security; Handler

How to Customize the Success and Failure Login Handlers
=======================================================

After the users successfully log in in your application, they are redirected to
the proper URL according to the security configuration options. This is done in
the :class:`Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationSuccessHandler`
class and Symfony defines a similar class called ``DefaultAuthenticationFailureHandler``
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

.. code-block:: yaml

    # app/config/services.yml
    services:
        app.security.success_login:
            class: AppBundle\Security\SuccesfulLoginHandler

Lastly, add a new ``success_handler`` option under the configuration of the
firewalls where this handler will be enabled and pass the ``id`` of the service
as its value:

.. code-block:: yaml

    # app/config/security.yml
    firewalls:
        main:
            pattern: ^/
            form_login:
                success_handler: app.security.successful_login

Creating a Failure Login Handler
--------------------------------

The steps to follow are identical to the ones explained in the previous section.
First, define your own logic in a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationFailureHandlerInterface`
and create a new service for it. Then, add the ``failure_handler`` configuration
option in your firewall:

.. code-block:: yaml

    # app/config/security.yml
    firewalls:
        main:
            pattern: ^/
            form_login:
                failure_handler: app.security.failure_login

When Should Login Handlers Be Used?
-----------------------------------

Symfony defines an event called ``security.interactive_login`` that lets you
customize the behavior of the login process. The main differences between this
event and the login handlers are XXXX and YYYY.

Therefore, you should use the login handlers when XXXX and YYYY, whereas the
interactive login event is better for ZZZZ.
