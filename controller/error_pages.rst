.. index::
   single: Controller; Customize error pages
   single: Error pages

How to Customize Error Pages
============================

In Symfony applications, all errors are treated as exceptions, no matter if they
are a 404 Not Found error or a fatal error triggered by throwing some exception
in your code.

In the :ref:`development environment <configuration-environments>`,
Symfony catches all the exceptions and displays a special **exception page**
with lots of debug information to help you discover the root problem:

.. image:: /_images/controller/error_pages/exceptions-in-dev-environment.png
   :alt: A typical exception page in the development environment
   :align: center
   :class: with-browser

Since these pages contain a lot of sensitive internal information, Symfony won't
display them in the production environment. Instead, it'll show a minimal and
generic **error page**:

.. image:: /_images/controller/error_pages/errors-in-prod-environment.png
   :alt: A typical error page in the production environment
   :align: center
   :class: with-browser

Error pages for the production environment can be customized in different ways
depending on your needs:

#. If you only want to change the contents and styles of the error pages to match
   the rest of your application, :ref:`override the default error templates <use-default-error-controller>`;

#. If you want to change the contents of non-HTML error output,
   :ref:`create a new normalizer <overriding-non-html-error-output>`;

#. If you also want to tweak the logic used by Symfony to generate error pages,
   :ref:`override the default error controller <custom-error-controller>`;

#. If you need total control of exception handling to run your own logic
   :ref:`use the kernel.exception event <use-kernel-exception-event>`.

.. _use-default-error-controller:
.. _using-the-default-errorcontroller:

Overriding the Default Error Templates
--------------------------------------

You can use the built-in Twig error renderer to override the default error
templates. Both the TwigBundle and TwigBridge need to be installed for this. Run
this command to ensure both are installed:

.. code-block:: terminal

    $ composer require symfony/twig-pack

When the error page loads, :class:`Symfony\\Bridge\\Twig\\ErrorRenderer\\TwigErrorRenderer`
is used to render a Twig template to show the user.

.. _controller-error-pages-by-status-code:

This renderer uses the HTTP status code and the following
logic to determine the template filename:

#. Look for a template for the given status code (like ``error500.html.twig``);

#. If the previous template doesn't exist, discard the status code and look for
   a generic error template (``error.html.twig``).

.. _overriding-or-adding-templates:

To override these templates, rely on the standard Symfony method for
:ref:`overriding templates that live inside a bundle <override-templates>` and
put them in the ``templates/bundles/TwigBundle/Exception/`` directory.

A typical project that returns HTML pages might look like this:

.. code-block:: text

    templates/
    └─ bundles/
       └─ TwigBundle/
          └─ Exception/
             ├─ error404.html.twig
             ├─ error403.html.twig
             └─ error.html.twig      # All other HTML errors (including 500)

Example 404 Error Template
--------------------------

To override the 404 error template for HTML pages, create a new
``error404.html.twig`` template located at ``templates/bundles/TwigBundle/Exception/``:

.. code-block:: html+twig

    {# templates/bundles/TwigBundle/Exception/error404.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Page not found</h1>

        <p>
            The requested page couldn't be located. Checkout for any URL
            misspelling or <a href="{{ path('homepage') }}">return to the homepage</a>.
        </p>
    {% endblock %}

In case you need them, the ``TwigErrorRenderer`` passes some information to
the error template via the ``status_code`` and ``status_text`` variables that
store the HTTP status code and message respectively.

.. tip::

    You can customize the status code of an exception by implementing
    :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface`
    and its required ``getStatusCode()`` method. Otherwise, the ``status_code``
    will default to ``500``.

Additionally you have access to the Exception with ``exception``, which for example
allows you to output the stack trace using ``{{ exception.traceAsString }}`` or
access any other method on the object. You should be careful with this though,
as this is very likely to expose sensitive data.

.. tip::

    PHP errors are turned into exceptions as well by default, so you can also
    access these error details using ``exception``.

Security & 404 Pages
--------------------

Due to the order of how routing and security are loaded, security information will
*not* be available on your 404 pages. This means that it will appear as if your
user is logged out on the 404 page (it will work while testing, but not on production).

.. _testing-error-pages:

Testing Error Pages during Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While you're in the development environment, Symfony shows the big *exception*
page instead of your shiny new customized error page. So, how can you see
what it looks like and debug it?

Fortunately, the default ``ErrorController`` allows you to preview your
*error* pages during development.

To use this feature, you need to load some special routes provided by FrameworkBundle
(if the application uses :ref:`Symfony Flex <symfony-flex>` they are loaded
automatically when installing ``symfony/framework-bundle``):

.. configuration-block::

    .. code-block:: yaml

        # config/routes/framework.yaml
        when@dev:
            _errors:
                resource: '@FrameworkBundle/Resources/config/routing/errors.xml'
                prefix:   /_error

    .. code-block:: xml

        <!-- config/routes/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <when env="dev">
                <import resource="@FrameworkBundle/Resources/config/routing/errors.xml" prefix="/_error"/>
            </when>
        </routes>

    .. code-block:: php

        // config/routes/framework.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            if ('dev' === $routes->env()) {
                $routes->import('@FrameworkBundle/Resources/config/routing/errors.xml')
                    ->prefix('/_error')
                ;
            }
        };

With this route added, you can use URLs like these to preview the *error* page
for a given status code as HTML or for a given status code and format (you might
need to replace ``http://localhost/`` by the host used in your local setup):

* ``http://localhost/_error/{statusCode}`` for HTML
* ``http://localhost/_error/{statusCode}.{format}`` for any other format

.. _overriding-non-html-error-output:

Overriding Error output for non-HTML formats
--------------------------------------------

To override non-HTML error output, the Serializer component needs to be installed.

.. code-block:: terminal

    $ composer require symfony/serializer-pack

The Serializer component has a built-in ``FlattenException`` normalizer
(:class:`Symfony\\Component\\Serializer\\Normalizer\\ProblemNormalizer`) and
JSON/XML/CSV/YAML encoders. When your application throws an exception, Symfony
can output it in one of those formats. If you want to change the output
contents, create a new Normalizer that supports the ``FlattenException`` input::

    # src/Serializer/MyCustomProblemNormalizer.php
    namespace App\Serializer;

    use Symfony\Component\ErrorHandler\Exception\FlattenException;
    use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

    class MyCustomProblemNormalizer implements NormalizerInterface
    {
        public function normalize($exception, string $format = null, array $context = []): array
        {
            return [
                'content' => 'This is my custom problem normalizer.',
                'exception'=> [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getStatusCode(),
                ],
            ];
        }

        public function supportsNormalization($data, string $format = null, array $context = []): bool
        {
            return $data instanceof FlattenException;
        }
    }

.. _custom-error-controller:
.. _replacing-the-default-errorcontroller:

Overriding the Default ErrorController
--------------------------------------

If you need a little more flexibility beyond just overriding the template,
then you can change the controller that renders the error page. For example,
you might need to pass some additional variables into your template.

To do this, create a new controller anywhere in your application and set
the :ref:`framework.error_controller <config-framework-error_controller>`
configuration option to point to it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            error_controller: App\Controller\ErrorController::show

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <framework:config>
                <framework:error-controller>App\Controller\ErrorController::show</framework:error-controller>
            </framework:config>

        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->errorController('App\Controller\ErrorController::show');
        };

The :class:`Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener`
class used by the FrameworkBundle as a listener of the ``kernel.exception`` event creates
the request that will be dispatched to your controller. In addition, your controller
will be passed two parameters:

``exception``
    The original :phpclass:`Throwable` instance being handled.

``logger``
    A :class:`\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface`
    instance which may be ``null`` in some circumstances.

.. tip::

    The :ref:`error page preview <testing-error-pages>` also works for
    your own controllers set up this way.

.. _use-kernel-exception-event:

Working with the ``kernel.exception`` Event
-------------------------------------------

When an exception is thrown, the :class:`Symfony\\Component\\HttpKernel\\HttpKernel`
class catches it and dispatches a ``kernel.exception`` event. This gives you the
power to convert the exception into a ``Response`` in a few different ways.

Working with this event is actually much more powerful than what has been explained
before, but also requires a thorough understanding of Symfony internals. Suppose
that your code throws specialized exceptions with a particular meaning to your
application domain.

:doc:`Writing your own event listener </event_dispatcher>`
for the ``kernel.exception`` event allows you to have a closer look at the exception
and take different actions depending on it. Those actions might include logging
the exception, redirecting the user to another page or rendering specialized
error pages.

.. note::

    If your listener calls ``setThrowable()`` on the
    :class:`Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent`
    event, propagation will be stopped and the response will be sent to
    the client.

This approach allows you to create centralized and layered error handling:
instead of catching (and handling) the same exceptions in various controllers
time and again, you can have just one (or several) listeners deal with them.

.. tip::

    See :class:`Symfony\\Component\\Security\\Http\\Firewall\\ExceptionListener`
    class code for a real example of an advanced listener of this type. This
    listener handles various security-related exceptions that are thrown in
    your application (like :class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`)
    and takes measures like redirecting the user to the login page, logging them
    out and other things.
