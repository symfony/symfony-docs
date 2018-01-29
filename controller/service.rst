.. index::
   single: Controller; As Services

How to Define Controllers as Services
=====================================

In Symfony, a controller does *not* need to be registered as a service. But if you're
using the :ref:`default services.yml configuration <service-container-services-load-example>`,
your controllers *are* already registered as services. This means you can use dependency
injection like any other normal service.

Referencing your Service from Routing
-------------------------------------

In addition to registering your controller as a service, you also need to make
sure that your routing references the service properly, so that Symfony can find
it. Use the ``service_id:method_name`` syntax:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/HelloController.php
        // ...

        use Symfony\Component\Routing\Annotation\Route;

        /**
         * @Route(service="AppBundle\Controller\HelloController")
         */
        class HelloController
        {
            // ...
        }

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:     /hello
            defaults: { _controller: AppBundle\Controller\HelloController:indexAction }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello">
                <default key="_controller">AppBundle\Controller\HelloController:indexAction</default>
            </route>

        </routes>

    .. code-block:: php

        // app/config/routing.php
        $collection->add('hello', new Route('/hello', array(
            '_controller' => 'AppBundle\Controller\HelloController:indexAction',
        )));

.. _controller-service-invoke:

Invokable Controllers
---------------------

If your controller implements the ``__invoke()`` method - popular with the
Action-Domain-Response (ADR) pattern, you can omit the method name from the
``service_id:method_name`` syntax (in the previous example, you just need to
use ``AppBundle\Controller\HelloController``).

Alternatives to base Controller Methods
---------------------------------------

When using a controller defined as a service, you can still extend any of the
:ref:`normal base controller <the-base-controller-class-services>` classes and
use their shortcuts. But, you don't need to! You can choose to extend *nothing*,
and use dependency injection to access difference services.

The base `Controller class source code`_ is a great way to see how to accomplish
common tasks. For example, ``$this->render()`` is usually used to render a Twig
template and return a Response. But, you can also do this directly:

In a controller that's defined as a service, you can instead inject the ``templating``
service and use it directly::

    // src/AppBundle/Controller/HelloController.php
    namespace AppBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        private $twig;

        public function __construct(\Twig_Environment $twig)
        {
            $this->twig = $twig;
        }

        public function indexAction($name)
        {
            $content = $this->twig->render(
                'hello/index.html.twig',
                array('name' => $name)
            );

            return new Response($content);
        }
    }

You can also use a special :ref:`action-based dependency injection <controller-accessing-services>`
to receive services as arguments to your controller action methods.

Base Controller Methods and Their Service Replacements
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to see how to replace base ``Controller`` convenience methods is to
look at the `ControllerTrait`_ that holds its logic.

If you want to know what type-hints to use for each service, see the
``getSubscribedServices()`` method in `AbstractController`_.

.. _`Controller class source code`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/ControllerTrait.php
.. _`base Controller class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/ControllerTrait.php
.. _`ControllerTrait`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/ControllerTrait.php
.. _`AbstractController`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/AbstractController.php
