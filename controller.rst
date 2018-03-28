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

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

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
    use Symfony\Component\Routing\Annotation\Route;

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

.. _the-base-controller-class-services:

The Base Controller Classes & Services
--------------------------------------

For convenience, Symfony comes with two optional base
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` and
:class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController`
classes. You can extend either to get access to a number of `helper methods`_.

Add the ``use`` statement atop the ``Controller`` class and then modify
``LuckyController`` to extend it::

    // src/AppBundle/Controller/LuckyController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class LuckyController extends Controller
    {
        // ...
    }

That's it! You now have access to methods like :ref:`$this->render() <controller-rendering-templates>`
and many others that you'll learn about next.

.. _controller-abstract-versus-controller:

.. tip::

    You can extend either ``Controller`` or ``AbstractController``. The difference
    is that when you extend ``AbstractController``, you can't access services directly
    via ``$this->get()`` or ``$this->container->get()``. This forces you to write
    more robust code to access services. But if you *do* need direct access to the
    container, using ``Controller`` is fine.

.. versionadded:: 3.3
    The ``AbstractController`` class was added in Symfony 3.3.

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
        // redirects to the "homepage" route
        return $this->redirectToRoute('homepage');

        // does a permanent - 301 redirect
        return $this->redirectToRoute('homepage', array(), 301);

        // redirects to a route with parameters
        return $this->redirectToRoute('blog_show', array('slug' => 'my-page'));

        // redirects externally
        return $this->redirect('http://symfony.com/doc');
    }

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

Fetching Services as Controller Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 3.3
    The ability to type-hint a controller argument in order to receive a service
    was added in Symfony 3.3.

Symfony comes *packed* with a lot of useful objects, called :doc:`services </service_container>`.
These are used for rendering templates, sending emails, querying the database and
any other "work" you can think of.

If you need a service in a controller, just type-hint an argument with its class
(or interface) name. Symfony will automatically pass you the service you need::

    use Psr\Log\LoggerInterface
    // ...

    /**
     * @Route("/lucky/number/{max}")
     */
    public function numberAction($max, LoggerInterface $logger)
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

        # app/config/services.yml
        services:
            # ...

            # explicitly configure the service
            AppBundle\Controller\LuckyController:
                public: true
                bind:
                    # for any $logger argument, pass this specific service
                    $logger: '@monolog.logger.doctrine'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- Explicitly configure the service -->
                <service id="AppBundle\Controller\LuckyController" public="true">
                    <bind key="$logger"
                        type="service"
                        id="monolog.logger.doctrine"
                    />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Controller\LuckyController;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(LuckyController::class)
            ->setPublic(true)
            ->setBindings(array(
                '$logger' => new Reference('monolog.logger.doctrine'),
            ))
        ;

You can of course also use normal :ref:`constructor injection <services-constructor-injection>`
in your controllers.

.. caution::

    You can *only* pass *services* to your controller arguments in this way. It's not
    possible, for example, to pass a service parameter as a controller argument,
    even by using ``bind``. If you need a parameter, use the ``$this->getParameter('kernel.debug')``
    shortcut or pass the value through your controller's ``__construct()`` method
    and specify its value with ``bind``.

For more information about services, see the :doc:`/service_container` article.

.. _controller-service-arguments-tag:

.. note::
    If this isn't working, make sure your controller is registered as a service,
    is :ref:`autoconfigured <services-autoconfigure>` and extends either
    :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller` or
    :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController`. If
    you use the :ref:`services.yml configuration from the Symfony Standard Edition <service-container-services-load-example>`,
    then your controllers are already registered as services and autoconfigured.

    If you're not using the default configuration, you can tag your service manually
    with ``controller.service_arguments``.

.. _accessing-other-services:
.. _controller-access-services-directly:

Accessing the Container Directly
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you extend the base ``Controller`` class, you can access any Symfony service
via the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::get`
method. Here are several common services you might need::

    $templating = $this->get('templating');

    $router = $this->get('router');

    $mailer = $this->get('mailer');

    // you can also fetch parameters
    $someParameter = $this->getParameter('some_parameter');

If you receive an error like:

.. code-block:: text

    You have requested a non-existent service "my_service_id"

Check to make sure the service exists (use :ref:`debug:container <container-debug-container>`)
and that it's :ref:`public <container-public>`.

.. index::
   single: Controller; Managing errors
   single: Controller; 404 pages

Managing Errors and 404 Pages
-----------------------------

When things are not found, you should play well with the HTTP protocol and
return a 404 response. To do this, you'll throw a special type of exception.
If you're extending the base ``Controller`` or the base ``AbstractController``
class, do the following::

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
Symfony will automatically return a 500 HTTP response code::

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

.. versionadded:: 3.3
    The ability to request a ``Session`` instance in controllers was introduced
    in Symfony 3.3.

To retrieve the session, add the :class:`Symfony\\Component\\HttpFoundation\\Session\\SessionInterface`
type-hint to your argument and Symfony will provide you with a session::

    use Symfony\Component\HttpFoundation\Session\SessionInterface;

    public function indexAction(SessionInterface $session)
    {
        // stores an attribute for reuse during a later user request
        $session->set('foo', 'bar');

        // gets the attribute set by another controller in another request
        $foobar = $session->get('foobar');

        // uses a default value if the attribute doesn't exist
        $filters = $session->get('filters', array());
    }

Stored attributes remain in the session for the remainder of that user's session.

.. tip::

    Every ``SessionInterface`` implementation is supported. If you have your
    own implementation, type-hint this in the arguments instead.

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
read any flash messages from the session using ``app.flashes()``:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/base.html.twig #}

        {# you can read and display just one flash message type... #}
        {% for message in app.flashes('notice') %}
            <div class="flash-notice">
                {{ message }}
            </div>
        {% endfor %}

        {# ...or you can read and display every flash message available #}
        {% for label, messages in app.flashes %}
            {% for message in messages %}
                <div class="flash-{{ label }}">
                    {{ message }}
                </div>
            {% endfor %}
        {% endfor %}

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->

        // you can read and display just one flash message type...
        <?php foreach ($view['session']->getFlashBag()->get('notice') as $message): ?>
            <div class="flash-notice">
                <?php echo $message ?>
            </div>
        <?php endforeach ?>

        // ...or you can read and display every flash message available
        <?php foreach ($view['session']->getFlashBag()->all() as $type => $flash_messages): ?>
            <?php foreach ($flash_messages as $flash_message): ?>
                <div class="flash-<?php echo $type ?>">
                    <?php echo $message ?>
                </div>
            <?php endforeach ?>
        <?php endforeach ?>

.. versionadded:: 3.3
    The ``app.flashes()`` Twig function was introduced in Symfony 3.3. Prior,
    you had to use ``app.session.flashBag()``.

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

    // creates a simple Response with a 200 status code (the default)
    $response = new Response('Hello '.$name, Response::HTTP_OK);

    // creates a CSS-response with a 200 status code
    $response = new Response('<style> ... </style>');
    $response->headers->set('Content-Type', 'text/css');

There are special classes that make certain kinds of responses easier:

* For files, there is :class:`Symfony\\Component\\HttpFoundation\\BinaryFileResponse`.
  See :ref:`component-http-foundation-serving-files`.

* For streamed responses, there is
  :class:`Symfony\\Component\\HttpFoundation\\StreamedResponse`.
  See :ref:`streaming-response`.

.. seealso::

    Now that you know the basics you can continue your research on Symfony
    ``Request`` and ``Response`` object in the
    :ref:`HttpFoundation component documentation <component-http-foundation-request>`.

JSON Helper
~~~~~~~~~~~

To return JSON from a controller, use the ``json()`` helper method on the base controller.
This returns a special ``JsonResponse`` object that encodes the data automatically::

    // ...
    public function indexAction()
    {
        // returns '{"username":"jane.doe"}' and sets the proper Content-Type header
        return $this->json(array('username' => 'jane.doe'));

        // the shortcut defines three optional arguments
        // return $this->json($data, $status = 200, $headers = array(), $context = array());
    }

If the :doc:`serializer service </serializer>` is enabled in your
application, contents passed to ``json()`` are encoded with it. Otherwise,
the :phpfunction:`json_encode` function is used.

File helper
~~~~~~~~~~~

.. versionadded:: 3.2
    The ``file()`` helper was introduced in Symfony 3.2.

You can use the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::file`
helper to serve a file from inside a controller::

    public function fileAction()
    {
        // send the file contents and force the browser to download it
        return $this->file('/path/to/some_file.pdf');
    }

The ``file()`` helper provides some arguments to configure its behavior::

    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\ResponseHeaderBag;

    public function fileAction()
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

To make life easier, you'll probably extend the base ``Controller`` class because
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
.. _`unvalidated redirects security vulnerability`: https://www.owasp.org/index.php/Open_redirect
