How to Customize Access Denied Responses
========================================

In Symfony, you can throw an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`
to disallow access to the user. Symfony will handle this exception and
generates a response based on the authentication state:

* **If the user is not authenticated** (or authenticated anonymously), an
  authentication entry point is used to generate a response (typically
  a redirect to the login page or an *401 Unauthorized* response);
* **If the user is authenticated, but does not have the required
  permissions**, a *403 Forbidden* response is generated.

.. _security-entry-point:

Customize the Unauthorized Response
-----------------------------------

You need to create a class that implements
:class:`Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface`.
This interface has one method (``start()``) that is called whenever an
unauthenticated user tries to access a protected resource::

    // src/Security/AuthenticationEntryPoint.php
    namespace App\Security;

    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Security\Core\Exception\AuthenticationException;
    use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

    class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
    {
        public function __construct(
            private UrlGeneratorInterface $urlGenerator,
        ) {
        }

        public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
        {
            // add a custom flash message and redirect to the login page
            $request->getSession()->getFlashBag()->add('note', 'You have to login in order to access this page.');

            return new RedirectResponse($this->urlGenerator->generate('security_login'));
        }
    }

That's it if you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`.
Otherwise, you have to register this service in the container.

Now, configure this service ID as the entry point for the firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        firewalls:
            # ...

            main:
                # ...
                entry_point: App\Security\AuthenticationEntryPoint

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main"
                    entry-point="App\Security\AuthenticationEntryPoint"
                >
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AuthenticationEntryPoint;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                // ....
                ->entryPoint(AuthenticationEntryPoint::class)
            ;
        };

Customize the Forbidden Response
--------------------------------

Create a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authorization\\AccessDeniedHandlerInterface`.
This interface defines one method called ``handle()`` where you can
implement whatever logic that should execute when access is denied for the
current user (e.g. send a mail, log a message, or generally return a custom
response)::

    // src/Security/AccessDeniedHandler.php
    namespace App\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

    class AccessDeniedHandler implements AccessDeniedHandlerInterface
    {
        public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
        {
            // ...

            return new Response($content, 403);
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service. You can then
configure it under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        firewalls:
            # ...

            main:
                # ...
                access_denied_handler: App\Security\AccessDeniedHandler

    .. code-block:: xml

        <!-- config/packages/security.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <srv:container xmlns="http://symfony.com/schema/dic/security"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:srv="http://symfony.com/schema/dic/services"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <config>
                <firewall name="main"
                    access-denied-handler="App\Security\AccessDeniedHandler"
                >
                    <!-- ... -->
                </firewall>
            </config>
        </srv:container>

    .. code-block:: php

        // config/packages/security.php
        use App\Security\AccessDeniedHandler;
        use Symfony\Config\SecurityConfig;

        return static function (SecurityConfig $security): void {
            $security->firewall('main')
                // ....
                ->accessDeniedHandler(AccessDeniedHandler::class)
            ;
        };

Customizing All Access Denied Responses
---------------------------------------

In some cases, you might want to customize both responses or do a specific
action (e.g. logging) for each ``AccessDeniedException``. In this case,
configure a :ref:`kernel.exception listener <use-kernel-exception-event>`::

    // src/EventListener/AccessDeniedListener.php
    namespace App\EventListener;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class AccessDeniedListener implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                // the priority must be greater than the Security HTTP
                // ExceptionListener, to make sure it's called before
                // the default exception listener
                KernelEvents::EXCEPTION => ['onKernelException', 2],
            ];
        }

        public function onKernelException(ExceptionEvent $event): void
        {
            $exception = $event->getThrowable();
            if (!$exception instanceof AccessDeniedException) {
                return;
            }

            // ... perform some action (e.g. logging)

            // optionally set the custom response
            $event->setResponse(new Response(null, 403));

            // or stop propagation (prevents the next exception listeners from being called)
            //$event->stopPropagation();
        }
    }
