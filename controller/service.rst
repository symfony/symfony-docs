How to Define Controllers as Services
=====================================

In Symfony, a controller does *not* need to be registered as a service. But if
you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
and your controllers extend the `AbstractController`_ class, they *are* automatically
registered as services. This means you can use dependency injection like any
other normal service.

If your controllers don't extend the `AbstractController`_ class, you must
explicitly mark your controller services as ``public``. Alternatively, you can
apply the ``controller.service_arguments`` tag to your controller services. This
will make the tagged services ``public`` and will allow you to inject services
in method parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        # controllers are imported separately to make sure services can be injected
        # as action arguments even if you don't extend any base controller class
        App\Controller\:
           resource: '../src/Controller/'
           tags: ['controller.service_arguments']

If you prefer, you can use the ``#[AsController]`` PHP attribute to automatically
apply the ``controller.service_arguments`` tag to your controller services::

    // src/Controller/HelloController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\AsController;
    use Symfony\Component\Routing\Annotation\Route;

    #[AsController]
    class HelloController
    {
        #[Route('/hello', name: 'hello', methods: ['GET'])]
        public function index(): Response
        {
            // ...
        }
    }

Registering your controller as a service is the first step, but you also need to
update your routing config to reference the service properly, so that Symfony
knows to use it.

Use the ``service_id::method_name`` syntax to refer to the controller method.
If the service id is the fully-qualified class name (FQCN) of your controller,
as Symfony recommends, then the syntax is the same as if the controller was not
a service like: ``App\Controller\HelloController::index``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/HelloController.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class HelloController
        {
            #[Route('/hello', name: 'hello', methods: ['GET'])]
            public function index(): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        hello:
            path:       /hello
            controller: App\Controller\HelloController::index
            methods:    GET

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello" controller="App\Controller\HelloController::index" methods="GET"/>

        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\HelloController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->add('hello', '/hello')
                ->controller([HelloController::class, 'index'])
                ->methods(['GET'])
            ;
        };

.. _controller-service-invoke:

Invokable Controllers
---------------------

Controllers can also define a single action using the ``__invoke()`` method,
which is a common practice when following the `ADR pattern`_
(Action-Domain-Responder):

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/Hello.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        #[Route('/hello/{name}', name: 'hello')]
        class Hello
        {
            public function __invoke(string $name = 'World'): Response
            {
                return new Response(sprintf('Hello %s!', $name));
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        hello:
            path:       /hello/{name}
            controller: App\Controller\HelloController

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello/{name}">
                <default key="_controller">App\Controller\HelloController</default>
            </route>

        </routes>

    .. code-block:: php

        use App\Controller\HelloController;

        // app/config/routing.php
        $collection->add('hello', new Route('/hello', [
            '_controller' => HelloController::class,
        ]));

Alternatives to base Controller Methods
---------------------------------------

When using a controller defined as a service, you can still extend the
:ref:`AbstractController base controller <the-base-controller-class-services>`
and use its shortcuts. But, you don't need to! You can choose to extend *nothing*,
and use dependency injection to access different services.

The base `Controller class source code`_ is a great way to see how to accomplish
common tasks. For example, ``$this->render()`` is usually used to render a Twig
template and return a Response. But, you can also do this directly:

In a controller that's defined as a service, you can instead inject the ``twig``
service and use it directly::

    // src/Controller/HelloController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Twig\Environment;

    class HelloController
    {
        public function __construct(
            private Environment $twig,
        ) {
        }

        public function index(string $name): Response
        {
            $content = $this->twig->render(
                'hello/index.html.twig',
                ['name' => $name]
            );

            return new Response($content);
        }
    }

You can also use a special :ref:`action-based dependency injection <controller-accessing-services>`
to receive services as arguments to your controller action methods.

Base Controller Methods and Their Service Replacements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to see how to replace base ``Controller`` convenience methods is to
look at the `AbstractController`_ class that holds its logic.

If you want to know what type-hints to use for each service, see the
``getSubscribedServices()`` method in `AbstractController`_.

.. _`Controller class source code`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
.. _`AbstractController`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
.. _`ADR pattern`: https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder
