.. index::
   single: Controller

Controller
==========

A controller is a PHP function you create that reads information from the Symfony's
``Request`` object and creates and returns a ``Response`` object. The response could
be an HTML page, JSON, XML, a file download, a redirect, a 404 error or anything
else you can dream up. The controller executes whatever arbitrary logic
*your application* needs to render the content of a page.

See how simple this is by looking at a Symfony controller in action.
This renders a page that prints a lucky (random) number::

    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;

    class LuckyController
    {
        /**
         * @Route("/lucky/number")
         */
        public function numberAction()
        {
            $number = mt_rand(0, 100);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

But in the real world, your controller will probably do a lot of work in order to
create the response. It might read information from the request, load a database
resource, send an email or set information on the user's session.
But in all cases, the controller will eventually return the ``Response`` object
that will be delivered back to the client.

.. tip::

    If you haven't already created your first working page, check out
    :doc:`/page_creation` and then come back!

.. index::
   single: Controller; Simple example

A Simple Controller
-------------------

While a controller can be any PHP callable (a function, method on an object,
or a ``Closure``), a controller is usually a method inside a controller
class::

    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class LuckyController
    {
        /**
         * @Route("/lucky/number/{max}")
         */
        public function numberAction($max)
        {
            $number = mt_rand(0, $max);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

The controller is the ``numberAction()`` method, which lives inside a
controller class ``LuckyController``.

This controller is pretty straightforward:

* *line 2*: Symfony takes advantage of PHP's namespace functionality to
  namespace the entire controller class.

* *line 4*: Symfony again takes advantage of PHP's namespace functionality:
  the ``use`` keyword imports the ``Response`` class, which the controller
  must return.

* *line 7*: The class can technically be called anything - but should end in the
  word ``Controller`` (this isn't *required*, but some shortcuts rely on this).

* *line 12*: Each action method in a controller class is suffixed with ``Action``
  (again, this isn't *required*, but some shortcuts rely on this). This method
  is allowed to have a ``$max`` argument thanks to the ``{max}``
  :doc:`wildcard in the route </routing>`.

* *line 16*: The controller creates and returns a ``Response`` object.

.. index::
   single: Controller; Routes and controllers

Mapping a URL to a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to *view* the result of this controller, you need to map a URL to it via
a route. This was done above with the ``@Route("/lucky/number/{max}")`` annotation.

To see your page, go to this URL in your browser:

    http://localhost:8000/lucky/number/100

For more information on routing, see :doc:`/routing`.

.. index::
   single: Controller; Base controller class

The Base Controller Class & Services
------------------------------------

For convenience, Symfony comes with an optional base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class.
If you extend it, this won't change anything about how your controller
works, but you'll get access to a number of **helper methods** and the
**service container** (see :ref:`controller-accessing-services`): an
array-like object that gives you access to every useful object in the
system. These useful objects are called **services**, and Symfony ships
with a service object that can render Twig templates, another that can
log messages and many more.

Add the ``use`` statement atop the ``Controller`` class and then modify
``LuckyController`` to extend it::

    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        // ...
    }

Helper methods are just shortcuts to using core Symfony functionality
that's available to you with or without the use of the base
``Controller`` class. A great way to see the core functionality in
action is to look in the
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` class.

.. index::
   single: Controller; Redirecting

Generating URLs
~~~~~~~~~~~~~~~

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::generateUrl`
method is just a helper method that generates the URL for a given route::

    $url = $this->generateUrl('blog_show', array('slug' => 'slug-value'));

Redirecting
~~~~~~~~~~~

If you want to redirect the user to another page, use the ``redirectToRoute()``
and ``redirect()`` methods::

    public function indexAction()
    {
        // redirect to the "homepage" route
        return $this->redirectToRoute('homepage');

        // do a permanent - 301 redirect
        return $this->redirectToRoute('homepage', array(), 301);

        // redirect to a route with parameters
        return $this->redirectToRoute('blog_show', array('slug' => 'my-page'));

        // redirect externally
        return $this->redirect('http://symfony.com/doc');
    }

.. versionadded:: 2.6
    The ``redirectToRoute()`` method was introduced in Symfony 2.6. Previously (and still now), you
    could use ``redirect()`` and ``generateUrl()`` together for this.

For more information, see the :doc:`Routing article </routing>`.

.. caution::

    The ``redirect()`` method does not check its destination in any way. If you
    redirect to some URL provided by the end-users, your application may be open
    to the `unvalidated redirects security vulnerability`_.


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

    // renders app/Resources/views/lucky/number.html.twig
    return $this->render('lucky/number.html.twig', array('name' => $name));

Templates can also live in deeper sub-directories. Just try to avoid
creating unnecessarily deep structures::

    // renders app/Resources/views/lottery/lucky/number.html.twig
    return $this->render('lottery/lucky/number.html.twig', array(
        'name' => $name,
    ));

The Symfony templating system and Twig are explained more in the
:doc:`Creating and Using Templates article </templating>`.

.. index::
   single: Controller; Accessing services

.. _controller-accessing-services:

Accessing Other Services
~~~~~~~~~~~~~~~~~~~~~~~~

Symfony comes packed with a lot of useful objects, called *services*. These
are used for rendering templates, sending emails, querying the database and
any other "work" you can think of. When you install a new bundle, it probably
brings in even *more* services.

When extending the base controller class, you can access any Symfony service
via the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::get`
method of the ``Controller`` class. Here are several common services you might
need::

    $templating = $this->get('templating');

    $router = $this->get('router');

    $mailer = $this->get('mailer');

What other services exist? To list all services, use the ``debug:container``
console command:

.. code-block:: terminal

    $ php app/console debug:container

.. versionadded:: 2.6
    Prior to Symfony 2.6, this command was called ``container:debug``.

For more information, see the :doc:`/service_container` article.

.. tip::

    To get a :ref:`container configuration parameter <config-parameter-intro>`,
    use the
    :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getParameter`
    method::

        $from = $this->getParameter('app.mailer.from');

    .. versionadded:: 2.7
        The ``Controller::getParameter()`` method was introduced in Symfony
        2.7. Use ``$this->container->getParameter()`` in versions prior to 2.7.

.. index::
   single: Controller; Managing errors
   single: Controller; 404 pages

Managing Errors and 404 Pages
-----------------------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. To do this, you'll throw a special type of exception.
If you're extending the base ``Controller`` class, do the following::

    public function indexAction()
    {
        // retrieve the object from database
        $product = ...;
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        return $this->render(...);
    }

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::createNotFoundException`
method is just a shortcut to create a special
:class:`Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException`
object, which ultimately triggers a 404 HTTP response inside Symfony.

Of course, you're free to throw any ``Exception`` class in your controller -
Symfony will automatically return a 500 HTTP response code.

.. code-block:: php

    throw new \Exception('Something went wrong!');

In every case, an error page is shown to the end user and a full debug
error page is shown to the developer (i.e. when you're using the ``app_dev.php``
front controller - see :ref:`page-creation-environments`).

You'll want to customize the error page your user sees. To do that, see
the :doc:`/controller/error_pages` article.

.. index::
   single: Controller; The session
   single: Session

.. _controller-request-argument:

The Request object as a Controller Argument
-------------------------------------------

What if you need to read query parameters, grab a request header or get access
to an uploaded file? All of that information is stored in Symfony's ``Request``
object. To get it in your controller, just add it as an argument and
**type-hint it with the Request class**::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request, $firstName, $lastName)
    {
        $page = $request->query->get('page', 1);

        // ...
    }

:ref:`Keep reading <request-object-info>` for more information about using the
Request object.

Managing the Session
--------------------

Symfony provides a nice session object that you can use to store information
about the user between requests. By default, Symfony stores the token in a
cookie and writes the attributes to a file by using native PHP sessions.

To retrieve the session, call
:method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getSession`
method on the ``Request`` object. This method returns a
:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface` with easy
methods for storing and fetching things from the session::

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

Stored attributes remain in the session for the remainder of that user's session.

.. index::
   single: Session; Flash messages

Flash Messages
~~~~~~~~~~~~~~

You can also store special messages, called "flash" messages, on the user's
session. By design, flash messages are meant to be used exactly once: they vanish
from the session automatically as soon as you retrieve them. This feature makes
"flash" messages particularly great for storing user notifications.

For example, imagine you're processing a :doc:`form </forms>` submission::

    use Symfony\Component\HttpFoundation\Request;

    public function updateAction(Request $request)
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            // do some sort of processing

            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );
            // $this->addFlash() is equivalent to $request->getSession()->getFlashBag()->add()

            return $this->redirectToRoute(...);
        }

        return $this->render(...);
    }

After processing the request, the controller sets a flash message in the session
and then redirects. The message key (``notice`` in this example) can be anything:
you'll use this key to retrieve the message.

In the template of the next page (or even better, in your base layout template),
read any flash messages from the session:

.. code-block:: html+twig

    {# app/Resources/views/base.html.twig #}

    {# you can read and display just one flash message type... #}
    {% for flash_message in app.session.flashBag.get('notice') %}
        <div class="flash-notice">
            {{ flash_message }}
        </div>
    {% endfor %}

    {# ...or you can read and display every flash message available #}
    {% for type, flash_messages in app.session.flashBag.all %}
        {% for flash_message in flash_messages %}
            <div class="flash-{{ type }}">
                {{ flash_message }}
            </div>
        {% endfor %}
    {% endfor %}

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

.. _request-object-info:

The Request and Response Object
-------------------------------

As mentioned :ref:`earlier <controller-request-argument>`, the framework will
pass the ``Request`` object to any controller argument that is type-hinted with
the ``Request`` class::

    use Symfony\Component\HttpFoundation\Request;

    public function indexAction(Request $request)
    {
        $request->isXmlHttpRequest(); // is it an Ajax request?

        $request->getPreferredLanguage(array('en', 'fr'));

        // retrieve GET and POST variables respectively
        $request->query->get('page');
        $request->request->get('page');

        // retrieve SERVER variables
        $request->server->get('HTTP_HOST');

        // retrieves an instance of UploadedFile identified by foo
        $request->files->get('foo');

        // retrieve a COOKIE value
        $request->cookies->get('PHPSESSID');

        // retrieve an HTTP request header, with normalized, lowercase keys
        $request->headers->get('host');
        $request->headers->get('content_type');
    }

The ``Request`` class has several public properties and methods that return any
information you need about the request.

Like the ``Request``, the ``Response`` object has also a public ``headers`` property.
This is a :class:`Symfony\\Component\\HttpFoundation\\ResponseHeaderBag` that has
some nice methods for getting and setting response headers. The header names are
normalized so that using ``Content-Type`` is equivalent to ``content-type`` or even
``content_type``.

The only requirement for a controller is to return a ``Response`` object.
The :class:`Symfony\\Component\\HttpFoundation\\Response` class is an
abstraction around the HTTP response - the text-based message filled with
headers and content that's sent back to the client::

    use Symfony\Component\HttpFoundation\Response;

    // create a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // JsonResponse is a sub-class of Response
    $response = new JsonResponse(array('name' => $name));
    // set a header!
    $response->headers->set('X-Rate-Limit', 10);

There are special classes that make certain kinds of responses easier:

* For JSON, there is :class:`Symfony\\Component\\HttpFoundation\\JsonResponse`.
  See :ref:`component-http-foundation-json-response`.

* For files, there is :class:`Symfony\\Component\\HttpFoundation\\BinaryFileResponse`.
  See :ref:`component-http-foundation-serving-files`.

* For streamed responses, there is
  :class:`Symfony\\Component\\HttpFoundation\\StreamedResponse`.
  See :ref:`streaming-response`.

.. seealso::

    Now that you know the basics you can continue your research on Symfony
    ``Request`` and ``Response`` object in the
    :ref:`HttpFoundation component documentation <component-http-foundation-request>`.

Final Thoughts
--------------

Whenever you create a page, you'll ultimately need to write some code that
contains the logic for that page. In Symfony, this is called a controller,
and it's a PHP function where you can do anything in order to return the
final ``Response`` object that will be returned to the user.

To make life easier, you'll probably extend the base ``Controller`` class because
this gives two things:

A) Shortcut methods (like ``render()`` and ``redirectToRoute()``);

B) Access to *all* of the useful objects (services) in the system via the
   :ref:`get() <controller-accessing-services>` method.

In other articles, you'll learn how to use specific services from inside your controller
that will help you persist and fetch objects from a database, process form submissions,
handle caching and more.

Keep Going!
-----------

Next, learn all about :doc:`rendering templates with Twig </templating>`.

Learn more about Controllers
----------------------------

.. toctree::
    :hidden:

    templating

.. toctree::
    :maxdepth: 1
    :glob:

    controller/*

.. _`unvalidated redirects security vulnerability`: https://www.owasp.org/index.php/Open_redirect
