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
class and override the default method named ``setTargetPath()``::

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

By preventing ``setTargetPath()`` from being called on the parent, the Security component
won't retain the request URI. Add as much or as little logic here as required for your scenario!

Next, create the ``ExceptionListenerPass`` to replace the definition of the default
``ExceptionListener`` with the one you just created. Make sure to use the name of 
the firewall in your security configuration::

    // src/AppBundle/DependencyInjection/Compiler/ExceptionListenerPass.php
    namespace AppBundle\DependencyInjection\Compiler;
    
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use AppBundle\Security\Firewall\ExceptionListener;

    class ExceptionListenerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            // Use the name of your firewall for the suffix e.g. 'secured_area'
            $definition = $container->getDefinition('security.exception_listener.secured_area');
            $definition->setClass(ExceptionListener::class);
        }
    }

Finally, add a compiler pass to tie it all together::

    // src/AppBundle/AppBundle.php
    namespace AppBundle;
    
    use AppBundle\DependencyInjection\Compiler\ExceptionListenerPass;

    class AppBundle extends Bundle
    {   
        public function build(ContainerBuilder $container)
        {
            $container->addCompilerPass(new ExceptionListenerPass());
        }
    }

