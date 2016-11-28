.. index::
    single: Security; Creating a Custom Access Denied Handler

How to Create a Custom Access Denied Handler
============================================

When your application throws an ``AccessDeniedException``, you can handle this exception
with a service to return a custom response.

Each firewall context can define its own custom access denied handler:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        firewalls:
            foo:
                # ...
                access_denied_handler: app.security.access_denied_handler

    .. code-block:: xml

        <config>
          <firewall name="foo">
            <access_denied_handler>app.security.access_denied_handler</access_denied_handler>
          </firewall>
        </config>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', array(
            'firewalls' => array(
                'foo' => array(
                    // ...
                    'access_denied_handler' => 'app.security.access_denied_handler',
                ),
            ),
        ));


Your handler must implement the 
:class:`Symfony\\Component\\Security\\Http\\Authorization\\AccessDeniedHandlerInterface`.
This interface defines one method called ``handle()`` that implements the logic to
execute when access is denied to the current user (send a mail, log a message, or
generally return a custom response).

.. code-block:: php

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

Then, register the service for the access denied handler:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.security.access_denied_handler:
                class: AppBundle\Security\AccessDeniedHandler

    .. code-block:: xml

	<!-- app/config/services.xml -->
	<?xml version="1.0" encoding="UTF-8" ?>
	<container xmlns="http://symfony.com/schema/dic/services"
	    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	    xsi:schemaLocation="http://symfony.com/schema/dic/services
		http://symfony.com/schema/dic/services/services-1.0.xsd">

	    <services>
		<service id="app.security.access_denied_handler"
                    class="AppBundle\Security\AccessDeniedHandler" />
	    </services>
	</container>

    .. code-block:: php

	// app/config/services.php
	$container->register(
            'app.security.access_denied_handler',
	    'AppBundle\Security\AccessDeniedHandler'
	);

That's it! Any ``AccessDeniedException`` thrown by the ``foo`` firewall will now be handled by your service.
