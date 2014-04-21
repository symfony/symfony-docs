.. index::
   single: Controller

Controller
==========

A controller is a PHP function you create that takes information from the
HTTP request and constructs and returns an HTTP response (as a Symfony2
``Response`` object). The response could be an HTML page, an XML document,
a serialized JSON array, an image, a redirect, a 404 error or anything else
you can dream up. The controller contains whatever arbitrary logic *your
application* needs to render the content of a page.

See how simple this is by looking at a Symfony2 controller in action.
The following controller would render a page that simply prints ``Hello world!``::

    use Symfony\Component\HttpFoundation\Response;

    public function helloAction()
    {
        return new Response('Hello world!');
    }

The goal of a controller is always the same: create and return a ``Response``
object. Along the way, it might read information from the request, load a
database resource, send an email, or set information on the user's session.
But in all cases, the controller will eventually return the ``Response`` object
that will be delivered back to the client.

There's no magic and no other requirements to worry about! Here are a few
common examples:

* *Controller A* prepares a ``Response`` object representing the content
  for the homepage of the site.

* *Controller B* reads the ``slug`` parameter from the request to load a
  blog entry from the database and create a ``Response`` object displaying
  that blog. If the ``slug`` can't be found in the database, it creates and
  returns a ``Response`` object with a 404 status code.

* *Controller C* handles the form submission of a contact form. It reads
  the form information from the request, saves the contact information to
  the database and emails the contact information to the webmaster. Finally,
  it creates a ``Response`` object that redirects the client's browser to
  the contact form "thank you" page.

.. index::
   single: Controller; Request-controller-response lifecycle

Requests, Controller, Response Lifecycle
----------------------------------------

Every request handled by a Symfony2 project goes through the same simple lifecycle.
The framework takes care of the repetitive tasks and ultimately executes a
controller, which houses your custom application code:

#. Each request is handled by a single front controller file (e.g. ``app.php``
   or ``app_dev.php``) that bootstraps the application;

#. The ``Router`` reads information from the request (e.g. the URI), finds
   a route that matches that information, and reads the ``_controller`` parameter
   from the route;

#. The controller from the matched route is executed and the code inside the
   controller creates and returns a ``Response`` object;

#. The HTTP headers and content of the ``Response`` object are sent back to
   the client.

Creating a page is as easy as creating a controller (#3) and making a route that
maps a URL to that controller (#2).

.. note::

    Though similarly named, a "front controller" is different from the
    "controllers" talked about in this chapter. A front controller
    is a short PHP file that lives in your web directory and through which
    all requests are directed. A typical application will have a production
    front controller (e.g. ``app.php``) and a development front controller
    (e.g. ``app_dev.php``). You'll likely never need to edit, view or worry
    about the front controllers in your application.

.. index::
   single: Controller; Simple example

A Simple Controller
-------------------

While a controller can be any PHP callable (a function, method on an object,
or a ``Closure``), in Symfony2, a controller is usually a single method inside
a controller object. Controllers are also called *actions*.

.. code-block:: php
    :linenos:

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;

    class HelloController
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

.. tip::

    Note that the *controller* is the ``indexAction`` method, which lives
    inside a *controller class* (``HelloController``). Don't be confused
    by the naming: a *controller class* is simply a convenient way to group
    several controllers/actions together. Typically, the controller class
    will house several controllers/actions (e.g. ``updateAction``, ``deleteAction``,
    etc).

This controller is pretty straightforward:

* *line 4*: Symfony2 takes advantage of PHP 5.3 namespace functionality to
  namespace the entire controller class. The ``use`` keyword imports the
  ``Response`` class, which the controller must return.

* *line 6*: The class name is the concatenation of a name for the controller
  class (i.e. ``Hello``) and the word ``Controller``. This is a convention
  that provides consistency to controllers and allows them to be referenced
  only by the first part of the name (i.e. ``Hello``) in the routing configuration.

* *line 8*: Each action in a controller class is suffixed with ``Action``
  and is referenced in the routing configuration by the action's name (``index``).
  In the next section, you'll create a route that maps a URI to this action.
  You'll learn how the route's placeholders (``{name}``) become arguments
  to the action method (``$name``).

* *line 10*: The controller creates and returns a ``Response`` object.

.. index::
   single: Controller; Routes and controllers

Mapping a URL to a Controller
-----------------------------

The new controller returns a simple HTML page. To actually view this page
in your browser, you need to create a route, which maps a specific URL path
to the controller:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:      /hello/{name}
            defaults:  { _controller: AcmeHelloBundle:Hello:index }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello/{name}">
                <default key="_controller">AcmeHelloBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'AcmeHelloBundle:Hello:index',
        )));

Going to ``/hello/ryan`` now executes the ``HelloController::indexAction()``
controller and passes in ``ryan`` for the ``$name`` variable. Creating a
"page" means simply creating a controller method and associated route.

Notice the syntax used to refer to the controller: ``AcmeHelloBundle:Hello:index``.
Symfony2 uses a flexible string notation to refer to different controllers.
This is the most common syntax and tells Symfony2 to look for a controller
class called ``HelloController`` inside a bundle named ``AcmeHelloBundle``. The
method ``indexAction()`` is then executed.

For more details on the string format used to reference different controllers,
see :ref:`controller-string-syntax`.

.. note::

    This example places the routing configuration directly in the ``app/config/``
    directory. A better way to organize your routes is to place each route
    in the bundle it belongs to. For more information on this, see
    :ref:`routing-include-external-resources`.

.. tip::

    You can learn much more about the routing system in the :doc:`Routing chapter </book/routing>`.

.. index::
   single: Controller; Controller arguments

.. _route-parameters-controller-arguments:

Route Parameters as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You already know that the ``_controller`` parameter ``AcmeHelloBundle:Hello:index``
refers to a ``HelloController::indexAction()`` method that lives inside the
``AcmeHelloBundle`` bundle. What's more interesting is the arguments that are
passed to that method::

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
          // ...
        }
    }

The controller has a single argument, ``$name``, which corresponds to the
``{name}`` parameter from the matched route (``ryan`` in the example). In
fact, when executing your controller, Symfony2 matches each argument of
the controller with a parameter from the matched route. Take the following
example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:      /hello/{firstName}/{lastName}
            defaults:  { _controller: AcmeHelloBundle:Hello:index, color: green }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello/{firstName}/{lastName}">
                <default key="_controller">AcmeHelloBundle:Hello:index</default>
                <default key="color">green</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        $collection->add('hello', new Route('/hello/{firstName}/{lastName}', array(
            '_controller' => 'AcmeHelloBundle:Hello:index',
            'color'       => 'green',
        )));

The controller for this can take several arguments::

    public function indexAction($firstName, $lastName, $color)
    {
        // ...
    }

Notice that both placeholder variables (``{firstName}``, ``{lastName}``)
as well as the default ``color`` variable are available as arguments in the
controller. When a route is matched, the placeholder variables are merged
with the ``defaults`` to make one array that's available to your controller.

Mapping route parameters to controller arguments is easy and flexible. Keep
the following guidelines in mind while you develop.

* **The order of the controller arguments does not matter**

  Symfony is able to match the parameter names from the route to the variable
  names in the controller method's signature. In other words, it realizes that
  the ``{lastName}`` parameter matches up with the ``$lastName`` argument.
  The arguments of the controller could be totally reordered and still work
  perfectly::

      public function indexAction($lastName, $color, $firstName)
      {
          // ...
      }

* **Each required controller argument must match up with a routing parameter**

  The following would throw a ``RuntimeException`` because there is no ``foo``
  parameter defined in the route::

      public function indexAction($firstName, $lastName, $color, $foo)
      {
          // ...
      }

  Making the argument optional, however, is perfectly ok. The following
  example would not throw an exception::

      public function indexAction($firstName, $lastName, $color, $foo = 'bar')
      {
          // ...
      }

* **Not all routing parameters need to be arguments on your controller**

  If, for example, the ``lastName`` weren't important for your controller,
  you could omit it entirely::

      public function indexAction($firstName, $color)
      {
          // ...
      }

.. tip::

    Every route also has a special ``_route`` parameter, which is equal to
    the name of the route that was matched (e.g. ``hello``). Though not usually
    useful, this is equally available as a controller argument.

.. _book-controller-request-argument:

The ``Request`` as a Controller Argument
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For convenience, you can also have Symfony pass you the ``Request`` object
as an argument to your controller. This is especially convenient when you're
working with forms, for example::

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        $form = $this->createForm(...);

        $form->handleRequest($request);
        // ...
    }

.. index::
   single: Controller; Base controller class

Creating Static Pages
---------------------

You can create a static page without even creating a controller (only a route
and template are needed).

Use it! See :doc:`/cookbook/templating/render_without_controller`.

The Base Controller Class
-------------------------

For convenience, Symfony2 comes with a base ``Controller`` class that assists
with some of the most common controller tasks and gives your controller class
access to any resource it might need. By extending this ``Controller`` class,
you can take advantage of several helper methods.

Add the ``use`` statement atop the ``Controller`` class and then modify the
``HelloController`` to extend it::

    // src/Acme/HelloBundle/Controller/HelloController.php
    namespace Acme\HelloBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
            return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

This doesn't actually change anything about how your controller works. In
the next section, you'll learn about the helper methods that the base controller
class makes available. These methods are just shortcuts to using core Symfony2
functionality that's available to you with or without the use of the base
``Controller`` class. A great way to see the core functionality in action
is to look in the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class
itself.

.. tip::

    Extending the base class is *optional* in Symfony; it contains useful
    shortcuts but nothing mandatory. You can also extend
    :class:`Symfony\\Component\\DependencyInjection\\ContainerAware` or use
    the class:`Symfony\\Component\\DependencyInjection\\ContainerAwareTrait` trait
    (if you have PHP 5.4). The service container object will then be accessible
    via the ``container`` property.

.. versionadded:: 2.4
    The ``ContainerAwareTrait`` was introduced in Symfony 2.4.

.. note::

    You can also define your :doc:`Controllers as Services </cookbook/controller/service>`.
    This is optional, but can give you more control over the exact dependencies
    that are injected into your controllers.

.. index::
   single: Controller; Common tasks

Common Controller Tasks
-----------------------

Though a controller can do virtually anything, most controllers will perform
the same basic tasks over and over again. These tasks, such as redirecting,
forwarding, rendering templates and accessing core services, are very easy
to manage in Symfony2.

.. index::
   single: Controller; Redirecting

Redirecting
~~~~~~~~~~~

If you want to redirect the user to another page, use the ``redirect()`` method::

    public function indexAction()
    {
        return $this->redirect($this->generateUrl('homepage'));
    }

The ``generateUrl()`` method is just a helper function that generates the URL
for a given route. For more information, see the :doc:`Routing </book/routing>`
chapter.

By default, the ``redirect()`` method performs a 302 (temporary) redirect. To
perform a 301 (permanent) redirect, modify the second argument::

    public function indexAction()
    {
        return $this->redirect($this->generateUrl('homepage'), 301);
    }

.. tip::

    The ``redirect()`` method is simply a shortcut that creates a ``Response``
    object that specializes in redirecting the user. It's equivalent to::

        use Symfony\Component\HttpFoundation\RedirectResponse;

        return new RedirectResponse($this->generateUrl('homepage'));

.. index::
   single: Controller; Forwarding

Forwarding
~~~~~~~~~~

You can also easily forward to another controller internally with the
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::forward`
method. Instead of redirecting the user's browser, it makes an internal sub-request,
and calls the specified controller. The ``forward()`` method returns the ``Response``
object that's returned from that controller::

    public function indexAction($name)
    {
        $response = $this->forward('AcmeHelloBundle:Hello:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ));

        // ... further modify the response or return it directly

        return $response;
    }

Notice that the ``forward()`` method uses the same string representation of
the controller used in the routing configuration. In this case, the target
controller class will be ``HelloController`` inside some ``AcmeHelloBundle``.
The array passed to the method becomes the arguments on the resulting controller.
This same interface is used when embedding controllers into templates (see
:ref:`templating-embedding-controller`). The target controller method should
look something like the following::

    public function fancyAction($name, $color)
    {
        // ... create and return a Response object
    }

And just like when creating a controller for a route, the order of the arguments
to ``fancyAction`` doesn't matter. Symfony2 matches the index key names
(e.g. ``name``) with the method argument names (e.g. ``$name``). If you
change the order of the arguments, Symfony2 will still pass the correct
value to each variable.

.. tip::

    Like other base ``Controller`` methods, the ``forward`` method is just
    a shortcut for core Symfony2 functionality. A forward can be accomplished
    directly by duplicating the current request. When this
    :ref:`sub request <http-kernel-sub-requests>` is executed via the ``http_kernel``
    service the ``HttpKernel`` returns a ``Response`` object::

        use Symfony\Component\HttpKernel\HttpKernelInterface;

        $path = array(
            '_controller' => 'AcmeHelloBundle:Hello:fancy',
            'name'        => $name,
            'color'       => 'green',
        );
        $request = $this->container->get('request');
        $subRequest = $request->duplicate(array(), null, $path);

        $httpKernel = $this->container->get('http_kernel');
        $response = $httpKernel->handle(
            $subRequest,
            HttpKernelInterface::SUB_REQUEST
        );

.. index::
   single: Controller; Rendering templates

.. _controller-rendering-templates:

Rendering Templates
~~~~~~~~~~~~~~~~~~~

Though not a requirement, most controllers will ultimately render a template
that's responsible for generating the HTML (or other format) for the controller.
The ``renderView()`` method renders a template and returns its content. The
content from the template can be used to create a ``Response`` object::

    use Symfony\Component\HttpFoundation\Response;

    $content = $this->renderView(
        'AcmeHelloBundle:Hello:index.html.twig',
        array('name' => $name)
    );

    return new Response($content);

This can even be done in just one step with the ``render()`` method, which
returns a ``Response`` object containing the content from the template::

    return $this->render(
        'AcmeHelloBundle:Hello:index.html.twig',
        array('name' => $name)
    );

In both cases, the ``Resources/views/Hello/index.html.twig`` template inside
the ``AcmeHelloBundle`` will be rendered.

The Symfony templating engine is explained in great detail in the
:doc:`Templating </book/templating>` chapter.

.. tip::

    You can even avoid calling the ``render`` method by using the ``@Template``
    annotation. See the
    :doc:`FrameworkExtraBundle documentation </bundles/SensioFrameworkExtraBundle/annotations/view>`
    more details.

.. tip::

    The ``renderView`` method is a shortcut to direct use of the ``templating``
    service. The ``templating`` service can also be used directly::

        $templating = $this->get('templating');
        $content = $templating->render(
            'AcmeHelloBundle:Hello:index.html.twig',
            array('name' => $name)
        );

.. note::

    It is possible to render templates in deeper subdirectories as well, however
    be careful to avoid the pitfall of making your directory structure unduly
    elaborate::

        $templating->render(
            'AcmeHelloBundle:Hello/Greetings:index.html.twig',
            array('name' => $name)
        );
        // index.html.twig found in Resources/views/Hello/Greetings
        // is rendered.

.. index::
   single: Controller; Accessing services

Accessing other Services
~~~~~~~~~~~~~~~~~~~~~~~~

When extending the base controller class, you can access any Symfony2 service
via the ``get()`` method. Here are several common services you might need::

    $templating = $this->get('templating');

    $router = $this->get('router');

    $mailer = $this->get('mailer');

There are countless other services available and you are encouraged to define
your own. To list all available services, use the ``container:debug`` console
command:

.. code-block:: bash

    $ php app/console container:debug

For more information, see the :doc:`/book/service_container` chapter.

.. index::
   single: Controller; Managing errors
   single: Controller; 404 pages

Managing Errors and 404 Pages
-----------------------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. To do this, you'll throw a special type of exception.
If you're extending the base controller class, do the following::

    public function indexAction()
    {
        // retrieve the object from database
        $product = ...;
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        return $this->render(...);
    }

The ``createNotFoundException()`` method creates a special ``NotFoundHttpException``
object, which ultimately triggers a 404 HTTP response inside Symfony.

Of course, you're free to throw any ``Exception`` class in your controller -
Symfony2 will automatically return a 500 HTTP response code.

.. code-block:: php

    throw new \Exception('Something went wrong!');

In every case, a styled error page is shown to the end user and a full debug
error page is shown to the developer (when viewing the page in debug mode).
Both of these error pages can be customized. For details, read the
":doc:`/cookbook/controller/error_pages`" cookbook recipe.

.. index::
   single: Controller; The session
   single: Session

Managing the Session
--------------------

Symfony2 provides a nice session object that you can use to store information
about the user (be it a real person using a browser, a bot, or a web service)
between requests. By default, Symfony2 stores the attributes in a cookie
by using the native PHP sessions.

Storing and retrieving information from the session can be easily achieved
from any controller::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $session = $request->getSession();

        // store an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // get the attribute set by another controller in another request
        $foobar = $session->get('foobar');

        // use a default value if the attribute doesn't exist
        $filters = $session->get('filters', array());
    }

These attributes will remain on the user for the remainder of that user's
session.

.. index::
   single: Session; Flash messages

Flash Messages
~~~~~~~~~~~~~~

You can also store small messages that will be stored on the user's session
for exactly one additional request. This is useful when processing a form:
you want to redirect and have a special message shown on the *next* request.
These types of messages are called "flash" messages.

For example, imagine you're processing a form submit::

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        $form = $this->createForm(...);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // do some sort of processing

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirect($this->generateUrl(...));
        }

        return $this->render(...);
    }

After processing the request, the controller sets a ``notice`` flash message
and then redirects. The name (``notice``) isn't significant - it's just what
you're using to identify the type of the message.

In the template of the next action, the following code could be used to render
the ``notice`` message:

.. configuration-block::

    .. code-block:: html+jinja

        {% for flashMessage in app.session.flashbag.get('notice') %}
            <div class="flash-notice">
                {{ flashMessage }}
            </div>
        {% endfor %}

    .. code-block:: html+php

        <?php foreach ($view['session']->getFlash('notice') as $message): ?>
            <div class="flash-notice">
                <?php echo "<div class='flash-error'>$message</div>" ?>
            </div>
        <?php endforeach; ?>

By design, flash messages are meant to live for exactly one request (they're
"gone in a flash"). They're designed to be used across redirects exactly as
you've done in this example.

.. index::
   single: Controller; Response object

The Response Object
-------------------

The only requirement for a controller is to return a ``Response`` object. The
:class:`Symfony\\Component\\HttpFoundation\\Response` class is a PHP
abstraction around the HTTP response - the text-based message filled with HTTP
headers and content that's sent back to the client::

    use Symfony\Component\HttpFoundation\Response;

    // create a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // create a JSON-response with a 200 status code
    $response = new Response(json_encode(array('name' => $name)));
    $response->headers->set('Content-Type', 'application/json');

.. versionadded:: 2.4
    Support for HTTP status code constants was introduced in Symfony 2.4.

.. tip::

    The ``headers`` property is a
    :class:`Symfony\\Component\\HttpFoundation\\HeaderBag` object with several
    useful methods for reading and mutating the ``Response`` headers. The
    header names are normalized so that using ``Content-Type`` is equivalent
    to ``content-type`` or even ``content_type``.

.. tip::

    There are also special classes to make certain kinds of responses easier:

    - For JSON, there is :class:`Symfony\\Component\\HttpFoundation\\JsonResponse`.
      See :ref:`component-http-foundation-json-response`.
    - For files, there is :class:`Symfony\\Component\\HttpFoundation\\BinaryFileResponse`.
      See :ref:`component-http-foundation-serving-files`.

.. index::
   single: Controller; Request object

The Request Object
------------------

Besides the values of the routing placeholders, the controller also has access
to the ``Request`` object. The framework injects the ``Request`` object in the
controller if a variable is type-hinted with
:class:`Symfony\\Component\\HttpFoundation\\Request`::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $request->isXmlHttpRequest(); // is it an Ajax request?

        $request->getPreferredLanguage(array('en', 'fr'));

        $request->query->get('page'); // get a $_GET parameter

        $request->request->get('page'); // get a $_POST parameter
    }

Like the ``Response`` object, the request headers are stored in a ``HeaderBag``
object and are easily accessible.

Final Thoughts
--------------

Whenever you create a page, you'll ultimately need to write some code that
contains the logic for that page. In Symfony, this is called a controller,
and it's a PHP function that can do anything it needs in order to return
the final ``Response`` object that will be returned to the user.

To make life easier, you can choose to extend a base ``Controller`` class,
which contains shortcut methods for many common controller tasks. For example,
since you don't want to put HTML code in your controller, you can use
the ``render()`` method to render and return the content from a template.

In other chapters, you'll see how the controller can be used to persist and
fetch objects from a database, process form submissions, handle caching and
more.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/controller/error_pages`
* :doc:`/cookbook/controller/service`
