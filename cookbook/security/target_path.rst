.. index::
   single: Security; Target redirect path

How to change the Default Target Path Behavior
==============================================

By default, the security component retains the information of the last request
URI in a session variable named ``_security.target_path``. Upon a successful
login, the user is redirected to this path, as to help her continue from
the last known page she visited.

On some occasions, this is unexpected. For example when the last request
URI was an HTTP POST against a route which is configured to allow only a POST
method, the user is redirected to this route only to get a 404 error.

To get around this behavior, you would simply need to extend the ``ExceptionListener``
class and override the default method named ``setTargetPath()``.

First, override the ``security.exception_listener.class`` parameter in your
configuration file. This can be done from your main configuration file (in
`app/config`) or from a configuration file being imported from a bundle:

.. configuration-block::

        .. code-block:: yaml

            # src/Acme/HelloBundle/Resources/config/services.yml
            parameters:
                # ...
                security.exception_listener.class: Acme\HelloBundle\Security\Firewall\ExceptionListener

        .. code-block:: xml

            <!-- src/Acme/HelloBundle/Resources/config/services.xml -->
            <parameters>
                <!-- ... -->
                <parameter key="security.exception_listener.class">Acme\HelloBundle\Security\Firewall\ExceptionListener</parameter>
            </parameters>

        .. code-block:: php

            // src/Acme/HelloBundle/Resources/config/services.php
            // ...
            $container->setParameter('security.exception_listener.class', 'Acme\HelloBundle\Security\Firewall\ExceptionListener');

Next, create your own ``ExceptionListener``::

    // src/Acme/HelloBundle/Security/Firewall/ExceptionListener.php
    namespace Acme\HelloBundle\Security\Firewall;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

    class ExceptionListener extends BaseExceptionListener
    {
        protected function setTargetPath(Request $request)
        {
            // Do not save target path for XHR and non-GET requests
            // You can add any more logic here you want
            if ($request->isXmlHttpRequest() || 'GET' !== $request->getMethod()) {
                return;
            }

            $request->getSession()->set('_security.target_path', $request->getUri());
        }
    }

Add as much or few logic here as required for your scenario!