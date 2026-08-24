How to Define Controllers as Services
=====================================

In Symfony, a controller does *not* need to be registered as a service. But if
you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
and your controllers extend the `AbstractController`_ class, they *are* automatically
registered as services. This means you can use dependency injection like any
other normal service.

If you prefer to not extend the ``AbstractController`` class, you can register
your controllers as services in several ways:

#. Using the ``#[Route]`` attribute;
#. Using the ``#[AsController]`` attribute;
#. Using the ``controller.service_arguments`` service tag.

All three approaches rely on the same underlying mechanism: they apply the
``controller.service_arguments`` tag to the controller service. Symfony then
marks any service carrying that tag as public and non-lazy, and enables
:ref:`service argument injection <controller-accessing-services>` on its
action methods. The three options only differ in how you opt in to that tag.

The services are public because Symfony's controller resolver fetches them
from the container by their service id at runtime (for example, when a route
points to ``App\Controller\HelloController::index``), and private services
cannot be retrieved that way. They are also marked non-lazy because the resolver
invokes the controller immediately after fetching it, so proxy indirection
would be unnecessary.

Using the ``#[Route]`` Attribute
--------------------------------

When using :ref:`the #[Route] attribute <routing-route-attributes>` to define
routes on any PHP class, Symfony automatically applies the
``controller.service_arguments`` tag to it. As explained above, this registers
the class as a public, non-lazy service and enables service argument injection.

This is the simplest and recommended way to register controllers as services
when not extending the base controller class.

Using the ``#[AsController]`` Attribute
---------------------------------------

If you prefer, you can use the ``#[AsController]`` PHP attribute to automatically
apply the ``controller.service_arguments`` tag to your controller services.
The end result is the same as with ``#[Route]``: the class becomes a public,
non-lazy service with service argument injection enabled on its action methods::

    // src/Controller/HelloController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\AsController;
    use Symfony\Component\Routing\Attribute\Route;

    #[AsController]
    class HelloController
    {
        #[Route('/hello', name: 'hello', methods: ['GET'])]
        public function index(): Response
        {
            // ...
        }
    }

.. tip::

    When using the ``#[Route]`` attribute, Symfony already registers the controller
    class as a service, so using the ``#[AsController]`` attribute is redundant.

Using the ``controller.service_arguments`` Service Tag
------------------------------------------------------

If your controllers don't extend the `AbstractController`_ class and you don't
use the ``#[AsController]`` or ``#[Route]`` attributes, you must register the
controllers as public services manually and apply the ``controller.service_arguments``
:doc:`service tag </service_container/tags>` to enable service injection in
controller actions:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        # controllers are imported separately to make sure services can be injected
        # as action arguments even if you don't extend any base controller class
        App\Controller\:
           resource: '../src/Controller/'
           tags: ['controller.service_arguments']

.. note::

    If you don't use either :doc:`autowiring </service_container/autowiring>`
    or :ref:`autoconfiguration <services-autoconfigure>` and you extend the
    ``AbstractController``, you'll need to apply other tags and make some method
    calls to register your controllers as services:

    .. code-block:: yaml

        # config/services.yaml

        # this extended configuration is only required when not using autowiring/autoconfiguration,
        # which is uncommon and not recommended

        abstract_controller.locator:
            class: Symfony\Component\DependencyInjection\ServiceLocator
            arguments:
                -
                    router: '@router'
                    request_stack: '@request_stack'
                    http_kernel: '@http_kernel'
                    session: '@session'
                    parameter_bag: '@parameter_bag'
                    # you can add more services here as you need them (e.g. the `serializer`
                    # service) and have a look at the AbstractController class to see
                    # which services are defined in the locator

        App\Controller\:
            resource: '../src/Controller/'
            tags: ['controller.service_arguments']
            calls:
                - [setContainer, ['@abstract_controller.locator']]

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
        use Symfony\Component\Routing\Attribute\Route;

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

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\HelloController;

        return Routes::config([
            'hello' => [
                'path' => '/hello',
                'controller' => [HelloController::class, 'index'],
                'methods' => ['GET'],
            ],
        ]);

.. _controller-service-invoke:

Invokable Controllers
---------------------

Any controller (whether or not it is registered as a service) can also define a
single action using the ``__invoke()`` method, which is a common practice when
following the `ADR pattern`_ (Action-Domain-Responder). When the class is
registered as a service (for example via ``#[Route]`` on the class, as shown
below), the ``__invoke()`` method benefits from service argument injection just
like any other action method:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/HelloController.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Attribute\Route;

        #[Route('/hello/{name}', name: 'hello')]
        class HelloController
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

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\HelloController;

        return Routes::config([
            'hello' => [
                'path' => '/hello/{name}',
                'controller' => [HelloController::class, 'index'],
            ],
        ]);

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

.. _controller-allowed-controllers:

Controller Allowlist
--------------------

For security reasons, Symfony maintains an allowlist of controllers that are
permitted to handle requests. Controllers that are not in this list will be
rejected when Symfony needs to verify their legitimacy (e.g. when rendering
:doc:`ESI fragments </http_cache/esi>` or using the
:ref:`fragment renderer <templates-embed-controllers>`).

The following controllers are automatically allowed:

* Classes using the ``#[AsController]`` attribute;
* Classes extending ``AbstractController``;
* The built-in ``TemplateController``;
* All services tagged with ``controller.service_arguments``.

If you use the ``#[Route]`` attribute on a class, Symfony already registers it
as a service with the ``controller.service_arguments`` tag, so it is
automatically allowed.

For bundle authors or advanced use cases where a controller does not match any
of these criteria, call the
:method:`Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver::allowControllers`
method on the ``controller_resolver`` service to register additional controller
types or attributes::

    // src/DependencyInjection/MyBundleExtension.php
    namespace App\DependencyInjection;

    use App\Controller\CustomFragmentController;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Extension\Extension;

    class MyBundleExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container): void
        {
            $container->getDefinition('controller_resolver')
                ->addMethodCall('allowControllers', [[CustomFragmentController::class]]);
        }
    }

The ``allowControllers()`` method accepts two arguments: an array of class names
(``$types``) and an array of attribute class names (``$attributes``). A controller
is allowed if it is an instance of one of the given types or if its class has
one of the given attributes.

.. _`Controller class source code`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
.. _`AbstractController`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
.. _`ADR pattern`: https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder
