Controller
==========

A controller is a PHP function you create that reads information from the
``Request`` object and creates and returns a ``Response`` object. The response could
be an HTML page, JSON, XML, a file download, a redirect, a 404 error or anything
else. The controller runs whatever arbitrary logic *your application* needs
to render the content of a page.

.. tip::

    If you haven't already created your first working page, check out
    :doc:`/page_creation` and then come back!

A Basic Controller
------------------

While a controller can be any PHP callable (function, method on an object,
or a ``Closure``), a controller is usually a method inside a controller
class::

    // src/Controller/LuckyController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class LuckyController
    {
        #[Route('/lucky/number/{max}', name: 'app_lucky_number')]
        public function number(int $max): Response
        {
            $number = random_int(0, $max);

            return new Response(
                '<html><body>Lucky number: '.$number.'</body></html>'
            );
        }
    }

The controller is the ``number()`` method, which lives inside the
controller class ``LuckyController``.

This controller is pretty straightforward:

* *line 2*: Symfony takes advantage of PHP's namespace functionality to
  namespace the entire controller class.

* *line 4*: Symfony again takes advantage of PHP's namespace functionality:
  the ``use`` keyword imports the ``Response`` class, which the controller
  must return.

* *line 7*: The class can technically be called anything, but it's suffixed
  with ``Controller`` by convention.

* *line 10*: The action method is allowed to have a ``$max`` argument thanks to the
  ``{max}`` :doc:`wildcard in the route </routing>`.

* *line 14*: The controller creates and returns a ``Response`` object.

Mapping a URL to a Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In order to *view* the result of this controller, you need to map a URL to it via
a route. This was done above with the ``#[Route('/lucky/number/{max}')]``
:ref:`route attribute <annotation-routes>`.

To see your page, go to this URL in your browser: http://localhost:8000/lucky/number/100

For more information on routing, see :doc:`/routing`.

.. _the-base-controller-class-services:
.. _the-base-controller-classes-services:

The Base Controller Class & Services
------------------------------------

To aid development, Symfony comes with an optional base controller class called
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController`.
It can be extended to gain access to helper methods.

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

Generating URLs
~~~~~~~~~~~~~~~

The :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController::generateUrl`
method is just a helper method that generates the URL for a given route::

    $url = $this->generateUrl('app_lucky_number', ['max' => 10]);

.. _controller-redirect:

Redirecting
~~~~~~~~~~~

If you want to redirect the user to another page, use the ``redirectToRoute()``
and ``redirect()`` methods::

    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpFoundation\Response;

    // ...
    public function index(): RedirectResponse
    {
        // redirects to the "homepage" route
        return $this->redirectToRoute('homepage');

        // redirectToRoute is a shortcut for:
        // return new RedirectResponse($this->generateUrl('homepage'));

        // does a permanent HTTP 301 redirect
        return $this->redirectToRoute('homepage', [], 301);
        // if you prefer, you can use PHP constants instead of hardcoded numbers
        return $this->redirectToRoute('homepage', [], Response::HTTP_MOVED_PERMANENTLY);

        // redirect to a route with parameters
        return $this->redirectToRoute('app_lucky_number', ['max' => 10]);

        // redirects to a route and maintains the original query string parameters
        return $this->redirectToRoute('blog_show', $request->query->all());

        // redirects to the current route (e.g. for Post/Redirect/Get pattern):
        return $this->redirectToRoute($request->attributes->get('_route'));

        // redirects externally
        return $this->redirect('http://symfony.com/doc');
    }

.. caution::

    The ``redirect()`` method does not check its destination in any way. If you
    redirect to a URL provided by end-users, your application may be open
    to the `unvalidated redirects security vulnerability`_.

.. _controller-rendering-templates:

Rendering Templates
~~~~~~~~~~~~~~~~~~~

If you're serving HTML, you'll want to render a template. The ``render()``
method renders a template **and** puts that content into a ``Response``
object for you::

    // renders templates/lucky/number.html.twig
    return $this->render('lucky/number.html.twig', ['number' => $number]);

Templating and Twig are explained more in the
:doc:`Creating and Using Templates article </templates>`.

.. _controller-accessing-services:
.. _accessing-other-services:

Fetching Services
~~~~~~~~~~~~~~~~~

Symfony comes *packed* with a lot of useful classes and functionalities, called :doc:`services </service_container>`.
These are used for rendering templates, sending emails, querying the database and
any other "work" you can think of.

If you need a service in a controller, type-hint an argument with its class
(or interface) name. Symfony will automatically pass you the service you need::

    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    #[Route('/lucky/number/{max}')]
    public function number(int $max, LoggerInterface $logger): Response
    {
        $logger->info('We are logging!');
        // ...
    }

Awesome!

What other services can you type-hint? To see them, use the ``debug:autowiring`` console
command:

.. code-block:: terminal

    $ php bin/console debug:autowiring

.. tip::

    If you need control over the *exact* value of an argument, or require a parameter,
    you can use the ``#[Autowire]`` attribute::

        // ...
        use Psr\Log\LoggerInterface;
        use Symfony\Component\DependencyInjection\Attribute\Autowire;
        use Symfony\Component\HttpFoundation\Response;

        class LuckyController extends AbstractController
        {
            public function number(
                int $max,

                // inject a specific logger service
                #[Autowire(service: 'monolog.logger.request')]
                LoggerInterface $logger,

                // or inject parameter values
                #[Autowire('%kernel.project_dir%')]
                string $projectDir
            ): Response
            {
                $logger->info('We are logging!');
                // ...
            }
        }

    You can read more about this attribute in :ref:`autowire-attribute`.

    .. versionadded:: 6.1

        The ``#[Autowire]`` attribute was introduced in Symfony 6.1.

Like with all services, you can also use regular
:ref:`constructor injection <services-constructor-injection>` in your
controllers.

For more information about services, see the :doc:`/service_container` article.

Generating Controllers
----------------------

To save time, you can install `Symfony Maker`_ and tell Symfony to generate a
new controller class:

.. code-block:: terminal

    $ php bin/console make:controller BrandNewController

    created: src/Controller/BrandNewController.php
    created: templates/brandnew/index.html.twig

If you want to generate an entire CRUD from a Doctrine :doc:`entity </doctrine>`,
use:

.. code-block:: terminal

    $ php bin/console make:crud Product

    created: src/Controller/ProductController.php
    created: src/Form/ProductType.php
    created: templates/product/_delete_form.html.twig
    created: templates/product/_form.html.twig
    created: templates/product/edit.html.twig
    created: templates/product/index.html.twig
    created: templates/product/new.html.twig
    created: templates/product/show.html.twig

Managing Errors and 404 Pages
-----------------------------

When things are not found, you should return a 404 response. To do this, throw a
special type of exception::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    // ...
    public function index(): Response
    {
        // retrieve the object from database
        $product = ...;
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');

            // the above is just a shortcut for:
            // throw new NotFoundHttpException('The product does not exist');
        }

        return $this->render(/* ... */);
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
to an uploaded file? That information is stored in Symfony's ``Request``
object. To access it in your controller, add it as an argument and
**type-hint it with the Request class**::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        // ...
    }

:ref:`Keep reading <request-object-info>` for more information about using the
Request object.

Managing the Session
--------------------

You can store special messages, called "flash" messages, on the user's session.
By design, flash messages are meant to be used exactly once: they vanish from
the session automatically as soon as you retrieve them. This feature makes
"flash" messages particularly great for storing user notifications.

For example, imagine you're processing a :doc:`form </forms>` submission::

.. configuration-block::

    .. code-block:: php-symfony

        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\Response;
        // ...

        public function update(Request $request): Response
        {
            // ...

            if ($form->isSubmitted() && $form->isValid()) {
                // do some sort of processing

                $this->addFlash(
                    'notice',
                    'Your changes were saved!'
                );
                // $this->addFlash() is equivalent to $request->getSession()->getFlashBag()->add()

                return $this->redirectToRoute(/* ... */);
            }

            return $this->render(/* ... */);
        }

:ref:`Reading <session-intro>` for more information about using Sessions.

.. _request-object-info:

The Request and Response Object
-------------------------------

As mentioned :ref:`earlier <controller-request-argument>`, Symfony will
pass the ``Request`` object to any controller argument that is type-hinted with
the ``Request`` class::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    public function index(Request $request): Response
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

Like the ``Request``, the ``Response`` object has a public ``headers`` property.
This object is of the type :class:`Symfony\\Component\\HttpFoundation\\ResponseHeaderBag`
and provides methods for getting and setting response headers. The header names are
normalized. As a result, the name ``Content-Type`` is equivalent to
the name ``content-type`` or ``content_type``.

In Symfony, a controller is required to return a ``Response`` object::

    use Symfony\Component\HttpFoundation\Response;

    // creates a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // creates a CSS-response with a 200 status code
    $response = new Response('<style> ... </style>');
    $response->headers->set('Content-Type', 'text/css');

To facilitate this, different response objects are included to address different
response types.  Some of these are mentioned below. To learn more about the
``Request`` and ``Response`` (and different ``Response`` classes), see the
:ref:`HttpFoundation component documentation <component-http-foundation-request>`.

Accessing Configuration Values
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To get the value of any :ref:`configuration parameter <configuration-parameters>`
from a controller, use the ``getParameter()`` helper method::

    // ...
    public function index(): Response
    {
        $contentsDir = $this->getParameter('kernel.project_dir').'/contents';
        // ...
    }

Returning JSON Response
~~~~~~~~~~~~~~~~~~~~~~~

To return JSON from a controller, use the ``json()`` helper method. This returns a
``JsonResponse`` object that encodes the data automatically::

    use Symfony\Component\HttpFoundation\JsonResponse;
    // ...

    public function index(): JsonResponse
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

    use Symfony\Component\HttpFoundation\BinaryFileResponse;
    // ...

    public function download(): BinaryFileResponse
    {
        // send the file contents and force the browser to download it
        return $this->file('/path/to/some_file.pdf');
    }

The ``file()`` helper provides some arguments to configure its behavior::

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\ResponseHeaderBag;
    // ...

    public function download(): BinaryFileResponse
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

In Symfony, a controller is usually a class method which is used to accept
requests, and return a ``Response`` object. When mapped with a URL, a controller
becomes accessible and its response can be viewed.

To facilitate the development of controllers, Symfony provides an
``AbstractController``.  It can be used to extend the controller class allowing
access to some frequently used utilities such as ``render()`` and
``redirectToRoute()``. The ``AbstractController`` also provides the
``createNotFoundException()`` utility which is used to return a page not found
response.

In other articles, you'll learn how to use specific services from inside your controller
that will help you persist and fetch objects from a database, process form submissions,
handle caching and more.

Keep Going!
-----------

Next, learn all about :doc:`rendering templates with Twig </templates>`.

Learn more about Controllers
----------------------------

.. toctree::
    :hidden:

    templates

.. toctree::
    :maxdepth: 1
    :glob:

    controller/*

.. _`Symfony Maker`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`unvalidated redirects security vulnerability`: https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html
