.. index::
   single: Controller; Customize error pages
   single: Error pages

How to Customize Error Pages
============================

In Symfony applications, all errors are treated as exceptions, no matter if they
are just a 404 Not Found error or a fatal error triggered by throwing some
exception in your code.

If your app has the `TwigBundle`_ installed, a special controller handles these
exceptions. This controller displays debug information for errors and allows to
customize error pages, so run this command to make sure the bundle is installed:

.. code-block:: terminal

    $ composer require twig

In the :ref:`development environment <configuration-environments>`,
Symfony catches all the exceptions and displays a special **exception page**
with lots of debug information to help you discover the root problem:

.. image:: /_images/controller/error_pages/exceptions-in-dev-environment.png
   :alt: A typical exception page in the development environment
   :align: center
   :class: with-browser

Since these pages contain a lot of sensitive internal information, Symfony won't
display them in the production environment. Instead, it'll show a simple and
generic **error page**:

.. image:: /_images/controller/error_pages/errors-in-prod-environment.png
   :alt: A typical error page in the production environment
   :align: center
   :class: with-browser

Error pages for the production environment can be customized in different ways
depending on your needs:

#. If you just want to change the contents and styles of the error pages to match
   the rest of your application, :ref:`override the default error templates <use-default-exception-controller>`;

#. If you also want to tweak the logic used by Symfony to generate error pages,
   :ref:`override the default exception controller <custom-exception-controller>`;

#. If you need total control of exception handling to execute your own logic
   :ref:`use the kernel.exception event <use-kernel-exception-event>`.

.. _use-default-exception-controller:
.. _using-the-default-exceptioncontroller:

Overriding the Default Error Templates
--------------------------------------

When the error page loads, an internal :class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`
is used to render a Twig template to show the user.

.. _controller-error-pages-by-status-code:

This controller uses the HTTP status code, the request format and the following
logic to determine the template filename:

#. Look for a template for the given format and status code (like ``error404.json.twig``
   or ``error500.html.twig``);

#. If the previous template doesn't exist, discard the status code and look for
   a generic template for the given format (like ``error.json.twig`` or
   ``error.xml.twig``);

#. If none of the previous templates exist, fall back to the generic HTML template
   (``error.html.twig``).

.. _overriding-or-adding-templates:

To override these templates, rely on the standard Symfony method for
:ref:`overriding templates that live inside a bundle <override-templates>` and
put them in the ``templates/bundles/TwigBundle/Exception/`` directory.

A typical project that returns HTML and JSON pages might look like this:

.. code-block:: text

    templates/
    └─ bundles/
       └─ TwigBundle/
          └─ Exception/
             ├─ error404.html.twig
             ├─ error403.html.twig
             ├─ error.html.twig      # All other HTML errors (including 500)
             ├─ error404.json.twig
             ├─ error403.json.twig
             └─ error.json.twig      # All other JSON errors (including 500)

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

In case you need them, the ``ExceptionController`` passes some information to
the error template via the ``status_code`` and ``status_text`` variables that
store the HTTP status code and message respectively.

.. tip::

    You can customize the status code by implementing
    :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface`
    and its required ``getStatusCode()`` method. Otherwise, the ``status_code``
    will default to ``500``.

.. note::

    The exception pages shown in the development environment can be customized
    in the same way as error pages. Create a new ``exception.html.twig`` template
    for the standard HTML exception page or ``exception.json.twig`` for the JSON
    exception page.

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

Fortunately, the default ``ExceptionController`` allows you to preview your
*error* pages during development.

To use this feature, you need to load some special routes provided by TwigBundle
(if the application uses :ref:`Symfony Flex <symfony-flex>` they are loaded
automatically when installing Twig support):

.. configuration-block::

    .. code-block:: yaml

        # config/routes/dev/twig.yaml
        _errors:
            resource: '@TwigBundle/Resources/config/routing/errors.xml'
            prefix:   /_error

    .. code-block:: xml

        <!-- config/routes/dev/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@TwigBundle/Resources/config/routing/errors.xml" prefix="/_error"/>
        </routes>

    .. code-block:: php

        // config/routes/dev/twig.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('@TwigBundle/Resources/config/routing/errors.xml')
                ->prefix('/_error')
            ;
        };

With this route added, you can use URLs like these to preview the *error* page
for a given status code as HTML or for a given status code and format.

.. code-block:: text

     http://localhost/index.php/_error/{statusCode}
     http://localhost/index.php/_error/{statusCode}.{format}

.. _custom-exception-controller:
.. _replacing-the-default-exceptioncontroller:

Overriding the Default ExceptionController
------------------------------------------

If you need a little more flexibility beyond just overriding the template,
then you can change the controller that renders the error page. For example,
you might need to pass some additional variables into your template.

To do this, create a new controller anywhere in your application and set
the :ref:`twig.exception_controller <config-twig-exception-controller>`
configuration option to point to it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            exception_controller: App\Controller\ExceptionController::showAction

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:exception-controller>App\Controller\ExceptionController::showAction</twig:exception-controller>
            </twig:config>

        </container>

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', [
            'exception_controller' => 'App\Controller\ExceptionController::showAction',
            // ...
        ]);

The :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`
class used by the TwigBundle as a listener of the ``kernel.exception`` event creates
the request that will be dispatched to your controller. In addition, your controller
will be passed two parameters:

``exception``
    A :class:`\\Symfony\\Component\\ErrorRenderer\\Exception\\FlattenException`
    instance created from the exception being handled.

``logger``
    A :class:`\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface`
    instance which may be ``null`` in some circumstances.

Instead of creating a new exception controller from scratch you can also extend
the default :class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`.
In that case, you might want to override one or both of the ``showAction()`` and
``findTemplate()`` methods. The latter one locates the template to be used.

.. note::

    In case of extending the
    :class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController` you
    may configure a service to pass the Twig environment and the ``debug`` flag
    to the constructor.

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                _defaults:
                    # ... be sure autowiring is enabled
                    autowire: true
                # ...

                App\Controller\CustomExceptionController:
                    public: true
                    arguments:
                        $debug: '%kernel.debug%'

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... be sure autowiring is enabled -->
                    <defaults autowire="true"/>
                    <!-- ... -->

                    <service id="App\Controller\CustomExceptionController" public="true">
                        <argument key="$debug">%kernel.debug%</argument>
                    </service>
                </services>

            </container>

        .. code-block:: php

            // config/services.php
            use App\Controller\CustomExceptionController;

            $container->autowire(CustomExceptionController::class)
                ->setArgument('$debug', '%kernel.debug%');

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

    If your listener calls ``setResponse()`` on the
    :class:`Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent`,
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

.. _`TwigBundle`: https://github.com/symfony/twig-bundle
