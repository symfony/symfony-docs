.. index::
    single: Action Domain Responder approach

How to implement the ADR pattern
================================

In Symfony, you're used to implement the MVC pattern and extending the default :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller`
class.
Since the 3.3 update, Symfony is capable of using natively the ADR approach.

Update the configuration
------------------------

The first step is to update the default services.yaml file, here's the new content: 

.. code-block:: yaml

    parameters:
        locale: 'en'

    services:
        _defaults:
            autowire: true
            autoconfigure: true
            public: false

        App\Actions\:
            resource: '../src/Actions'
            tags: 
                - 'controller.service_arguments'

Now that the container knows about our actions, time to build a simple Action !

Updating your classes
---------------------

As the framework evolve, you must update your classes, first, delete your Controller folder and create an Actions one then a new class using the ADR principles, for this example, call it ``HelloAction.php``: 

.. code-block:: php

    <?php

    namespace App\Action;

    use App\Responders\HelloResponder;
    use Symfony\Component\HttpFoundation\Response;

    final class HelloAction
    {
        public function __invoke(HelloResponder $responder): Response
        { 
            return $responder([
                'text' => 'Hello World'
            ]);
        }
    }

.. tip::

    As described in the DependencyInjection component documentation, you can still use the __construct() injection
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

Creating a Responder
--------------------

As you can see in the __invoke call, this action require a ``HelloResponder`` class in order to build the response which is returned to the browser, first, update the services.yaml according to this need: 

.. code-block:: yaml

    parameters:
        locale: 'en'

    services:
        _defaults:
            autowire: true
            autoconfigure: true
            public: false

        App\Actions\:
            resource: '../src/Actions'
            tags: 
                - 'controller.service_arguments'
                
        App\Responders\:
            resource: '../src/Responders'
            
Here, the container only need to know about the existence of the classes, nothing difficult to understand as the fact that our Responders are responsable of returning the actual Response to the browser, no need to add the 'controller.service_arguments' tags as the Responders need to be called using the __invoke method in order to receive data from the Action. 

Now that the logic behind is clear, time to create the ``HelloResponder.php`` file: 
                
.. code-block:: php

    <?php
    
    namespace App\Responders;

    use Twig\Environment;
    use Symfony\Component\HttpFoundation\Response;

    final class HomeResponder
    {
        private $twig;
        
        public function __construct(Environment $twig)
        {
            $this->twig = $twig;
        }

        public function __invoke(array $data)
        {
            return new Response(
                $this->twig->render('index.html.twig', $data)
            );
        }
    }

If the routing is clearly define, the browser should display the traditional "Hello World" using the ADR approach, congrats !

Accessing the request
---------------------

In many case, your classes can ask for any data passed via a form or via an API call, 
as you can imagine, as the logic evolve, your class is capable of accessing the request 
from a simple method injection like this:

.. code-block:: php

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
