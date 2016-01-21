.. index::
   single: Controller; Customize error pages
   single: Error pages

How to Customize Error Pages
============================

In Symfony applications, all errors are treated as exceptions, no matter if they
are just a 404 Not Found error or a fatal error triggered by throwing some
exception in your code.

In the :doc:`development environment </cookbook/configuration/environments>`,
Symfony catches all the exceptions and displays a special **exception page**
with lots of debug information to help you quickly discover the root problem:

.. image:: /images/cookbook/controller/error_pages/exceptions-in-dev-environment.png
   :alt: A typical exception page in the development environment

Since these pages contain a lot of sensitive internal information, Symfony won't
display them in the production environment. Instead, it'll show a simple and
generic **error page**:

.. image:: /images/cookbook/controller/error_pages/errors-in-prod-environment.png
   :alt: A typical error page in the production environment

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

.. _cookbook-error-pages-by-status-code:

This controller uses the HTTP status code, the request format and the following
logic to determine the template filename:

#. Look for a template for the given format and status code (like ``error404.json.twig``
   or ``error500.html.twig``);

#. If the previous template doesn't exist, discard the status code and look for
   a generic template for the given format (like ``error.json.twig`` or
   ``error.xml.twig``);

#. If none of the previous template exist, fall back to the generic HTML template
   (``error.html.twig``).

.. _overriding-or-adding-templates:

To override these templates, simply rely on the standard Symfony method for
:ref:`overriding templates that live inside a bundle <overriding-bundle-templates>`:
put them in the ``app/Resources/TwigBundle/views/Exception/`` directory.

A typical project that returns HTML and JSON pages, might look like this:

.. code-block:: text

    app/
    └─ Resources/
       └─ TwigBundle/
          └─ views/
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
``error404.html.twig`` template located at ``app/Resources/TwigBundle/views/Exception/``:

.. code-block:: html+twig

    {# app/Resources/TwigBundle/views/Exception/error404.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Page not found</h1>

        {# example security usage, see below #}
        {% if is_granted('IS_AUTHENTICATED_FULLY') %}
            {# ... #}
        {% endif %}

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

.. _testing-error-pages:

Testing Error Pages during Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

While you're in the development environment, Symfony shows the big *exception*
page instead of your shiny new customized error page. So, how can you see
what it looks like and debug it?

Fortunately, the default ``ExceptionController`` allows you to preview your
*error* pages during development.

To use this feature, you need to have a definition in your
``routing_dev.yml`` file like so:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing_dev.yml
        _errors:
            resource: "@TwigBundle/Resources/config/routing/errors.xml"
            prefix:   /_error

    .. code-block:: xml

        <!-- app/config/routing_dev.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="@TwigBundle/Resources/config/routing/errors.xml"
                prefix="/_error" />
        </routes>

    .. code-block:: php

        // app/config/routing_dev.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection(
            $loader->import('@TwigBundle/Resources/config/routing/errors.xml')
        );
        $collection->addPrefix("/_error");

        return $collection;

If you're coming from an older version of Symfony, you might need to
add this to your ``routing_dev.yml`` file. If you're starting from
scratch, the `Symfony Standard Edition`_ already contains it for you.

With this route added, you can use URLs like

.. code-block:: text

     http://localhost/app_dev.php/_error/{statusCode}
     http://localhost/app_dev.php/_error/{statusCode}.{format}

to preview the *error* page for a given status code as HTML or for a
given status code and format.

.. _custom-exception-controller:
.. _replacing-the-default-exceptioncontroller:

Overriding the Default ExceptionController
------------------------------------------

If you need a little more flexibility beyond just overriding the template,
then you can change the controller that renders the error page. For example,
you might need to pass some additional variables into your template.

To do this, simply create a new controller anywhere in your application and set
the :ref:`twig.exception_controller <config-twig-exception-controller>`
configuration option to point to it:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            exception_controller:  AppBundle:Exception:showException

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:exception-controller>AppBundle:Exception:showException</twig:exception-controller>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'exception_controller' => 'AppBundle:Exception:showException',
            // ...
        ));

The :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`
class used by the TwigBundle as a listener of the ``kernel.exception`` event creates
the request that will be dispatched to your controller. In addition, your controller
will be passed two parameters:

``exception``
    A :class:`\\Symfony\\Component\\Debug\\Exception\\FlattenException`
    instance created from the exception being handled.

``logger``
    A :class:`\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface`
    instance which may be ``null`` in some circumstances.

Instead of creating a new exception controller from scratch you can, of course,
also extend the default :class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`.
In that case, you might want to override one or both of the ``showAction()`` and
``findTemplate()`` methods. The latter one locates the template to be used.

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

:doc:`Writing your own event listener </cookbook/event_dispatcher/event_listener>`
for the ``kernel.exception`` event allows you to have a closer look at the exception
and take different actions depending on it. Those actions might include logging
the exception, redirecting the user to another page or rendering specialized
error pages.

.. note::

    If your listener calls ``setResponse()`` on the
    :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`,
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

.. _`WebfactoryExceptionsBundle`: https://github.com/webfactory/exceptions-bundle
.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard/
.. _`ExceptionListener`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Http/Firewall/ExceptionListener.php
.. _`development environment`: http://symfony.com/doc/current/cookbook/configuration/environments.html
