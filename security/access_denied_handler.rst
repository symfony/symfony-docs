.. index::
    single: Security; Creating a Custom Access Denied Handler

How to Create a Custom Access Denied Handler
============================================

When your application throw an ``AccessDeniedException`` you can catch this exception
with a service to return a custom Response.

On each firewall context you can define a custom access denied handler.

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        firewalls:
            foo:
                # ...
                access_denied_handler: custom_handler.service.id

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'foo' => array(
                    // ...
                    'access_denied_handler' => 'custom_handler.service.id',
                ),
            ),
        ));


Your handler must implement the interface
:class:`Symfony\\Component\\Security\\Http\\Authorization\\AccessDeniedHandlerInterface`.
This interface define one method called ``handle()`` that can do whatever you want.
You can use it to send a mail, log a message, or generally return a custom Response.


.. code-block:: php

    namespace AppBundle\Security;

    use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;

    class AccessDeniedHandler implements AccessDeniedHandlerInterface
    {
        public function handle(Request $request, AccessDeniedException $accessDeniedException)
        {
            // to some stuff...
            return new Response($content, 403);
        }
    }

Then you must register your service :

.. code-block:: yml

    # app/config/services.yml
    services:
        custom_handler.service.id:
            class: AppBundle\Security\AccessDeniedHandler

That's it, now on the ``foo`` firewall, all ``AccessDeniedException`` will be notified to you service.
