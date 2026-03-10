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
    use Symfony\Component\Routing\Attribute\Route;

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

This controller is quite simple:

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
:ref:`route attribute <attribute-routes>`.

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

.. danger::

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
(or interface) name and Symfony will inject it automatically. This requires
your :doc:`controller to be registered as a service </controller/service>`::

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

.. _controller_map-request:

Automatic Mapping Of The Request
--------------------------------

It is possible to automatically map request's payload and/or query parameters to
your controller's action arguments with attributes.

Mapping Query Parameters Individually
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Let's say a user sends you a request with the following query string:
``https://example.com/dashboard?firstName=John&lastName=Smith&age=27``.
Thanks to the :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapQueryParameter`
attribute, arguments of your controller's action can be automatically fulfilled::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

    // ...

    public function dashboard(
        #[MapQueryParameter] string $firstName,
        #[MapQueryParameter] string $lastName,
        #[MapQueryParameter] int $age,
    ): Response
    {
        // ...
    }

The ``MapQueryParameter`` attribute supports the following argument types:

* ``\BackedEnum``
* ``array``
* ``bool``
* ``float``
* ``int``
* ``string``
* Objects that extend :class:`Symfony\\Component\\Uid\\AbstractUid`

``#[MapQueryParameter]`` can take an optional argument called ``filter``. You can use the
`Validate Filters`_ constants defined in PHP::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

    // ...

    public function dashboard(
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w+$/'])] string $firstName,
        #[MapQueryParameter] string $lastName,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_INT)] int $age,
    ): Response
    {
        // ...
    }

.. _controller-mapping-query-string:

Mapping The Whole Query String
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Another possibility is to map the entire query string into an object that will hold
available query parameters. Let's say you declare the following DTO with its
optional validation constraints::

    namespace App\Model;

    use Symfony\Component\Validator\Constraints as Assert;

    class UserDto
    {
        public function __construct(
            #[Assert\NotBlank]
            public string $firstName,

            #[Assert\NotBlank]
            public string $lastName,

            #[Assert\GreaterThan(18)]
            public int $age,
        ) {
        }
    }

You can then use the :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapQueryString`
attribute in your controller::

    use App\Model\UserDto;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapQueryString;

    // ...

    public function dashboard(
        #[MapQueryString] UserDto $userDto
    ): Response
    {
        // ...
    }

You can customize the validation groups used during the mapping and also the
HTTP status to return if the validation fails::

    use Symfony\Component\HttpFoundation\Response;

    // ...

    public function dashboard(
        #[MapQueryString(
            validationGroups: ['strict', 'edit'],
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
        )] UserDto $userDto
    ): Response
    {
        // ...
    }

The default status code returned if the validation fails is 404.

If you want to map your object to a nested array in your query using a specific key,
set the ``key`` option in the ``#[MapQueryString]`` attribute::

    use App\Model\SearchDto;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapQueryString;

    // ...

    public function dashboard(
        #[MapQueryString(key: 'search')] SearchDto $searchDto
    ): Response
    {
        // ...
    }

If you need a valid DTO even when the request query string is empty, set a
default value for your controller arguments::

    use App\Model\UserDto;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapQueryString;

    // ...

    public function dashboard(
        #[MapQueryString] UserDto $userDto = new UserDto()
    ): Response
    {
        // ...
    }

.. _controller-mapping-request-payload:

Mapping Request Payload
~~~~~~~~~~~~~~~~~~~~~~~

When creating an API and dealing with other HTTP methods than ``GET`` (like
``POST`` or ``PUT``), user's data are not stored in the query string
but directly in the request payload, like this:

.. code-block:: json

    {
        "firstName": "John",
        "lastName": "Smith",
        "age": 28
    }

In this case, it is also possible to directly map this payload to your DTO by
using the :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapRequestPayload`
attribute::

    use App\Model\UserDto;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

    // ...

    public function dashboard(
        #[MapRequestPayload] UserDto $userDto
    ): Response
    {
        // ...
    }

This attribute allows you to customize the serialization context as well
as the class responsible of doing the mapping between the request and
your DTO::

    public function dashboard(
        #[MapRequestPayload(
            serializationContext: ['...'],
            resolver: App\Resolver\UserDtoResolver
        )]
        UserDto $userDto
    ): Response
    {
        // ...
    }

You can also customize the validation groups used, the status code to return if
the validation fails as well as supported payload formats::

    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function dashboard(
        #[MapRequestPayload(
            acceptFormat: 'json',
            validationGroups: ['strict', 'read'],
            validationFailedStatusCode: Response::HTTP_NOT_FOUND
        )] UserDto $userDto
    ): Response
    {
        // ...
    }

The default status code returned if the validation fails is 422.

You can also use expressions to define validation groups dynamically based on
controller arguments::

    use Symfony\Component\ExpressionLanguage\Expression;
    // ...

    #[Route('/user/{id}', methods: ['PUT'])]
    public function update(
        User $user,
        #[MapRequestPayload(
            validationGroups: [new Expression('args["user"].getType()')]
        )] UpdateUserDto $dto
    ): Response
    {
        // ...
    }

In this example, the validation group is resolved from the ``User`` entity.
The ``args`` variable provides access to all controller arguments by name.

.. versionadded:: 8.1

    Support for expressions in ``validationGroups`` was introduced in Symfony 8.1.

.. tip::

    If you build a JSON API, make sure to declare your route as using the JSON
    :ref:`format <routing-format-parameter>`. This will make the error handling
    output a JSON response in case of validation errors, rather than an HTML page::

        #[Route('/dashboard', name: 'dashboard', format: 'json')]

Make sure to install `phpstan/phpdoc-parser`_ and `phpdocumentor/type-resolver`_
if you want to map a nested array of specific DTOs::

    public function dashboard(
        #[MapRequestPayload] EmployeesDto $employeesDto
    ): Response
    {
        // ...
    }

    final class EmployeesDto
    {
        /**
         * @param UserDto[] $users
         */
        public function __construct(
            public readonly array $users = []
        ) {}
    }

Instead of returning an array of DTO objects, you can tell Symfony to transform
each DTO object into an array and return something like this:

.. code-block:: json

    [
        {
            "firstName": "John",
            "lastName": "Smith",
            "age": 28
        },
        {
            "firstName": "Jane",
            "lastName": "Doe",
            "age": 30
        }
    ]

To do so, use a variadic argument and let Symfony map each payload item to a DTO
instance automatically::

    public function dashboard(
        #[MapRequestPayload] UserDto ...$users
    ): Response
    {
        // ...
    }

.. versionadded:: 8.1

    Support for variadic arguments with ``#[MapRequestPayload]`` was introduced
    in Symfony 8.1.

.. note::

    As an alternative, instead of variadic arguments you can map the parameter
    as an array and configure the type of each element using the ``type`` option
    of the attribute::

        public function dashboard(
            #[MapRequestPayload(type: UserDto::class)] array $users
        ): Response
        {
            // ...
        }

.. _controller_map-request-header:

Mapping Request Headers
~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    The ``#[MapRequestHeader]`` attribute was introduced in Symfony 8.1.

The :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapRequestHeader`
attribute maps an HTTP request header to a controller argument::

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapRequestHeader;

    // ...

    public function dashboard(
        #[MapRequestHeader] string $acceptLanguage,
    ): Response
    {
        // ...
    }

By default, the header name is converted from kebab-case to camelCase to
match the argument name (e.g. the ``accept-language`` header maps to the
``$acceptLanguage`` argument). Use the ``name`` option to map to a different
header::

    public function dashboard(
        #[MapRequestHeader(name: 'x-custom-token')] string $token,
    ): Response
    {
        // ...
    }

The attribute supports the following argument types:

* ``string``: returns the header value as a string;
* ``array``: returns the header values as an array. For the ``accept``,
  ``accept-charset``, ``accept-language`` and ``accept-encoding`` headers, the
  values are automatically parsed (e.g. ``accept-language: en-us,en;q=0.5``
  returns ``['en_US', 'en']``);
* :class:`Symfony\\Component\\HttpFoundation\\AcceptHeader`: returns a parsed
  ``AcceptHeader`` object for advanced quality-value handling.

If the header is missing and the argument has no default value and is not
nullable, a ``400 Bad Request`` response is returned. You can customize this
status code with the ``validationFailedStatusCode`` option::

    use Symfony\Component\HttpFoundation\Response;

    // ...

    public function dashboard(
        #[MapRequestHeader(validationFailedStatusCode: Response::HTTP_NOT_FOUND)] string $accept,
    ): Response
    {
        // ...
    }

.. _controller_map-uploaded-file:

Mapping Uploaded Files
~~~~~~~~~~~~~~~~~~~~~~

Symfony provides an attribute called ``#[MapUploadedFile]`` to map one or more
``UploadedFile`` objects to controller arguments::

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
    use Symfony\Component\Routing\Attribute\Route;

    class UserController extends AbstractController
    {
        #[Route('/user/picture', methods: ['PUT'])]
        public function changePicture(
            #[MapUploadedFile] UploadedFile $picture,
        ): Response {
            // ...
        }
    }

In this example, the associated :doc:`argument resolver </controller/value_resolver>`
fetches the ``UploadedFile`` based on the argument name (``$picture``). If no file
is submitted, an ``HttpException`` is thrown. You can change this by making the
controller argument nullable:

.. code-block:: php-attributes

    #[MapUploadedFile]
    ?UploadedFile $document

The ``#[MapUploadedFile]`` attribute also allows you to pass a list of constraints
to apply to the uploaded file::

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Validator\Constraints as Assert;

    class UserController extends AbstractController
    {
        #[Route('/user/picture', methods: ['PUT'])]
        public function changePicture(
            #[MapUploadedFile([
                new Assert\File(mimeTypes: ['image/png', 'image/jpeg']),
                new Assert\Image(maxWidth: 3840, maxHeight: 2160),
            ])]
            UploadedFile $picture,
        ): Response {
            // ...
        }
    }

The validation constraints are checked before injecting the ``UploadedFile`` into
the controller argument. If there's a constraint violation, an ``HttpException``
is thrown and the controller's action is not executed.

If you need to upload a collection of files, map them to an array or a variadic
argument. The given constraint will be applied to all files and if any of them
fails, an ``HttpException`` is thrown:

.. code-block:: php-attributes

    #[MapUploadedFile(new Assert\File(mimeTypes: ['application/pdf']))]
    array $documents

    #[MapUploadedFile(new Assert\File(mimeTypes: ['application/pdf']))]
    UploadedFile ...$documents

Use the ``name`` option to rename the uploaded file to a custom value:

.. code-block:: php-attributes

    #[MapUploadedFile(name: 'something-else')]
    UploadedFile $document

In addition, you can change the status code of the HTTP exception thrown when
there are constraint violations:

.. code-block:: php-attributes

    #[MapUploadedFile(
        constraints: new Assert\File(maxSize: '2M'),
        validationFailedStatusCode: Response::HTTP_REQUEST_ENTITY_TOO_LARGE
    )]
    UploadedFile $document

Managing the Session
--------------------

Symfony provides a session service to store information about the user between
requests. You can access the session through the ``Request`` object (in services,
:doc:`inject the RequestStack service </service_container/request>`)::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    public function index(Request $request): Response
    {
        $session = $request->getSession();

        // store an attribute for reuse during a later user request
        $session->set('user_id', 42);

        // retrieve an attribute with an optional default value
        $userId = $session->get('user_id', 0);

        // ...
    }

Read the :doc:`session documentation </session>` for more details about
configuring and using sessions.

Flash Messages
~~~~~~~~~~~~~~

Flash messages are special session messages meant to be used exactly once: they
vanish from the session automatically as soon as you retrieve them. This makes
them ideal for storing user notifications::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function update(Request $request): Response
    {
        // ... do some data processing

        $this->addFlash('notice', 'Your changes were saved!');
        // $this->addFlash() is equivalent to $request->getSession()->getFlashBag()->add()

        return $this->redirectToRoute(/* ... */);
    }

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
        $request->getPayload()->get('page');

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

.. note::

    Technically, a controller can return a value other than a ``Response``.
    However, your application is responsible for transforming that value into a
    ``Response`` object. This is handled using :doc:`events </event_dispatcher>`
    (specifically the :ref:`kernel.view event <component-http-kernel-kernel-view>`),
    an advanced feature you'll learn about later.

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

.. _controller_serialize:

Serializing Controller Return Values Automatically
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of manually calling the serializer and building a response, you can add
the :class:`Symfony\\Component\\HttpKernel\\Attribute\\Serialize` attribute to
your controller method. The controller can then return any object or array, and
Symfony will serialize it automatically based on the request format (defaulting
to JSON)::

    use Symfony\Component\HttpKernel\Attribute\Serialize;

    class ProductController
    {
        #[Serialize]
        public function show(): Product
        {
            return new Product(1, 'Asus UX550');
        }
    }

You can also customize the HTTP status code, headers, and serialization context::

    use Symfony\Component\HttpKernel\Attribute\Serialize;

    class ProductController
    {
        #[Serialize(code: 201, headers: ['X-Custom' => 'value'], context: ['groups' => ['read']])]
        public function create(): ProductCreated
        {
            // ... create the product

            return new ProductCreated(1);
        }
    }

The ``#[Serialize]`` attribute accepts the following arguments:

``code``
    The HTTP status code of the response (default: ``200``).
``headers``
    An associative array of extra HTTP headers to add to the response.
``context``
    The serialization context passed to the :doc:`Serializer </serializer>`
    (e.g. ``['groups' => ['read']]``).

The response format is determined by the request format (``$request->getRequestFormat()``),
which defaults to ``json``. The ``Content-Type`` header is set automatically
based on the format. If the format is not supported by the serializer, a
``415 Unsupported Media Type`` response is returned.

.. note::

    The ``#[Serialize]`` attribute requires the
    :doc:`Serializer component </serializer>` to be installed and enabled.

.. versionadded:: 8.1

    The ``#[Serialize]`` attribute was introduced in Symfony 8.1.

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

Sending Early Hints
~~~~~~~~~~~~~~~~~~~

You can improve performance by sending ``103`` Early Hints responses to ask
the browser to start downloading assets before the full response is ready.
See :ref:`early-hints` for details.

.. _controller-server-sent-events:

Streaming Server-Sent Events
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

`Server-Sent Events (SSE)`_ is a standard that allows a server to push updates
to the client over a single HTTP connection. It provides an efficient way to
send real-time updates from the server to the browser, such as live notifications,
progress updates, or data feeds.

The :class:`Symfony\\Component\\HttpFoundation\\EventStreamResponse` class
allows you to stream events to the client using the SSE protocol. It automatically
sets the required headers (``Content-Type: text/event-stream``, ``Cache-Control: no-cache``,
``Connection: keep-alive``) and provides an API to send events::

    use Symfony\Component\HttpFoundation\EventStreamResponse;
    use Symfony\Component\HttpFoundation\ServerEvent;

    // ...

    public function liveNotifications(): EventStreamResponse
    {
        return new EventStreamResponse(function (): iterable {
            foreach ($this->getNotifications() as $notification) {
                yield new ServerEvent($notification->toJson());

                sleep(1); // simulate a delay between events
            }
        });
    }

The :class:`Symfony\\Component\\HttpFoundation\\ServerEvent` class is a DTO
that represents an SSE event following `the WHATWG SSE specification`_. You can
customize each event using its constructor arguments::

    // basic event with only data
    yield new ServerEvent('Some message');

    // event with a custom type (clients listen via addEventListener('my-event', ...))
    yield new ServerEvent(
        data: json_encode(['status' => 'completed']),
        type: 'my-event'
    );

    // event with an ID (useful for resuming streams with the Last-Event-ID header)
    yield new ServerEvent(
        data: 'Update content',
        id: 'event-123'
    );

    // event that tells the client to retry after a specific time (in milliseconds)
    yield new ServerEvent(
        data: 'Retry info',
        retry: 5000
    );

    // event with a comment (can be used for keep-alive)
    yield new ServerEvent(comment: 'keep-alive');

For use cases where generators are not practical, you can use the
:method:`Symfony\\Component\\HttpFoundation\\EventStreamResponse::sendEvent`
method for manual control::

    use Symfony\Component\HttpFoundation\EventStreamResponse;
    use Symfony\Component\HttpFoundation\ServerEvent;

    // ...

    public function liveProgress(): EventStreamResponse
    {
        return new EventStreamResponse(function (EventStreamResponse $response): void {
            $redis = new \Redis();
            $redis->connect('127.0.0.1');
            $redis->subscribe(['message'], function (/* ... */, string $message) use ($response): void {
                $response->sendEvent(new ServerEvent($message));
            });
        });
    }

On the client side, you can listen to events using the native ``EventSource`` API:

.. code-block:: javascript

    const eventSource = new EventSource('/live-notifications');

    // listen to all events (without a specific type)
    eventSource.onmessage = (event) => {
        console.log('Received:', event.data);
    };

    // listen to events with a specific type
    eventSource.addEventListener('my-event', (event) => {
        console.log('My event:', JSON.parse(event.data));
    });

    // handle connection errors
    eventSource.onerror = (error) => {
        console.error('SSE error:', error);
        eventSource.close();
    };

.. warning::

    ``EventStreamResponse`` is designed for applications with limited concurrent
    connections. Because SSE keeps HTTP connections open, it consumes server
    resources (memory and connection limits) for each connected client.

    For high-traffic applications that need to broadcast updates to many clients
    simultaneously, consider using :doc:`Mercure </mercure>`, which is built on
    top of SSE but uses a dedicated hub to manage connections efficiently.

Decoupling Controllers from Symfony
-----------------------------------

Extending the :ref:`AbstractController base class <the-base-controller-class-services>`
simplifies controller development and is **recommended for most applications**.
However, some advanced users prefer to fully decouple your controllers from Symfony
(for example, to improve testability or to follow a more framework-agnostic design)
Symfony provides tools to help you do that.

To decouple controllers, Symfony exposes all the helpers from ``AbstractController``
through another class called :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerHelper`,
where each helper is available as a public method::

    use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
    use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;
    use Symfony\Component\HttpFoundation\Response;

    class MyController
    {
        public function __construct(
            #[AutowireMethodOf(ControllerHelper::class)]
            private \Closure $render,
            #[AutowireMethodOf(ControllerHelper::class)]
            private \Closure $redirectToRoute,
        ) {
        }

        public function showProduct(int $id): Response
        {
            if (!$id) {
                return ($this->redirectToRoute)('product_list');
            }

            return ($this->render)('product/show.html.twig', ['product_id' => $id]);
        }
    }

You can inject the entire ``ControllerHelper`` class if you prefer, but using the
:ref:`AutowireMethodOf <autowiring_closures>` attribute as in the previous example,
lets you inject only the exact helpers you need, making your code more efficient.

Since ``#[AutowireMethodOf]`` also works with interfaces, you can define interfaces
for these helper methods::

    interface RenderInterface
    {
        // this is the signature of the render() helper
        public function __invoke(string $view, array $parameters = [], ?Response $response = null): Response;
    }

Then, update your controller to use the interface instead of a closure::

    use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
    use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

    class MyController
    {
        public function __construct(
            #[AutowireMethodOf(ControllerHelper::class)]
            private RenderInterface $render,
        ) {
        }

        // ...
    }

Using interfaces like in the previous example provides full static analysis and
autocompletion benefits with no extra boilerplate code.

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
    :maxdepth: 1
    :glob:

    controller/*

.. _`Symfony Maker`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`unvalidated redirects security vulnerability`: https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html
.. _`Validate Filters`: https://www.php.net/manual/en/filter.constants.php
.. _`phpstan/phpdoc-parser`: https://packagist.org/packages/phpstan/phpdoc-parser
.. _`phpdocumentor/type-resolver`: https://packagist.org/packages/phpdocumentor/type-resolver
.. _`Server-Sent Events (SSE)`: https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events
.. _`the WHATWG SSE specification`: https://html.spec.whatwg.org/multipage/server-sent-events.html
