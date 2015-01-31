.. index::
   single: Controller; As Services

How to Define Controllers as Services
=====================================

In the book, you've learned how easily a controller can be used when it
extends the base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class. While
this works fine, controllers can also be specified as services.

.. note::

    Specifying a controller as a service takes a little bit more work. The
    primary advantage is that the entire controller or any services passed to
    the controller can be modified via the service container configuration.
    This is especially useful when developing an open-source bundle or any
    bundle that will be used in many different projects.

    A second advantage is that your controllers are more "sandboxed". By
    looking at the constructor arguments, it's easy to see what types of things
    this controller may or may not do. And because each dependency needs
    to be injected manually, it's more obvious (i.e. if you have many constructor
    arguments) when your controller has become too big, and may need to be
    split into multiple controllers.

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

.. versionadded:: 2.6
    If your controller service implements the ``__invoke`` method, you can simply refer to the service id
    (``acme.hello.controller``).

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
                arguments: ["@templating"]

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

.. _`Controller class source code`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php
.. _`base Controller class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php
.. _`FrameworkExtraBundle documentation`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html
