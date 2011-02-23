.. index::
   single: Controller

The Controller
==============

A controller is a PHP function you create that takes information from the
HTTP request and constructs and returns an HTTP response (as a Symfony2
``Response`` object). The response could be the an HTML page, an XML document,
a serialized JSON array, an image, a redirect, a 404 error or anything else
you can dream up. The controller contains whatever arbitrary logic *your
application* needs to create that response.

Along the way, your controller might read information from the request, load
a database resource, send an email, or set information on the user's session.
But in all cases, the controller's final job is to return the ``Response``
object that will be delivered back to the client. There's no magic and no
other requirements to worry about. Here are a few common examples:

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

Every request handled by a Symfony2 project goes through the same basic lifecycle.
The framework takes care of the repetitive tasks and ultimately executes a
controller, which houses your custom application code:

* Each request is handled by a single front controller file (e.g. ``app.php``
  or ``index.php``) that's responsible for bootstrapping the framework;

* The ``Router`` reads the request information, matches that information to
  a specific route, and determines from that route which controller should
  be called;

* The controller is executed and the code inside the controller creates and
  returns a ``Response`` object;

* The HTTP headers and content of the ``Response`` object are sent back to
  the client.

Creating a page is as easy as creating a controller and making a route that
maps a URI to that controller.

.. note::

    Though similarly named, a "front controller" is different from the
    "controllers" we'll talk about in this guide. A front controller
    is a short PHP file that lives in your web directory and through which
    all requests are directed. A typical application will have a production
    front controller (e.g. ``app.php``) and a development front controller
    (e.g. ``app_dev.php``). You'll likely never need to edit, view or worry
    about the front controllers in your application.

.. index::
   single: Controller; Simple example

A Simple Controller
-------------------

The controller is a PHP callable responsible for returning a representation
of the resource (most of the time an HTML representation). Though a controller
can be any PHP callable (a function, a method on an object, or a ``Closure``),
in Symfony2, a controller is usually a single method inside a controller
object. Controllers are also called *actions*.

.. code-block::php

    // src/Sensio/HelloBundle/Controller/HelloController.php

    namespace Sensio\HelloBundle\Controller;
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
    several controllers together. Typically, the controller class will house
    several controllers (e.g. ``updateAction``, ``deleteAction``, etc). A
    controller is also sometimes referred to as an *action*.

This controller is pretty straightforward, but let's walk through it:

* *line 3*: Symfony2 takes advantage of PHP 5.3 namespace functionality to
  namespace the entire controller class. The ``use`` keyword imports the
  ``Response`` class, which our controller must return.

* *line 6*: The class name is the concatenation of a name for the controller
  class and the word ``Controller``. This is a convention that provides consistency
  to controllers and allows them to be referenced only by the first part of
  the name (i.e. ``Hello``) in the routing configuration.

* *line 8*: Each action in a controller class is suffixed with ``Action``
  and is referenced in the routing configuration by the action's name (``index``).
  In the next section, we'll use a route to map a URI to this action and
  show how the route's placeholders (``{name}``) become arguments on the
  action (``$name``).

* *line 10*: The controller creates and returns a ``Response`` object.

.. index::
   single: Controller; Routes and controllers

Mapping a URI to a Controller
-----------------------------

Our new controller returns a simple HTML page. To render this controller
at a specific URL, we need to create a route to it.

We'll talk about the ``Routing`` component in detail in the :doc:`Routing guide</guides/routing>`,
but let's create a simple route to our controller:

.. configuration-block::

    .. code-block:: yaml

        # src/Sensio/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:      /hello/{name}
            defaults:     { _controller: HelloBundle:Hello:index }

    .. code-block:: xml

        <!-- src/Sensio/HelloBundle/Resources/config/routing.xml -->
        <route id="hello" pattern="/hello/{name}">
            <default key="_controller">HelloBundle:Hello:index</default>
        </route>

    .. code-block:: php

        // src/Sensio/HelloBundle/Resources/config/routing.php
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'HelloBundle:Hello:index',
        )));

Going to ``/hello/ryan`` now executes the ``HelloController::indexAction()``
controller and passes in ``ryan`` for the ``$name`` variable. Creating a
"page" means simply creating a controller method and associated route. There's
no hidden layers or behind-the-scenes magic.

Notice the syntax used to refer to the controller: ``HelloBundle:Hello:index``.
Symfony2 uses a flexible string notation to refer to different controllers.
This is the most common syntax and tells Symfony2 to look for a controller
class called ``HelloController`` inside a bundle named ``HelloBundle``. The
method ``indexAction()`` is then executed.

For more details on the string format used to reference different controllers,
see :ref:`controller-string-syntax`.

.. tip::

    Notice that since our controller lives in the ``HelloBundle``, we've
    placed the routing configuration inside the ``HelloBundle`` to stay
    organized. To load routing configuration that lives inside a bundle, it
    must be imported from your application's main routing resource. See
    :ref:`routing-include-external-resources` for more information.

.. index::
   single: Controllers; Route parameters as controller arguments

.. route-parameters-controller-arguments:

Route Parameters as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

We already know now that the ``_controller`` parameter ``HelloBundle:Hello:index``
refers to a ``HelloController::indexAction()`` method that lives inside the
``HelloBundle`` bundle. What's more interesting is the arguments that are
passed to that method:

.. code-block:: php

    <?php
    // src/Sensio/HelloBundle/Controller/HelloController.php

    namespace Sensio\HelloBundle\Controller;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
          // ...
        }
    }

The controller has a single argument, ``$name``, which corresponds to the
``{name}`` parameter from the matched route (``ryan`` in our example). In
fact, when executing your controller, Symfony2 matches each argument of
the controller with a parameter from the matched route. Take the following
example::

.. configuration-block::

    .. code-block:: yaml

        # src/Sensio/HelloBundle/Resources/config/routing.yml
        hello:
            pattern:      /hello/{first_name}/{last_name}
            defaults:     { _controller: HelloBundle:Hello:index, color: green }

    .. code-block:: xml

        <!-- src/Sensio/HelloBundle/Resources/config/routing.xml -->
        <route id="hello" pattern="/hello/{first_name}/{last_name}">
            <default key="_controller">HelloBundle:Hello:index</default>
            <default key="color">green</default>
        </route>

    .. code-block:: php

        // src/Sensio/HelloBundle/Resources/config/routing.php
        $collection->add('hello', new Route('/hello/{first_name}/{last_name}', array(
            '_controller' => 'HelloBundle:Hello:index',
            'color'       => 'green',
        )));

The controller for this can take several arguments::

    public function indexAction($first_name, $last_name, $color)
    {
        // ...
    }

Notice that both placeholder variables (``{{first_name}}``, ``{{last_name}}``)
as well as the default ``color`` variable are available as arguments in the
controller. When a route is matched, the placeholder variables are merged
with the ``defaults`` to make one array that's available to your controller.

Mapping route parameters to controller arguments is easy and flexible. Keep
the following guidelines in mind while you develop.

The order of the controller arguments does not matter.
......................................................

Symfony2 is able to matches the parameter names from the route to the variable
names in the controller method's signature. In other words, it realizes that
the ``last_name`` parameter matches up with the ``$last_name`` argument.
The arguments of the controller could be totally reordered and still work
perfectly::

    public function indexAction($last_name, $color, $first_name)
    {
        // ..
    }

Each required controller argument must match up with a routing parameter.
.........................................................................

The following would throw a ``RuntimeException`` because there is no ``foo``
parameter defined in the route::

    public function indexAction($first_name, $last_name, $color, $foo)
    {
        // ..
    }

Making the argument optional, however, is perfectly ok. The following
example would not throw an exception::

    public function indexAction($first_name, $last_name, $color, $foo = 'bar')
    {
        // ..
    }

Not all routing parameters need to be arguments on your controller.
...................................................................

If, for example, the ``last_name`` weren't important for your controller,
you could omit it entirely::

    public function indexAction($first_name, $color)
    {
        // ..
    }

In fact, the ``_controller`` route parameter itself is technically available
as a controller argument since it's in the ``defaults`` of the route. Of
course, it's generally not very useful, so it's omitted from our controller.

.. tip::
    Every route also has a special ``_route`` parameter, which is equal to
    the name of the route that was matched (e.g. ``hello``). Though not usually
    useful, this is equally available as a controller argument.

The Base Controller Class
-------------------------

For convenience, Symfony2 comes with a base ``Controller`` class that assists
with some of the most common controller tasks and gives your controller class
access to any resource it might need. By extending this ``Controller`` class,
you can take advantage of several helper methods.

Add the ``use`` statement atop the ``Controller`` class and then modify the
``HelloController`` to extend it. That's all there is to it.

.. code-block::php

    // src/Sensio/HelloBundle/Controller/HelloController.php

    namespace Application\HelloBundle\Controller;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    class HelloController extends Controller
    {
        public function indexAction($name)
        {
          return new Response('<html><body>Hello '.$name.'!</body></html>');
        }
    }

So far, extending the base ``Controller`` class hasn't changed anything.
In the next section, we'll walk through several helper methods that the base
controller class makes available. These methods are just shortcuts to using
core Symfony2 functionality that's available to you with or without the use
of the base ``Controller`` class. A great way to see the core functionality
in action is to look in the :class:`Symfony\Bundle\FrameworkBundle\Controller\Controller`
class itself.

.. note::

    Extending the base class is a *choice* in Symfony. A much more robust
    and recommended approach is to treat your controllers as services. See
    the `Controllers as Services`_ section for more information.

.. index::
   single: Controller; Common Tasks

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

If you want to redirect the user to another page, use a special ``RedirectResponse``
class, which is designed specifically to redirect the user to another URL::

    // ...
    use Symfony\Component\HttpFoundation\RedirectResponse;

    class HelloController extends Controller
    {
      public function indexAction()
      {
          return new RedirectResponse($this->generateUrl('hello', array('name' => 'Lucas')));
      }
    }

The ``generateUrl()`` method is just a shortcut that calls ``generate()``
on the ``router`` service. It takes the route name and an array of parameters
as arguments and returns the associated friendly URL. See the :doc:`Routing </guides/routing>`
guide for more information.

By default, the ``redirect`` method does a 302 (temporary) redirect. To perform
a 301 (permanent) redirect, modify the second argument::

    public function indexAction()
    {
        return new RedirectResponse($this->generateUrl('hello', array('name' => 'Lucas')), 301);
    }

.. index::
   single: Controller; Forwarding

Forwarding
~~~~~~~~~~

You can also easily forward to another action internally with the ``forward()``
method. Instead of redirecting the user's browser, it makes an internal sub-request,
and calls the specified controller. The ``forward()`` method returns the ``Response``
object to allow for further modification if the need arises. That ``Response``
object is the end-product of the internal sub-request::

    public function indexAction($name)
    {
        $response = $this->forward('HelloBundle:Hello:fancy', array(
            'name'  => $name,
            'color' => 'green'
        ));

        // further modify the response or return it directly
        
        return $response;
    }

Notice that the `forward()` method uses the same string representation of
the controller used in the routing configuration. The array passed to the
method becomes the arguments on the resulting controller. This same interface
is used when embedding controllers into templates (see :ref:`templating-embedding-controller`).
The target controller method should look something like the following::

    public function fancyAction($name, $green)
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
    directly via the ``http_kernel`` service. A forward returns a ``Response``
    object::
    
        $httpKernel = $this->container->get('http_kernel');
        $response = $httpKernel->forward('HelloBundle:Hello:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ));

.. index::
   single:: Controller; Rendering templates

Rendering Templates
~~~~~~~~~~~~~~~~~~~

Though not a requirement, most controllers will ultimately render a template
that's responsible for generating the HTML (or other format) for the controller.
The ``renderView()`` method renders a template and returns its content. The
content from the template can be used to create a ``Response`` object::

    $content = $this->renderView('HelloBundle:Hello:index.html.twig', array('name' => $name));

    return new Response($content);

This can even be done in just one step with the ``render()`` method, which
returns a ``Response`` object with the content from the template::

    return $this->render('HelloBundle:Hello:index.html.twig', array('name' => $name));

The Symfony templating engine is explained in great detail in the :doc:`Templating </guides/templating>`
guide.

.. tip::

    The ``renderView`` method is a shortcut to direct use of the ``templating``
    service. The ``templating`` service can also be used directly::
    
        $templating = $this->get('templating');
        $content = $templating->render('HelloBundle:Hello:index.html.twig', array('name' => $name));

.. index::
   single: Controller; Accessing services

Accessing other Services
~~~~~~~~~~~~~~~~~~~~~~~~

When extending the base controller class, you can access any Symfony2 service
via the ``get()`` method. Here are several common services you might need::

    $request = $this->get('request');

    $response = $this->get('response');

    $templating = $this->get('templating');

    $router = $this->get('router');

    $mailer = $this->get('mailer');

The are countless other services available and you are encouraged to define
your own. For more information, see the :doc:`Extending Symfony </guides/extending_symfony>`
guide.

.. index::
   single: Controller; Managing errors

Managing Errors
---------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. This is easily done by throwing a built-in HTTP
exception:

.. code-block:: php

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    public function indexAction()
    {
        $product = // retrieve the object from database
        if (!$product) {
            throw new NotFoundHttpException('The product does not exist.');
        }

        return $this->render(...);
    }

The ``NotFoundHttpException`` will return a 404 HTTP response back to the
browser. When viewing a page in debug mode, a full exception with stacktrace
is displayed so that the cause of the exception can be easily tracked down.

Of course, you're free to throw any ``Exception`` class in your controller
- Symfony2 will automatically return a 500 HTTP response code.

.. code-block::php

    throw new \Exception('Something went wrong!');

In every case, a styled error page is shown to the end user and a full debug
error page is shown to the developer (when viewing the page in debug mode).
Both of these error pages can be customized.

The Nuts and Bolts of Error Handling
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When any exception is thrown in Symfony2, the exception is caught inside
the ``Kernel`` class and eventually forwarded to a special controller,
``FrameworkBundle:Exception:show`` for handling. This controller, which lives
inside the core ``FrameworkBundle``, determines which error template to display
and the status code that should be set for the given exception.

.. tip::

    The customization of exception handling is actually much more powerful
    than what's written here. An internal event, ``core.exception``, is thrown
    which allows complete control over exception handling. For more information,
    see ``events-core.exception``.

Customizing Error Pages
~~~~~~~~~~~~~~~~~~~~~~~

Whenever an ``Exception`` of any kind is thrown in a controller, Symfony
executes an internal controller responsible for rendering an error page (to
the end user) or a helpful exception page (to the developer). Both pages
are Twig template and can be easily overridden.

.. note::

    All of the error templates live inside the core ``FrameworkBundle``. To
    override the templates, we simply rely on the standard method for overriding
    templates that live inside a bundle. For more information, see :ref:`overiding-bundle-templates`.

For example, to override the default error template that's shown to the end-user,
create a new template located at ``app/views/FrameworkBundle/Exception/error.html.twig``::

    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
            <h1>Oops! An Error Occurred</h1>
            <h2>The server returned a "{{ exception.statuscode }} {{ exception.statustext }}".</h2>
        </body>
    </html>

.. tip::

    If you're not familiar with Twig, don't worry. Twig is simple, powerful
    and optional templating engine that integrates with ``Symfony2``.

In addition to the standard HTML error page, Symfony provides a default error
page for the many of the most common response formats, including JSON (``error.json.twig``),
XML, (``error.xml.twig``), and even Javascript (``error.js.twig``), to name
a few. To override any of these templates, just create a new file with the
same name in the ``app/views/FrameworkBundle/Exception`` directory. This
is the standard way of overriding any template that lives inside a bundle.

The debug-friendly exception pages shown to the developer can even be customized
in the same way by creating templates such as ``exception.html.twig`` for
the standard HTML exception page or ``exception.json.twig`` for the JSON
exception page.

.. tip::

    To see the full list of default error templates, see the ``Resources/views/Exception``
    directory of the ``FrameworkBundle``. In a standard Symfony2 installation,
    the ``FrameworkBundle`` can be found at ``src/vendor/symfony/src/Symfony/Bundle/FrameworkBundle``.
    Often, the easiest way to customize an error page is to copy it from
    the ``FrameworkBundle`` into ``app/views/FrameworkBundle/Exception``
    and then modify it.

.. index::
   single: Controller; The session
   single: Session

Managing the Session
--------------------

Even if the HTTP protocol is stateless, Symfony2 provides a nice session object
that represents the client (be it a real person using a browser, a bot, or a
web service). Between two requests, Symfony2 stores the attributes in a cookie
by using the native PHP sessions.

Storing and retrieving information from the session can be easily achieved
from any controller::

    $session = $this->get('request')->getSession();

    // store an attribute for reuse during a later user request
    $session->set('foo', 'bar');

    // in another controller for another request
    $foo = $session->get('foo');

    // set the user locale
    $session->setLocale('fr');

These attributes will remain on the user for the remainder of that user's
session.

.. index::
   single Session; Flash messages

Flash Messages
~~~~~~~~~~~~~~

You can also store small messages that will be stored on the user's session
for exactly one additional request. This is useful when processing a form:
you want to redirect and have a special message shown on the *next* request.
These types of messages are called "flash" messages.

Let's show an example where we're processing a form submit::

    public function updateAction()
    {
        if ('POST' === $this->get('request')->getMethod()) {
            // do some sort of processing
            
            $this->get('session')->setFlash('notice', 'Your changes were saved!');

            return $this->redirect($this->generateUrl(...));
        }
        
        return $this->render(...);
    }

After processing the request, the controller sets a ``notice`` flash message
and then redirects. In the template of the next action, the following code
could be used to render the messag::

.. configuration-block::

    .. code-block:: html+jinja

        {% if app.session.hasFlash('notice') %}
            <div class="flash-notice">
                {{ app.session.flash('notice') }}
            </div>
        {% endif %}

    .. code-block:: php
    
        <?php if ($view['session']->hasFlash('notice') ?>
            <div class="flash-notice">
                <?php echo $view['session']->getFlash('notice') ?>
            </div>
        <?php endif; ?>

By design, flash messages are meant to only live for exactly one request
(they're "gone in a flash"). They're designed to be used across redirects
exactly as we've done in this example.

.. index::
   single: Controller; Response

The Response Object
-------------------

The only requirement for a controller is to return a ``Response`` object.
The :class:`Symfony\Component\HttpFoundation\Response` class is a PHP abstraction
around the HTTP response - the text-based message filled with HTTP headers
and content that's sent back to the client::

    // create a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, 200);
    
    // create a JSON-response with a 200 status code
    $response = new Response(json_encode(array('name' => $name)));
    $response->headers->set('Content-Type', 'application/json');

.. tip::

    The ``headers`` property is a :class:`Symfony\Component\HttpFoundation\HeaderBag`
    object with several useful methods for reading and mutating the ``Response``
    headers. The header names are normalized so that using ``Content-Type``
    is equivalent to ``content-type`` or even ``content_type``.

.. index::
   single: Controller; Request

The Request Object
------------------

Besides the values of the routing placeholders, the controller also has access
to the ``Request`` object when extending the base ``Controller`` class::

    $request = $this->get('request');

    $request->isXmlHttpRequest(); // is it an Ajax request?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->query->get('page'); // get a $_GET parameter

    $request->request->get('page'); // get a $_POST parameter

Like the ``Response`` object, the request headers are stored in a ``HeaderBag``
object and are easily accessible.

.. index::
   single: Controller; As Services

Controllers as Services
-----------------------

So far, we've shown how easily a controller can be used when it extends
the base ``Symfony\Bundle\FrameworkBundle\Controller\Controller`` class.
While this this works fine, controllers can also be specified as services.

To refer to a controller that's defined as a service, use the single colon
(:) notation. For example, suppose we've defined a service called ``my_controller``
and we want to forward to a method called ``indexAction()`` inside the service::

    $this->forward('my_controller:indexAction', array('foo' => $bar));

To use a controller in this way, it must be defined in the service container
configuration. For more information, see the :doc:`Extending Symfony </guides/extending_symfony>`
guide.

When using a controller defined as a service, it will most likely not extend
the base ``Controller`` class. Instead of relying on its shortcut methods,
you'll interact directly with the services that you need. Fortunately, this
is usually pretty easy and the base ``Controller`` class itself is a great
source on how to perform many common tasks.

.. note::

    Specifying a controller as a service takes a little bit more work.
    The primary advantage is that the entire controller or any services
    passed to the controller can be modified via the service container configuration.
    This is especially common and important when developing an open-source
    bundle or any bundle that will be used in many different projects. So,
    even if you don't specify your controllers as services, you'll likely
    see this done in many popular open-source Symfony2 bundles.

.. index::
   single: Controller; Overview

Overview
--------

In Symfony, a controller is nothing more than a PHP function that contains
whatever arbitrary logic is needed to create and return a ``Response`` object.
The controller allows us to have an application with many pages while keeping
the logic for each page organized into different controller classes and action
methods.

Symfony2 decides which controller should handle each request by matching
a route and resolving the string format of its ``_controller`` parameter
to a real Symfony2 controller. The arguments on that controller correspond
to the parameters on the route, allowing your controller access to the information
form the request.

The controller can do anything and contain any logic, as long as it returns
a ``Response`` object. If you extend the base ``Controller`` class, you
instantly have access to all of the Symfony2 core service objects as well
as shortcut methods to performing the most common tasks.