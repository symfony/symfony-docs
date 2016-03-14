.. index::
   single: Controller; As Services

How to Define Controllers as Services
=====================================

In the book, you've learned how easily a controller can be used when it
extends the base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class. While
this works fine, controllers can also be specified as services.

.. note::

    Specifying a controller as a service takes a bit more work. The
    primary advantage is that the entire controller or any services passed to
    the controller can be modified via the service container configuration.
    This is especially useful when developing an open-source bundle or any
    bundle that will be used in different projects.

    A second advantage is that your controllers are more "sandboxed". By
    looking at the constructor arguments, it's easy to see what types of things
    this controller may or may not do. And because each dependency needs
    to be injected manually, it's more obvious (i.e. if you have many constructor
    arguments) when your controller is becoming too big. The recommendation from
    the :doc:`best practices </best_practices/controllers>` is also valid for
    controllers defined as services: Avoid putting your business logic into the
    controllers. Instead, inject services that do the bulk of the work.

    So, even if you don't specify your controllers as services, you'll likely
    see this done in some open-source Symfony bundles. It's also important
    to understand the pros and cons of both approaches.

Defining the Controller as a Service
------------------------------------

A controller can be defined as a service in the same way as any other class.
For example, if you have the following simple controller::

    // src/AppBundle/Controller/HelloController.php
    namespace AppBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

Then you can define it as a service as follows:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.hello_controller:
                class: AppBundle\Controller\HelloController

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.hello_controller" class="AppBundle\Controller\HelloController" />
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('app.hello_controller', new Definition(
            'AppBundle\Controller\HelloController'
        ));

Referring to the Service
------------------------

To refer to a controller that's defined as a service, use the single colon (:)
notation. For example, to forward to the ``indexAction()`` method of the service
defined above with the id ``app.hello_controller``::

    $this->forward('app.hello_controller:indexAction', array('name' => $name));

.. note::

    You cannot drop the ``Action`` part of the method name when using this
    syntax.

You can also route to the service by using the same notation when defining
the route ``_controller`` value:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:     /hello
            defaults: { _controller: app.hello_controller:indexAction }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <route id="hello" path="/hello">
            <default key="_controller">app.hello_controller:indexAction</default>
        </route>

    .. code-block:: php

        // app/config/routing.php
        $collection->add('hello', new Route('/hello', array(
            '_controller' => 'app.hello_controller:indexAction',
        )));

.. tip::

    You can also use annotations to configure routing using a controller
    defined as a service. See the `FrameworkExtraBundle documentation`_ for
    details.

.. tip::

    If your controller implements the ``__invoke()`` method, you can simply
    refer to the service id (``app.hello_controller``).

Alternatives to base Controller Methods
---------------------------------------

When using a controller defined as a service, it will most likely not extend
the base ``Controller`` class. Instead of relying on its shortcut methods,
you'll interact directly with the services that you need. Fortunately, this is
usually pretty easy and the base `Controller class source code`_ is a great
source on how to perform many common tasks.

For example, if you want to render a template instead of creating the ``Response``
object directly, then your code would look like this if you were extending
Symfony's base controller::

    // src/AppBundle/Controller/HelloController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return $this->render(
                'AppBundle:Hello:index.html.twig',
                array('name' => $name)
            );
        }
    }

If you look at the source code for the ``render`` function in Symfony's
`base Controller class`_, you'll see that this method actually uses the
``templating`` service::

    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

In a controller that's defined as a service, you can instead inject the ``templating``
service and use it directly::

    // src/AppBundle/Controller/HelloController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        private $templating;

        public function __construct(EngineInterface $templating)
        {
            $this->templating = $templating;
        }

        public function indexAction($name)
        {
            return $this->templating->renderResponse(
                'AppBundle:Hello:index.html.twig',
                array('name' => $name)
            );
        }
    }

The service definition also needs modifying to specify the constructor
argument:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.hello_controller:
                class:     AppBundle\Controller\HelloController
                arguments: ['@templating']

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="app.hello_controller" class="AppBundle\Controller\HelloController">
                <argument type="service" id="templating"/>
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('app.hello_controller', new Definition(
            'AppBundle\Controller\HelloController',
            array(new Reference('templating'))
        ));

Rather than fetching the ``templating`` service from the container, you can
inject *only* the exact service(s) that you need directly into the controller.

.. note::

   This does not mean that you cannot extend these controllers from your own
   base controller. The move away from the standard base controller is because
   its helper methods rely on having the container available which is not
   the case for controllers that are defined as services. It may be a good
   idea to extract common code into a service that's injected rather than
   place that code into a base controller that you extend. Both approaches
   are valid, exactly how you want to organize your reusable code is up to
   you.

Base Controller Methods and Their Service Replacements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This list explains how to replace the convenience methods of the base
controller:

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::createForm` (service: ``form.factory``)
    .. code-block:: php

        $formFactory->create($type, $data, $options);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::createFormBuilder` (service: ``form.factory``)
    .. code-block:: php

        $formFactory->createBuilder('form', $data, $options);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::createNotFoundException`
    .. code-block:: php

        new NotFoundHttpException($message, $previous);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::forward` (service: ``http_kernel``)
    .. code-block:: php

        use Symfony\Component\HttpKernel\HttpKernelInterface;
        // ...

        $request = ...;
        $attributes = array_merge($path, array('_controller' => $controller));
        $subRequest = $request->duplicate($query, null, $attributes);
        $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::generateUrl` (service: ``router``)
    .. code-block:: php

       $router->generate($route, $params, $referenceType);

    .. note::

        The ``$referenceType`` argument must be one of the constants defined
        in the :class:`Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface`.

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getDoctrine` (service: ``doctrine``)
    *Simply inject doctrine instead of fetching it from the container.*

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getUser` (service: ``security.token_storage``)
    .. code-block:: php

        $user = null;
        $token = $tokenStorage->getToken();
        if (null !== $token && is_object($token->getUser())) {
             $user = $token->getUser();
        }

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::isGranted` (service: ``security.authorization_checker``)
    .. code-block:: php

        $authChecker->isGranted($attributes, $object);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::redirect`
    .. code-block:: php

        use Symfony\Component\HttpFoundation\RedirectResponse;

        return new RedirectResponse($url, $status);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::render` (service: ``templating``)
    .. code-block:: php

        $templating->renderResponse($view, $parameters, $response);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::renderView` (service: ``templating``)
    .. code-block:: php

       $templating->render($view, $parameters);

:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::stream` (service: ``templating``)
    .. code-block:: php

        use Symfony\Component\HttpFoundation\StreamedResponse;

        $templating = $this->templating;
        $callback = function () use ($templating, $view, $parameters) {
            $templating->stream($view, $parameters);
        }

        return new StreamedResponse($callback);

.. tip::

    ``getRequest`` has been deprecated. Instead, have an argument to your
    controller action method called ``Request $request``. The order of the
    parameters is not important, but the typehint must be provided.

.. _`Controller class source code`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php
.. _`base Controller class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php
.. _`FrameworkExtraBundle documentation`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html
