.. index::
    single: Action Domain Responder approach

How to implement the ADR pattern
================================

In Symfony, you're used to implement the MVC pattern and extending the default :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller`
class.
Since the 3.3 update, Symfony is capable of using natively the ADR approach.

Updating your configuration
---------------------------

As the framework evolve, you must update the services.yml file in order to
use the latest features of the DependencyInjection component, this way, here's the updates::

    # ...

    services:
        _defaults:
            autowire: true
            autoconfigure: true
            public: false

        # Allow to load every actions
        AppBundle\Action\:
            resource: '../../src/AppBundle/Action/'
            tags: ['controller.service_arguments']
            public: true

Once the file is updated, delete your Controller folder and create an Action class using the ADR principles, i.e::

    <?php

    namespace AppBundle\Action;

    use Twig\Environment;
    use Symfony\Component\HttpFoundation\Response;

    final class HelloAction
    {
        public function __invoke(Environment $twig): Response
        {
            return new Response($twig->render('default/index.html.twig'));
        }
    }

.. tip::

    As described in the DependencyInjection doc, you can still use the __construct() injection
    approach.

By default, we define the class with the final keyword because this class shouldn't be extended,
the logic is pretty simple to understand as you understand the ADR pattern, in fact, the 'Action'
is linked to a single request and his dependencies are linked to this precise Action.

.. tip::

    By using the final approach and the private visibility (inside the container), our class
    is faster to return and easier to keep out of the framework logic.

Once this is done, you can define the routes like before using multiples approaches:

.. configuration-block::

    .. code-block:: php-annotations

        # src/AppBundle/Action/HelloAction.php
        // ...

        /**
         * @Route("/hello", name="hello")
         */
        final class HelloAction
        {
            // ...
        }

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:     /hello
            defaults: { _controller: AppBundle\Action\HelloAction }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello">
                <default key="_controller">AppBundle\Action\HelloAction</default>
            </route>

        </routes>

    .. code-block:: php

        // app/config/routing.php
        use AppBundle\Action\HelloAction

        $collection->add('hello', new Route('/hello', array(
            '_controller' => HelloAction::class,
        )));

Accessing the request
---------------------

As you can imagine, as the logic evolve, your class is capable of accessing
the request from simple method injection like this ::

    <?php

        use Symfony\Component\HttpFoundation\Request;
        // ...

        public function __invoke(Environment $twig, Request $request): Response
        {
            $id = $request->get('id');
            
            return $twig->render('default/index.html.twig', array('id' => $id));
        }
    }
    
Final thought
-------------

Keep in mind that this approach can be completely different from what you're used to use, in order to
keep your code clean and easy to maintain, we recommend to use this approach only if your code is
decoupled from the internal framework logic (like with Clean Architecture approach) or if you start a new
project and need to keep the logic linked to your business rules.
