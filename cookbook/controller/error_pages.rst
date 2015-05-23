.. index::
   single: Controller; Customize error pages
   single: Error pages

How to Customize Error Pages
============================

When an exception is thrown, the core ``HttpKernel`` class catches it and
dispatches a ``kernel.exception`` event. This gives you the power to convert
the exception into a ``Response`` in a few different ways.

The core TwigBundle sets up a listener for this event which will run
a configurable (but otherwise arbitrary) controller to generate the
response. The default controller used has a sensible way of
picking one out of the available set of error templates.

Thus, error pages can be customized in different ways, depending on how
much control you need:

#. :ref:`Use the default ExceptionController and create a few
   templates that allow you to customize how your different error
   pages look (easy); <use-default-exception-controller>`

#. :ref:`Replace the default exception controller with your own
   (intermediate). <custom-exception-controller>`

#. :ref:`Use the kernel.exception event to come up with your own
   handling (advanced). <use-kernel-exception-event>`

.. _use-default-exception-controller:

Using the Default ExceptionController
-------------------------------------

By default, the ``showAction()`` method of the
:class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`
will be called when an exception occurs.

This controller will either display an
*exception* or *error* page, depending on the setting of the ``kernel.debug``
flag. While *exception* pages give you a lot of helpful
information during development, *error* pages are meant to be
shown to the user in production.

.. tip::

    You can also :ref:`preview your error pages <testing-error-pages>`
    in ``kernel.debug`` mode.

.. _cookbook-error-pages-by-status-code:

How the Template for the Error and Exception Pages Is Selected
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The TwigBundle contains some default templates for error and
exception pages in its ``Resources/views/Exception`` directory.

.. tip::

    In a standard Symfony installation, the TwigBundle can be found at
    ``vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle``. In addition
    to the standard HTML error page, it also provides a default
    error page for many of the most common response formats, including
    JSON (``error.json.twig``), XML (``error.xml.twig``) and even
    JavaScript (``error.js.twig``), to name a few.

Here is how the ``ExceptionController`` will pick one of the
available templates based on the HTTP status code and request format:

* For *error* pages, it first looks for a template for the given format
  and status code (like ``error404.json.twig``);

* If that does not exist or apply, it looks for a general template for
  the given format (like ``error.json.twig`` or
  ``exception.json.twig``);

* Finally, it ignores the format and falls back to the HTML template
  (like ``error.html.twig`` or ``exception.html.twig``).

.. tip::

    If the exception being handled implements the
    :class:`Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface`,
    the ``getStatusCode()`` method will be
    called to obtain the HTTP status code to use. Otherwise,
    the status code will be "500".

Overriding or Adding Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To override these templates, simply rely on the standard method for
overriding templates that live inside a bundle. For more information,
see :ref:`overriding-bundle-templates`.

For example, to override the default error template, create a new
template located at
``app/Resources/TwigBundle/views/Exception/error.html.twig``:

.. code-block:: html+jinja

    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>An Error Occurred: {{ status_text }}</title>
    </head>
    <body>
        <h1>Oops! An Error Occurred</h1>
        <h2>The server returned a "{{ status_code }} {{ status_text }}".</h2>
    </body>
    </html>

.. caution::

    You **must not** use ``is_granted`` in your error pages (or layout used
    by your error pages), because the router runs before the firewall. If
    the router throws an exception (for instance, when the route does not
    match), then using ``is_granted`` will throw a further exception. You
    can use ``is_granted`` safely by saying ``{% if app.user and is_granted('...') %}``.

.. tip::

    If you're not familiar with Twig, don't worry. Twig is a simple,
    powerful and optional templating engine that integrates with
    Symfony. For more information about Twig see :doc:`/book/templating`.

This works not only to replace the default templates, but also to add
new ones.

For instance, create an ``app/Resources/TwigBundle/views/Exception/error404.html.twig``
template to display a special page for 404 (page not found) errors.
Refer to the previous section for the order in which the
``ExceptionController`` tries different template names.

.. tip::

    Often, the easiest way to customize an error page is to copy it from
    the TwigBundle into ``app/Resources/TwigBundle/views/Exception`` and
    then modify it.

.. note::

    The debug-friendly exception pages shown to the developer can even be
    customized in the same way by creating templates such as
    ``exception.html.twig`` for the standard HTML exception page or
    ``exception.json.twig`` for the JSON exception page.

.. _testing-error-pages:

Testing Error Pages during Development
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default ``ExceptionController`` also allows you to preview your
*error* pages during development.

.. versionadded:: 2.6
    This feature was introduced in Symfony 2.6. Before, the third-party
    `WebfactoryExceptionsBundle`_ could be used for the same purpose.

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

Replacing the Default ExceptionController
------------------------------------------

If you need a little more flexibility beyond just overriding the
template, then you can change the controller that renders the error
page. For example, you might need to pass some additional variables into
your template.

.. caution::

    Make sure you don't lose the exception pages that render the helpful
    error messages during development.

To do this, simply create a new controller and set the
:ref:`twig.exception_controller <config-twig-exception-controller>` option
to point to it.

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

.. tip::

    You can also set up your controller as a service.

    The default value of ``twig.controller.exception:showAction`` refers
    to the ``showAction`` method of the ``ExceptionController``
    described previously, which is registered in the DIC as the
    ``twig.controller.exception`` service.

Your controller will be passed two parameters: ``exception``,
which is a :class:`\\Symfony\\Component\\Debug\\Exception\\FlattenException`
instance created from the exception being handled, and ``logger``,
an instance of :class:`\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface`
(which may be ``null``).

.. tip::

    The Request that will be dispatched to your controller is created
    in the :class:`Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener`.
    This event listener is set up by the TwigBundle.

You can, of course, also extend the previously described
:class:`Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController`.
In that case, you might want to override one or both of the
``showAction`` and ``findTemplate`` methods. The latter one locates the
template to be used.

.. caution::

    As of writing, the ``ExceptionController`` is *not* part of the
    Symfony API, so be aware that it might change in following releases.

.. tip::

    The :ref:`error page preview <testing-error-pages>` also works for
    your own controllers set up this way.

.. _use-kernel-exception-event:

Working with the kernel.exception Event
-----------------------------------------

As mentioned in the beginning, the ``kernel.exception`` event is
dispatched whenever the Symfony Kernel needs to
handle an exception. For more information on that, see :ref:`kernel-kernel.exception`.

Working with this event is actually much more powerful than what has
been explained before but also requires a thorough understanding of
Symfony internals.

To give one example, assume your application throws
specialized exceptions with a particular meaning to your domain.

In that case, all the default ``ExceptionListener`` and
``ExceptionController`` could do for you was trying to figure out the
right HTTP status code and display your nice-looking error page.

:doc:`Writing your own event listener </cookbook/service_container/event_listener>`
for the ``kernel.exception`` event allows you to have a closer look
at the exception and take different actions depending on it. Those
actions might include logging the exception, redirecting the user to
another page or rendering specialized error pages.

.. note::

    If your listener calls ``setResponse()`` on the
    :class:`Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent`,
    event propagation will be stopped and the response will be sent to
    the client.

This approach allows you to create centralized and layered error
handling: Instead of catching (and handling) the same exceptions
in various controllers again and again, you can have just one (or
several) listeners deal with them.

.. tip::

    To see an example, have a look at the `ExceptionListener`_ in the
    Security Component.

    It handles various security-related exceptions that are thrown in
    your application (like :class:`Symfony\\Component\\Security\\Core\\Exception\\AccessDeniedException`)
    and takes measures like redirecting the user to the login page,
    logging them out and other things.

Good luck!

.. _`WebfactoryExceptionsBundle`: https://github.com/webfactory/exceptions-bundle
.. _`Symfony Standard Edition`: https://github.com/symfony/symfony-standard/
.. _`ExceptionListener`: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Http/Firewall/ExceptionListener.php
