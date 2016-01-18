.. index::
   single: Controller

Controller
==========

A controller is a PHP callable you create that takes information from the
HTTP request and creates and returns an HTTP response (as a Symfony
``Response`` object). The response could be an HTML page, an XML document,
a serialized JSON array, an image, a redirect, a 404 error or anything else
you can dream up. The controller contains whatever arbitrary logic *your
application* needs to render the content of a page.

See how simple this is by looking at a Symfony controller in action.
This renders a page that prints the famous ``Hello world!``::

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
  blog entry from the database and creates a ``Response`` object displaying
  that blog. If the ``slug`` can't be found in the database, it creates and
  returns a ``Response`` object with a 404 status code.

* *Controller C* handles the form submission of a contact form. It reads
  the form information from the request, saves the contact information to
  the database and emails the contact information to you. Finally, it creates
  a ``Response`` object that redirects the client's browser to the contact
  form "thank you" page.

.. index::
   single: Controller; Request-controller-response lifecycle

Requests, Controller, Response Lifecycle
----------------------------------------

Every request handled by a Symfony project goes through the same simple lifecycle.
The framework takes care of all the repetitive stuff: you just need to write
your custom code in the controller function:

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
or a ``Closure``), a controller is usually a method inside a controller class.
Controllers are also called *actions*.

.. code-block:: php

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

.. tip::

    Note that the *controller* is the ``indexAction`` method, which lives
    inside a *controller class* (``HelloController``). Don't be confused
    by the naming: a *controller class* is simply a convenient way to group
    several controllers/actions together. Typically, the controller class
    will house several controllers/actions (e.g. ``updateAction``, ``deleteAction``,
    etc).

This controller is pretty straightforward:

* *line 2*: Symfony takes advantage of PHP's namespace functionality to
  namespace the entire controller class.

* *line 4*: Symfony again takes advantage of PHP's namespace functionality: the ``use`` keyword imports the
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

    .. code-block:: php-annotations

        // src/AppBundle/Controller/HelloController.php
        namespace AppBundle\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class HelloController
        {
            /**
             * @Route("/hello/{name}", name="hello")
             */
            public function indexAction($name)
            {
                return new Response('<html><body>Hello '.$name.'!</body></html>');
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:      /hello/{name}
            # uses a special syntax to point to the controller - see note below
            defaults:  { _controller: AppBundle:Hello:index }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello/{name}">
                <!-- uses a special syntax to point to the controller - see note below -->
                <default key="_controller">AppBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\Route;
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{name}', array(
            // uses a special syntax to point to the controller - see note below
            '_controller' => 'AppBundle:Hello:index',
        )));

        return $collection;

Now, you can go to ``/hello/ryan`` (e.g. ``http://localhost:8000/hello/ryan``
if you're using the :doc:`built-in web server </cookbook/web_server/built_in>`)
and Symfony will execute the ``HelloController::indexAction()`` controller
and pass in ``ryan`` for the ``$name`` variable. Creating a "page" means
simply creating a controller method and an associated route.

Simple, right?

.. sidebar:: The AppBundle:Hello:index controller syntax

    If you use the YML or XML formats, you'll refer to the controller using
    a special shortcut syntax: ``AppBundle:Hello:index``. For more details
    on the controller format, see :ref:`controller-string-syntax`.

.. seealso::

    You can learn much more about the routing system in the
    :doc:`Routing chapter </book/routing>`.

.. index::
   single: Controller; Controller arguments

.. _route-parameters-controller-arguments:

Route Parameters as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You already know that the route points to the
``HelloController::indexAction()`` method that lives inside AppBundle. What's
more interesting is the argument that is passed to that method::

    // src/AppBundle/Controller/HelloController.php
    // ...
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    /**
     * @Route("/hello/{name}", name="hello")
     */
    public function indexAction($name)
    {
        // ...
    }

The controller has a single argument, ``$name``, which corresponds to the
``{name}`` parameter from the matched route (``ryan`` if you go to ``/hello/ryan``).
When executing your controller, Symfony matches each argument with a parameter
from the route. So the value for ``{name}`` is passed to ``$name``.

Take the following more-interesting example:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/HelloController.php
        // ...

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class HelloController
        {
            /**
             * @Route("/hello/{firstName}/{lastName}", name="hello")
             */
            public function indexAction($firstName, $lastName)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:      /hello/{firstName}/{lastName}
            defaults:  { _controller: AppBundle:Hello:index }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="hello" path="/hello/{firstName}/{lastName}">
                <default key="_controller">AppBundle:Hello:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\Route;
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->add('hello', new Route('/hello/{firstName}/{lastName}', array(
            '_controller' => 'AppBundle:Hello:index',
        )));

        return $collection;

Now, the controller can have two arguments::

    public function indexAction($firstName, $lastName)
    {
        // ...
    }

Mapping route parameters to controller arguments is easy and flexible. Keep
the following guidelines in mind while you develop.

* **The order of the controller arguments does not matter**

  Symfony matches the parameter **names** from the route to the variable
  **names** of the controller. The arguments of the controller could be totally
  reordered and still work perfectly::

      public function indexAction($lastName, $firstName)
      {
          // ...
      }

* **Each required controller argument must match up with a routing parameter**

  The following would throw a ``RuntimeException`` because there is no ``foo``
  parameter defined in the route::

      public function indexAction($firstName, $lastName, $foo)
      {
          // ...
      }

  Making the argument optional, however, is perfectly ok. The following
  example would not throw an exception::

      public function indexAction($firstName, $lastName, $foo = 'bar')
      {
          // ...
      }

* **Not all routing parameters need to be arguments on your controller**

  If, for example, the ``lastName`` weren't important for your controller,
  you could omit it entirely::

      public function indexAction($firstName)
      {
          // ...
      }

.. tip::

    Every route also has a special ``_route`` parameter, which is equal to
    the name of the route that was matched (e.g. ``hello``). Though not usually
    useful, this is also available as a controller argument. You can also
    pass other variables from your route to your controller arguments. See
    :doc:`/cookbook/routing/extra_information`.

.. _book-controller-request-argument:

The ``Request`` as a Controller Argument
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What if you need to read query parameters, grab a request header or get access
to an uploaded file? All of that information is stored in Symfony's ``Request``
object. To get it in your controller, just add it as an argument and
**type-hint it with the Request class**::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction($firstName, $lastName, Request $request)
    {
        $page = $request->query->get('page', 1);

        // ...
    }

.. seealso::

    Want to know more about getting information from the request? See
    :ref:`Access Request Information <component-http-foundation-request>`.

.. index::
   single: Controller; Base controller class

The Base Controller Class
-------------------------

For convenience, Symfony comes with an optional base ``Controller`` class.
If you extend it, you'll get access to a number of helper methods and all
of your service objects via the container (see :ref:`controller-accessing-services`).

Add the ``use`` statement atop the ``Controller`` class and then modify the
``HelloController`` to extend it::

    // src/AppBundle/Controller/HelloController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class HelloController extends Controller
    {
        // ...
    }

This doesn't actually change anything about how your controller works: it
just gives you access to helper methods that the base controller class makes
available. These are just shortcuts to using core Symfony functionality that's
available to you with or without the use of the base ``Controller`` class.
A great way to see the core functionality in action is to look in the
`Controller class`_.

.. seealso::

    If you're curious about how a controller would work that did *not* extend
    this base class, check out :doc:`Controllers as Services </cookbook/controller/service>`.
    This is optional, but can give you more control over the exact objects/dependencies
    that are injected into your controller.

.. index::
   single: Controller; Redirecting

Redirecting
~~~~~~~~~~~

If you want to redirect the user to another page, use the ``redirectToRoute()`` method::

    public function indexAction()
    {
        return $this->redirectToRoute('homepage');

        // redirectToRoute is equivalent to using redirect() and generateUrl() together:
        // return $this->redirect($this->generateUrl('homepage'), 301);
    }

Or, if you want to redirect externally, just use ``redirect()`` and pass it the URL::

    public function indexAction()
    {
        return $this->redirect('http://symfony.com/doc');
    }

By default, the ``redirectToRoute()`` method performs a 302 (temporary) redirect. To
perform a 301 (permanent) redirect, modify the third argument::

    public function indexAction()
    {
        return $this->redirectToRoute('homepage', array(), 301);
    }

.. tip::

    The ``redirectToRoute()`` method is simply a shortcut that creates a
    ``Response`` object that specializes in redirecting the user. It's
    equivalent to::

        use Symfony\Component\HttpFoundation\RedirectResponse;

        public function indexAction()
        {
            return new RedirectResponse($this->generateUrl('homepage'));
        }

.. index::
   single: Controller; Rendering templates

.. _controller-rendering-templates:

Rendering Templates
~~~~~~~~~~~~~~~~~~~

If you're serving HTML, you'll want to render a template. The ``render()``
method renders a template **and** puts that content into a ``Response``
object for you::

    // renders app/Resources/views/hello/index.html.twig
    return $this->render('hello/index.html.twig', array('name' => $name));

You can also put templates in deeper sub-directories. Just try to avoid creating
unnecessarily deep structures::

    // renders app/Resources/views/hello/greetings/index.html.twig
    return $this->render('hello/greetings/index.html.twig', array(
        'name' => $name
    ));

The Symfony templating engine is explained in great detail in the
:doc:`Templating </book/templating>` chapter.

.. sidebar:: Referencing Templates that Live inside the Bundle

    You can also put templates in the ``Resources/views`` directory of a
    bundle and reference them with a
    ``BundleName:DirectoryName:FileName`` syntax. For example,
    ``AppBundle:Hello:index.html.twig`` would refer to the template located in
    ``src/AppBundle/Resources/views/Hello/index.html.twig``. See :ref:`template-referencing-in-bundle`.

.. index::
   single: Controller; Accessing services

.. _controller-accessing-services:

Accessing other Services
~~~~~~~~~~~~~~~~~~~~~~~~

Symfony comes packed with a lot of useful objects, called services. These
are used for rendering templates, sending emails, querying the database and
any other "work" you can think of. When you install a new bundle, it probably
brings in even *more* services.

When extending the base controller class, you can access any Symfony service
via the ``get()`` method. Here are several common services you might need::

    $templating = $this->get('templating');

    $router = $this->get('router');

    $mailer = $this->get('mailer');

What other services exist? To list all services, use the ``debug:container``
console command:

.. code-block:: bash

    $ php bin/console debug:container

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

The ``createNotFoundException()`` method is just a shortcut to create a
special :class:`Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException`
object, which ultimately triggers a 404 HTTP response inside Symfony.

Of course, you're free to throw any ``Exception`` class in your controller -
Symfony will automatically return a 500 HTTP response code.

.. code-block:: php

    throw new \Exception('Something went wrong!');

In every case, an error page is shown to the end user and a full debug
error page is shown to the developer (i.e. when you're using ``app_dev.php`` -
see :ref:`page-creation-environments`).

You'll want to customize the error page your user sees. To do that, see the
":doc:`/cookbook/controller/error_pages`" cookbook recipe.

.. index::
   single: Controller; The session
   single: Session

Managing the Session
--------------------

Symfony provides a nice session object that you can use to store information
about the user (be it a real person using a browser, a bot, or a web service)
between requests. By default, Symfony stores the attributes in a cookie
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

These attributes will remain in the session for the remainder of that user's
session.

.. index::
   single: Session; Flash messages

Flash Messages
~~~~~~~~~~~~~~

You can also store special messages, called "flash" messages, on the user's
session. By design, flash messages are meant to be used exactly once: they vanish
from the session automatically as soon as you retrieve them. This feature makes
"flash" messages particularly great for storing user notifications.

For example, imagine you're processing a form submission::

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        $form = $this->createForm(...);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // do some sort of processing

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            // $this->addFlash is equivalent to $this->get('session')->getFlashBag()->add

            return $this->redirectToRoute(...);
        }

        return $this->render(...);
    }

After processing the request, the controller sets a flash message in the session
and then redirects. The message key (``notice`` in this example) can be anything:
you'll use this key to retrieve the message.

In the template of the next page (or even better, in your base layout template),
read any flash messages from the session:

.. configuration-block::

    .. code-block:: html+twig

        {% for flash_message in app.session.flashbag.get('notice') %}
            <div class="flash-notice">
                {{ flash_message }}
            </div>
        {% endfor %}

    .. code-block:: html+php

        <?php foreach ($view['session']->getFlash('notice') as $message): ?>
            <div class="flash-notice">
                <?php echo "<div class='flash-error'>$message</div>" ?>
            </div>
        <?php endforeach ?>

.. note::

    It's common to use ``notice``, ``warning`` and ``error`` as the keys of the
    different types of flash messages, but you can use any key that fits your
    needs.

.. tip::

    You can use the
    :method:`Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface::peek`
    method instead to retrieve the message while keeping it in the bag.

.. index::
   single: Controller; Response object

The Response Object
-------------------

The only requirement for a controller is to return a ``Response`` object. The
:class:`Symfony\\Component\\HttpFoundation\\Response` class is an abstraction
around the HTTP response: the text-based message filled with headers and
content that's sent back to the client::

    use Symfony\Component\HttpFoundation\Response;

    // create a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // create a JSON-response with a 200 status code
    $response = new Response(json_encode(array('name' => $name)));
    $response->headers->set('Content-Type', 'application/json');

The ``headers`` property is a :class:`Symfony\\Component\\HttpFoundation\\HeaderBag`
object and has some nice methods for getting and setting the headers. The
header names are normalized so that using ``Content-Type`` is equivalent to
``content-type`` or even ``content_type``.

There are also special classes to make certain kinds of responses easier:

* For JSON, there is :class:`Symfony\\Component\\HttpFoundation\\JsonResponse`.
  See :ref:`component-http-foundation-json-response`.

* For files, there is :class:`Symfony\\Component\\HttpFoundation\\BinaryFileResponse`.
  See :ref:`component-http-foundation-serving-files`.

* For streamed responses, there is :class:`Symfony\\Component\\HttpFoundation\\StreamedResponse`.
  See :ref:`streaming-response`.

.. seealso::

    Don't worry! There is a lot more information about the Response object
    in the component documentation. See :ref:`component-http-foundation-response`.

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

.. seealso::

    Don't worry! There is a lot more information about the Request object
    in the component documentation. See :ref:`component-http-foundation-request`.

Creating Static Pages
---------------------

You can create a static page without even creating a controller (only a route
and template are needed).

See :doc:`/cookbook/templating/render_without_controller`.

.. index::
   single: Controller; Forwarding

Forwarding to Another Controller
--------------------------------

Though not very common, you can also forward to another controller internally
with the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::forward`
method. Instead of redirecting the user's browser, it makes an internal sub-request,
and calls the controller. The ``forward()`` method returns the ``Response``
object that's returned from *that* controller::

    public function indexAction($name)
    {
        $response = $this->forward('AppBundle:Something:fancy', array(
            'name'  => $name,
            'color' => 'green',
        ));

        // ... further modify the response or return it directly

        return $response;
    }

Notice that the ``forward()`` method uses a special string representation
of the controller (see :ref:`controller-string-syntax`). In this case, the
target controller function will be ``SomethingController::fancyAction()``
inside the AppBundle. The array passed to the method becomes the arguments on
the resulting controller. This same idea is used when embedding controllers
into templates (see :ref:`templating-embedding-controller`). The target
controller method would look something like this::

    public function fancyAction($name, $color)
    {
        // ... create and return a Response object
    }

Just like when creating a controller for a route, the order of the arguments of
``fancyAction`` doesn't matter. Symfony matches the index key names (e.g.
``name``) with the method argument names (e.g. ``$name``). If you change the
order of the arguments, Symfony will still pass the correct value to each
variable.

.. _checking-the-validity-of-a-csrf-token:

Validating a CSRF Token
-----------------------

Sometimes, you want to use CSRF protection in an action where you don't want to
use the Symfony Form component. If, for example, you're doing a DELETE action,
you can use the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::isCsrfTokenValid`
method to check the CSRF token::

    if ($this->isCsrfTokenValid('token_id', $submittedToken)) {
        // ... do something, like deleting an object
    }

    // isCsrfTokenValid() is equivalent to:
    // $this->get('security.csrf.token_manager')->isTokenValid(
    //     new \Symfony\Component\Security\Csrf\CsrfToken\CsrfToken('token_id', $token)
    // );

Final Thoughts
--------------

Whenever you create a page, you'll ultimately need to write some code that
contains the logic for that page. In Symfony, this is called a controller,
and it's a PHP function where you can do anything in order to return the
final ``Response`` object that will be returned to the user.

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

.. _`Controller class`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/Controller.php
