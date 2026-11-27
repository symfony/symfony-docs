How to Customize Entry Points and Access Denied Responses
=========================================================

In Symfony, you can throw an
:class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`
to disallow access to the user. Symfony will handle this exception and
generate a response based on the authentication state:

* **If the user is not authenticated** (or authenticated anonymously), an
  authentication entry point is used to generate a response (typically
  a redirect to the login page or a *401 Unauthorized* response);
* **If the user is authenticated, but does not have the required
  permissions**, a *403 Forbidden* response is generated.

.. _security-entry-point:

Customize the Unauthorized Response
-----------------------------------

When an unauthenticated user tries to access a protected page, Symfony
gives them a suitable response to let them start authentication (e.g.
redirect to a login form or show a 401 Unauthorized HTTP response for
APIs). This response is created by the *authentication entry point* of the
firewall. To customize it, create a class that implements
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

        public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
        {
            // add a custom flash message and redirect to the login page
            $request->getSession()->getFlashBag()->add('note', 'You have to log in to access this page.');

            return new RedirectResponse($this->urlGenerator->generate('security_login'));
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
the handler is ready to use. Otherwise, you have to register this service in the
container.

Now, configure this service ID as the entry point for the firewall:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        firewalls:
            # ...

            main:
                # ...
                entry_point: App\Security\AuthenticationEntryPoint

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AuthenticationEntryPoint;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'entry_point' => AuthenticationEntryPoint::class,
                    ],
                ],
            ],
        ]);

Configure the Entry Point of a Firewall
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A firewall can have multiple ways to authenticate (e.g. both a form login and
a social login). If more than one of its authenticators provides an entry
point, you must choose which one to use with the ``entry_point`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                main:
                    # allow authentication using a form or a custom authenticator
                    form_login: ~
                    custom_authenticators:
                        - App\Security\SocialConnectAuthenticator

                    # use the form login as the entry point for unauthenticated users
                    entry_point: form_login

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\SocialConnectAuthenticator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        // allow authentication using a form or a custom authenticator
                        'form_login' => null,
                        'custom_authenticators' => [
                            SocialConnectAuthenticator::class,
                        ],
                        // use the form login as the entry point for unauthenticated users
                        'entry_point' => 'form_login',
                    ],
                ],
            ],
        ]);

The ``entry_point`` setting accepts the name of one of the authenticators of
the firewall (e.g. ``form_login``) or the service ID of a custom entry point,
like the ``App\Security\AuthenticationEntryPoint`` class shown above.

Multiple Authenticators with Separate Entry Points
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Some applications use different authenticators to protect different parts of
the application. For example, a login form protects the main website and API
keys protect the API endpoints used by external parties.

A firewall can only have one entry point, so split the configuration into two
separate firewalls:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/security.yaml
        security:
            # ...
            firewalls:
                api:
                    pattern: ^/api/
                    custom_authenticators:
                        - App\Security\ApiTokenAuthenticator
                main:
                    lazy: true
                    form_login: ~

            access_control:
                - { path: '^/login', roles: PUBLIC_ACCESS }
                - { path: '^/api', roles: ROLE_API_USER }
                - { path: '^/', roles: ROLE_USER }

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\ApiTokenAuthenticator;

        return App::config([
            'security' => [
                'firewalls' => [
                    'api' => [
                        'pattern' => '^/api/',
                        'custom_authenticators' => [
                            ApiTokenAuthenticator::class,
                        ],
                    ],
                    'main' => [
                        'lazy' => true,
                        'form_login' => null,
                    ],
                ],
                'access_control' => [
                    ['path' => '^/login', 'roles' => 'PUBLIC_ACCESS'],
                    ['path' => '^/api', 'roles' => 'ROLE_API_USER'],
                    ['path' => '^/', 'roles' => 'ROLE_USER'],
                ],
            ],
        ]);

Customize the Forbidden Response
--------------------------------

Create a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authorization\\AccessDeniedHandlerInterface`.
This interface defines one method called ``handle()`` where you can
implement any logic that should execute when access is denied for the
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

    .. code-block:: php

        // config/packages/security.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Security\AccessDeniedHandler;

        return App::config([
            'security' => [
                'firewalls' => [
                    'main' => [
                        'access_denied_handler' => AccessDeniedHandler::class,
                    ],
                ],
            ],
        ]);

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
