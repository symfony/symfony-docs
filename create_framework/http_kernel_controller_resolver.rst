The HttpKernel Component: the Controller Resolver
=================================================

You might think that our framework is already pretty solid and you are
probably right. But let's see how we can improve it nonetheless.

Right now, all our examples use procedural code, but remember that controllers
can be any valid PHP callbacks. Let's convert our controller to a proper
class::

    class LeapYearController
    {
        public function index($request)
        {
            if (is_leap_year($request->attributes->get('year'))) {
                return new Response('Yep, this is a leap year!');
            }

            return new Response('Nope, this is not a leap year.');
        }
    }

Update the route definition accordingly::

    $routes->add('leap_year', new Routing\Route('/is_leap_year/{year}', [
        'year' => null,
        '_controller' => [new LeapYearController(), 'index'],
    ]));

The move is pretty straightforward and makes a lot of sense as soon as you
create more pages but you might have noticed a non-desirable side-effect...
The ``LeapYearController`` class is *always* instantiated, even if the
requested URL does not match the ``leap_year`` route. This is bad for one main
reason: performance wise, all controllers for all routes must now be
instantiated for every request. It would be better if controllers were
lazy-loaded so that only the controller associated with the matched route is
instantiated.

To solve this issue, and a bunch more, let's install and use the HttpKernel
component:

.. code-block:: terminal

    $ composer require symfony/http-kernel

The HttpKernel component has many interesting features, but the ones we need
right now are the *controller resolver* and *argument resolver*. A controller resolver knows how to
determine the controller to execute and the argument resolver determines the arguments to pass to it,
based on a Request object. All controller resolvers implement the following interface::

    namespace Symfony\Component\HttpKernel\Controller;

    // ...
    interface ControllerResolverInterface
    {
        public function getController(Request $request);
    }

The ``getController()`` method relies on the same convention as the one we
have defined earlier: the ``_controller`` request attribute must contain the
controller associated with the Request. Besides the built-in PHP callbacks,
``getController()`` also supports strings composed of a class name followed by
two colons and a method name as a valid callback, like 'class::method'::

    $routes->add('leap_year', new Routing\Route('/is_leap_year/{year}', [
        'year' => null,
        '_controller' => 'LeapYearController::index',
    ]));

To make this code work, modify the framework code to use the controller
resolver from HttpKernel::

    use Symfony\Component\HttpKernel;

    $controllerResolver = new HttpKernel\Controller\ControllerResolver();
    $argumentResolver = new HttpKernel\Controller\ArgumentResolver();

    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);

.. note::

    As an added bonus, the controller resolver properly handles the error
    management for you: when you forget to define a ``_controller`` attribute
    for a Route for instance.

Now, let's see how the controller arguments are guessed. ``getArguments()``
introspects the controller signature to determine which arguments to pass to
it by using the native PHP `reflection`_. This method is defined in the
following interface::

    namespace Symfony\Component\HttpKernel\Controller;

    // ...
    interface ArgumentResolverInterface
    {
        public function getArguments(Request $request, $controller);
    }

The ``index()`` method needs the Request object as an argument.
``getArguments()`` knows when to inject it properly if it is type-hinted
correctly::

    public function index(Request $request)

    // won't work
    public function index($request)

More interesting, ``getArguments()`` is also able to inject any Request
attribute; if the argument has the same name as the corresponding
attribute::

    public function index($year)

You can also inject the Request and some attributes at the same time (as the
matching is done on the argument name or a type hint, the arguments order does
not matter)::

    public function index(Request $request, $year)

    public function index($year, Request $request)

Finally, you can also define default values for any argument that matches an
optional attribute of the Request::

    public function index($year = 2012)

Let's inject the ``$year`` request attribute for our controller::

    class LeapYearController
    {
        public function index($year)
        {
            if (is_leap_year($year)) {
                return new Response('Yep, this is a leap year!');
            }

            return new Response('Nope, this is not a leap year.');
        }
    }

The resolvers also take care of validating the controller callable and its
arguments. In case of a problem, it throws an exception with a nice message
explaining the problem (the controller class does not exist, the method is not
defined, an argument has no matching attribute, ...).

.. note::

    With the great flexibility of the default controller resolver and argument
    resolver, you might wonder why someone would want to create another one
    (why would there be an interface if not?). Two examples: in Symfony,
    ``getController()`` is enhanced to support :doc:`controllers as services </controller/service>`;
    and ``getArguments()`` provides an extension point to alter or enhance
    the resolving of arguments.

Let's conclude with the new version of our framework::

    // example.com/web/front.php
    require_once __DIR__.'/../vendor/autoload.php';

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing;
    use Symfony\Component\HttpKernel;

    function render_template(Request $request)
    {
        extract($request->attributes->all(), EXTR_SKIP);
        ob_start();
        include sprintf(__DIR__.'/../src/pages/%s.php', $_route);

        return new Response(ob_get_clean());
    }

    $request = Request::createFromGlobals();
    $routes = include __DIR__.'/../src/app.php';

    $context = new Routing\RequestContext();
    $context->fromRequest($request);
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);

    $controllerResolver = new HttpKernel\Controller\ControllerResolver();
    $argumentResolver = new HttpKernel\Controller\ArgumentResolver();

    try {
        $request->attributes->add($matcher->match($request->getPathInfo()));

        $controller = $controllerResolver->getController($request);
        $arguments = $argumentResolver->getArguments($request, $controller);

        $response = call_user_func_array($controller, $arguments);
    } catch (Routing\Exception\ResourceNotFoundException $exception) {
        $response = new Response('Not Found', 404);
    } catch (Exception $exception) {
        $response = new Response('An error occurred', 500);
    }

    $response->send();

Think about it once more: our framework is more robust and more flexible than
ever and it still has less than 50 lines of code.

.. _`reflection`: https://php.net/reflection
.. _`FrameworkExtraBundle`: https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
