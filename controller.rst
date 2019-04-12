.. index::
   single: Controller

Controller
==========

A controller is a PHP function you create that reads information from the
``Request`` object and creates and returns a ``Response`` object. The response could
be an HTML page, JSON, XML, a file download, a redirect, a 404 error or anything
else. The controller executes whatever arbitrary logic *your application* needs
to render the content of a page.

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

    // src/Controller/LuckyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class LuckyController
    {
        /**
         * @Route("/lucky/number/{max}", name="app_lucky_number")
         */
        public function number($max)
        {
            $number = random_int(0, $max);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

The controller is the ``number()`` method, which lives inside a
controller class ``LuckyController``.

This controller is pretty straightforward:

* *line 2*: Symfony takes advantage of PHP's namespace functionality to
  namespace the entire controller class.

* *line 4*: Symfony again takes advantage of PHP's namespace functionality:
  the ``use`` keyword imports the ``Response`` class, which the controller
  must return.

* *line 7*: The class can technically be called anything, but it's suffixed
  with ``Controller`` by convention.

* *line 12*: The action method is allowed to have a ``$max`` argument thanks to the
  ``{max}`` :doc:`wildcard in the route </routing>`.

* *line 16*: The controller creates and returns a ``Response`` object.

.. index::
   single: Controller; Routes and controllers

Mapping a URL to a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to *view* the result of this controller, you need to map a URL to it via
a route. This was done above with the ``@Route("/lucky/number/{max}")``
:ref:`route annotation <annotation-routes>`.

To see your page, go to this URL in your browser:

    http://localhost:8000/lucky/number/100

For more information on routing, see :doc:`/routing`.

.. index::
   single: Controller; Base controller class

.. _the-base-controller-class-services:
.. _the-base-controller-classes-services:

The Base Controller Class & Services
------------------------------------

To make life nicer, Symfony comes with an optional base controller class called
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController`.
You can extend it to get access to some `helper methods`_.

Add the ``use`` statement atop your controller class and then modify
``LuckyController`` to extend it:

.. code-block:: diff

    // src/Controller/LuckyController.php
    namespace App\Controller;

    + use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    - class LuckyController
    + class LuckyController extends AbstractController
    {
        // ...
    }

That's it! You now have access to methods like :ref:`$this->render() <controller-rendering-templates>`
and many others that you'll learn about next.

.. index::
   single: Controller; Redirecting

Generating URLs
~~~~~~~~~~~~~~~

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::generateUrl`
method is just a helper method that generates the URL for a given route::

    $url = $this->generateUrl('app_lucky_number', ['max' => 10]);

Redirecting
~~~~~~~~~~~

If you want to redirect the user to another page, use the ``redirectToRoute()``
and ``redirect()`` methods::

    use Symfony\Component\HttpFoundation\RedirectResponse;

    // ...
    public function index()
    {
        // redirects to the "homepage" route
        return $this->redirectToRoute('homepage');

        // redirectToRoute is a shortcut for:
        // return new RedirectResponse($this->generateUrl('homepage'));

        // does a permanent - 301 redirect
        return $this->redirectToRoute('homepage', [], 301);

        // redirect to a route with parameters
        return $this->redirectToRoute('app_lucky_number', ['max' => 10]);

        // redirects to a route and maintains the original query string parameters
        return $this->redirectToRoute('blog_show', $request->query->all());

        // redirects externally
        return $this->redirect('http://symfony.com/doc');
    }

.. caution::

    The ``redirect()`` method does not check its destination in any way. If you
    redirect to a URL provided by end-users, your application may be open
    to the `unvalidated redirects security vulnerability`_.

.. index::
   single: Controller; Rendering templates

.. _controller-rendering-templates:

Rendering Templates
~~~~~~~~~~~~~~~~~~~

If you're serving HTML, you'll want to render a template. The ``render()``
method renders a template **and** puts that content into a ``Response``
object for you::

    // renders templates/lucky/number.html.twig
    return $this->render('lucky/number.html.twig', ['number' => $number]);

Templating and Twig are explained more in the
:doc:`Creating and Using Templates article </templating>`.

.. index::
   single: Controller; Accessing services

.. _controller-accessing-services:
.. _accessing-other-services:

Fetching Services
~~~~~~~~~~~~~~~~~

Symfony comes *packed* with a lot of useful objects, called :doc:`services </service_container>`.
These are used for rendering templates, sending emails, querying the database and
any other "work" you can think of.

If you need a service in a controller, type-hint an argument with its class
(or interface) name. Symfony will automatically pass you the service you need::

    use Psr\Log\LoggerInterface;
    // ...

    /**
     * @Route("/lucky/number/{max}")
     */
    public function number($max, LoggerInterface $logger)
    {
        $logger->info('We are logging!');
        // ...
    }

Awesome!

What other services can you type-hint? To see them, use the ``debug:autowiring`` console
command:

.. code-block:: terminal

    $ php bin/console debug:autowiring

If you need control over the *exact* value of an argument, you can :ref:`bind <services-binding>`
the argument by its name:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # explicitly configure the service
            App\Controller\LuckyController:
                public: true
                bind:
                    # for any $logger argument, pass this specific service
                    $logger: '@monolog.logger.doctrine'
                    # for any $projectDir argument, pass this parameter value
                    $projectDir: '%kernel.project_dir%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- Explicitly configure the service -->
                <service id="App\Controller\LuckyController" public="true">
                    <bind key="$logger"
                        type="service"
                        id="monolog.logger.doctrine"
                    />
                    <bind key="$projectDir">%kernel.project_dir%</bind>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Controller\LuckyController;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(LuckyController::class)
            ->setPublic(true)
            ->setBindings([
                '$logger' => new Reference('monolog.logger.doctrine'),
                '$projectDir' => '%kernel.project_dir%'
            ])
        ;

Like with all services, you can also use regular :ref:`constructor injection <services-constructor-injection>`
in your controllers.

For more information about services, see the :doc:`/service_container` article.

Generating Controllers
----------------------

To save time, you can install `Symfony Maker`_ and tell Symfony to generate a
new controller class:

.. code-block:: terminal

    $ php bin/console make:controller BrandNewController

    created: src/Controller/BrandNewController.php

If you want to generate an entire CRUD from a Doctrine :doc:`entity </doctrine>`,
use:

.. code-block:: terminal

    $ php bin/console make:crud Product

.. versionadded:: 1.2

    The ``make:crud`` command was introduced in MakerBundle 1.2.

.. index::
   single: Controller; Managing errors
   single: Controller; 404 pages

Managing Errors and 404 Pages
-----------------------------

When things are not found, you should return a 404 response. To do this, throw a
special type of exception::

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    // ...
    public function index()
    {
        // retrieve the object from database
        $product = ...;
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');

            // the above is just a shortcut for:
            // throw new NotFoundHttpException('The product does not exist');
        }

        return $this->render(...);
    }

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::createNotFoundException`
method is just a shortcut to create a special
:class:`Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException`
object, which ultimately triggers a 404 HTTP response inside Symfony.

If you throw an exception that extends or is an instance of
:class:`Symfony\\Component\\HttpKernel\\Exception\\HttpException`, Symfony will
use the appropriate HTTP status code. Otherwise, the response will have a 500
HTTP status code::

    // this exception ultimately generates a 500 status error
    throw new \Exception('Something went wrong!');

In every case, an error page is shown to the end user and a full debug
error page is shown to the developer (i.e. when you're in "Debug" mode - see
:ref:`page-creation-environments`).

To customize the error page that's shown to the user, see the
:doc:`/controller/error_pages` article.

.. _controller-request-argument:

The Request object as a Controller Argument
-------------------------------------------

What if you need to read query parameters, grab a request header or get access
to an uploaded file? All of that information is stored in Symfony's ``Request``
object. To get it in your controller, add it as an argument and
**type-hint it with the Request class**::

    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request, $firstName, $lastName)
    {
        $page = $request->query->get('page', 1);

        // ...
    }

:ref:`Keep reading <request-object-info>` for more information about using the
Request object.

.. index::
   single: Controller; The session
   single: Session

.. _session-intro:

Managing the Session
--------------------

Symfony provides a session service that you can use to store information
about the user between requests. Session is enabled by default, but will only be
started if you read or write from it.

Session storage and other configuration can be controlled under the
:ref:`framework.session configuration <config-framework-session>` in
``config/packages/framework.yaml``.

To get the session, add an argument and type-hint it with
:class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface`::

    use Symfony\Component\HttpFoundation\Session\SessionInterface;

    public function index(SessionInterface $session)
    {
        // stores an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // gets the attribute set by another controller in another request
        $foobar = $session->get('foobar');

        // uses a default value if the attribute doesn't exist
        $filters = $session->get('filters', []);
    }

Stored attributes remain in the session for the remainder of that user's session.

For more info, see :doc:`/session`.

.. index::
   single: Session; Flash messages

.. _flash-messages:

Flash Messages
~~~~~~~~~~~~~~

You can also store special messages, called "flash" messages, on the user's
session. By design, flash messages are meant to be used exactly once: they vanish
from the session automatically as soon as you retrieve them. This feature makes
"flash" messages particularly great for storing user notifications.

For example, imagine you're processing a :doc:`form </forms>` submission::

    use Symfony\Component\HttpFoundation\Request;

    public function update(Request $request)
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
read any flash messages from the session using ``app.flashes()``:

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# read and display just one flash message type #}
    {% for message in app.flashes('notice') %}
        <div class="flash-notice">
            {{ message }}
        </div>
    {% endfor %}

    {# read and display several types of flash messages #}
    {% for label, messages in app.flashes(['success', 'warning']) %}
        {% for message in messages %}
            <div class="flash-{{ label }}">
                {{ message }}
            </div>
        {% endfor %}
    {% endfor %}

    {# read and display all flash messages #}
    {% for label, messages in app.flashes %}
        {% for message in messages %}
            <div class="flash-{{ label }}">
                {{ message }}
            </div>
        {% endfor %}
    {% endfor %}

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

As mentioned :ref:`earlier <controller-request-argument>`, Symfony will
pass the ``Request`` object to any controller argument that is type-hinted with
the ``Request`` class::

    use Symfony\Component\HttpFoundation\Request;

    public function index(Request $request)
    {
        $request->isXmlHttpRequest(); // is it an Ajax request?

        $request->getPreferredLanguage(['en', 'fr']);

        // retrieves GET and POST variables respectively
        $request->query->get('page');
        $request->request->get('page');

        // retrieves SERVER variables
        $request->server->get('HTTP_HOST');

        // retrieves an instance of UploadedFile identified by foo
        $request->files->get('foo');

        // retrieves a COOKIE value
        $request->cookies->get('PHPSESSID');

        // retrieves an HTTP request header, with normalized, lowercase keys
        $request->headers->get('host');
        $request->headers->get('content-type');
    }

The ``Request`` class has several public properties and methods that return any
information you need about the request.

Like the ``Request``, the ``Response`` object has also a public ``headers`` property.
This is a :class:`Symfony\\Component\\HttpFoundation\\ResponseHeaderBag` that has
some nice methods for getting and setting response headers. The header names are
normalized so that using ``Content-Type`` is equivalent to ``content-type`` or even
``content_type``.

The only requirement for a controller is to return a ``Response`` object::

    use Symfony\Component\HttpFoundation\Response;

    // creates a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // creates a CSS-response with a 200 status code
    $response = new Response('<style> ... </style>');
    $response->headers->set('Content-Type', 'text/css');

There are special classes that make certain kinds of responses easier. Some of these
are mentioned below. To learn more about the ``Request`` and ``Response`` (and special
``Response`` classes), see the :ref:`HttpFoundation component documentation <component-http-foundation-request>`.

Returning JSON Response
~~~~~~~~~~~~~~~~~~~~~~~

To return JSON from a controller, use the ``json()`` helper method. This returns a
special ``JsonResponse`` object that encodes the data automatically::

    // ...
    public function index()
    {
        // returns '{"username":"jane.doe"}' and sets the proper Content-Type header
        return $this->json(['username' => 'jane.doe']);

        // the shortcut defines three optional arguments
        // return $this->json($data, $status = 200, $headers = [], $context = []);
    }

If the :doc:`serializer service </serializer>` is enabled in your
application, it will be used to serialize the data to JSON. Otherwise,
the :phpfunction:`json_encode` function is used.

Streaming File Responses
~~~~~~~~~~~~~~~~~~~~~~~~

You can use the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::file`
helper to serve a file from inside a controller::

    public function download()
    {
        // send the file contents and force the browser to download it
        return $this->file('/path/to/some_file.pdf');
    }

The ``file()`` helper provides some arguments to configure its behavior::

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\ResponseHeaderBag;

    public function download()
    {
        // load the file from the filesystem
        $file = new File('/path/to/some_file.pdf');

        return $this->file($file);

        // rename the downloaded file
        return $this->file($file, 'custom_name.pdf');

        // display the file contents in the browser instead of downloading it
        return $this->file('invoice_3241.pdf', 'my_invoice.pdf', ResponseHeaderBag::DISPOSITION_INLINE);
    }

Final Thoughts
--------------

Whenever you create a page, you'll ultimately need to write some code that
contains the logic for that page. In Symfony, this is called a controller,
and it's a PHP function where you can do anything in order to return the
final ``Response`` object that will be returned to the user.

To make life easier, you'll probably extend the base ``AbstractController`` class because
this gives access to shortcut methods (like ``render()`` and ``redirectToRoute()``).

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

.. _`helper methods`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Controller/ControllerTrait.php
.. _`Symfony Maker`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`unvalidated redirects security vulnerability`: https://www.owasp.org/index.php/Open_redirect
