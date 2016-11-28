.. index::
   single: Security; Target redirect path

How to Change the default Target Path Behavior
==============================================

By default, the Security component retains the information of the last request
URI in a session variable named ``_security.main.target_path`` (with ``main`` being
the name of the firewall, defined in ``security.yml``). Upon a successful
login, the user is redirected to this path, as to help them continue from the
last known page they visited.

In some situations, this is not ideal. For example, when the last request
URI was an XMLHttpRequest which returned a non-HTML or partial HTML response,
the user is redirected back to a page which the browser cannot render.

To get around this behavior, you would simply need to extend the ``ExceptionListener``
class and override the default method named ``setTargetPath()``.

First, override the ``security.exception_listener.class`` parameter in your
configuration file. This can be done from your main configuration file (in
``app/config``) or from a configuration file being imported from a bundle:

.. configuration-block::

        .. code-block:: yaml

            # app/config/services.yml
            parameters:
                # ...
                security.exception_listener.class: AppBundle\Security\Firewall\ExceptionListener

        .. code-block:: xml

            <!-- app/config/services.xml -->
            <parameters>
                <!-- ... -->
                <parameter key="security.exception_listener.class">AppBundle\Security\Firewall\ExceptionListener</parameter>
            </parameters>

        .. code-block:: php

            // app/config/services.php
            // ...
            $container->setParameter('security.exception_listener.class', 'AppBundle\Security\Firewall\ExceptionListener');

Next, create your own ``ExceptionListener``::

    // src/AppBundle/Security/Firewall/ExceptionListener.php
    namespace AppBundle\Security\Firewall;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

    class ExceptionListener extends BaseExceptionListener
    {
        protected function setTargetPath(Request $request)
        {
            // Do not save target path for XHR requests
            // You can add any more logic here you want
            // Note that non-GET requests are already ignored
            if ($request->isXmlHttpRequest()) {
                return;
            }

            parent::setTargetPath($request);
        }
    }

Add as much or as little logic here as required for your scenario!
