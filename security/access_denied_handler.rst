.. index::
    single: Security; Creating a Custom Access Denied Handler

How to Create a Custom Access Denied Handler
============================================

When your application throws an ``AccessDeniedException``, you can handle this exception
with a service to return a custom response.

First, create a class that implements
:class:`Symfony\\Component\\Security\\Http\\Authorization\\AccessDeniedHandlerInterface`.
This interface defines one method called ``handle()`` where you can implement whatever
logic that should execute when access is denied for the current user (e.g. send a
mail, log a message, or generally return a custom response)::

    namespace AppBundle\Security;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

    class AccessDeniedHandler implements AccessDeniedHandlerInterface
    {
        public function handle(Request $request, AccessDeniedException $accessDeniedException)
        {
            // ...

            return new Response($content, 403);
        }
    }

If you're using the :ref:`default services.yml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service. You can then
configure it under your firewall:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        firewalls:
            # ...

            main:
                # ...
                access_denied_handler: AppBundle\Security\AccessDeniedHandler

    .. code-block:: xml

        <config>
            <firewall name="main">
                <access_denied_handler>AppBundle\Security\AccessDeniedHandler</access_denied_handler>
            </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        use AppBundle\Security\AccessDeniedHandler;

        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'main' => array(
                    // ...
                    'access_denied_handler' => AccessDeniedHandler::class,
                ),
            ),
        ));

That's it! Any ``AccessDeniedException`` thrown by code under the ``main`` firewall
will now be handled by your service.
