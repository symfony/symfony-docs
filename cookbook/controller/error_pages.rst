.. index::
   single: Controller; Customize error pages
   single: Error pages

How to customize Error Pages
============================

When any exception is thrown in Symfony2, the exception is caught inside the
``Kernel`` class and eventually forwarded to a special controller,
``TwigBundle:Exception:show`` for handling. This controller, which lives
inside the core TwigBundle, determines which error template to display and
the status code that should be set for the given exception.

Error pages can be customized in two different ways, depending on how much
control you need:

1. Customize the error templates of the different error pages;

2. Replace the default exception controller ``twig.controller.exception:showAction``.

The default ExceptionController
-------------------------------

The default ``ExceptionController`` will either display an
*exception* or *error* page, depending on the setting of the ``kernel.debug``
flag. While *exception* pages give you a lot of helpful
information during development, *error* pages are meant to be
shown to the end-user.

.. sidebar:: Testing Error Pages during Development

    You should not set ``kernel.debug`` to ``false`` in order to see your
    error pages during development. This will also stop
    Symfony2 from recompiling your twig templates, among other things.

    The third-party `WebfactoryExceptionsBundle`_ provides a special
    test controller that allows you to display your custom error
    pages for arbitrary HTTP status codes even with
    ``kernel.debug`` set to ``true``.

Override Error Templates
------------------------

All of the error templates live inside the TwigBundle. To override the
templates, simply rely on the standard method for overriding templates that
live inside a bundle. For more information, see
:ref:`overriding-bundle-templates`.

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

    If you're not familiar with Twig, don't worry. Twig is a simple, powerful
    and optional templating engine that integrates with Symfony2. For more
    information about Twig see :doc:`/book/templating`.

In addition to the standard HTML error page, Symfony provides a default error
page for many of the most common response formats, including JSON
(``error.json.twig``), XML (``error.xml.twig``) and even JavaScript
(``error.js.twig``), to name a few. To override any of these templates, just
create a new file with the same name in the
``app/Resources/TwigBundle/views/Exception`` directory. This is the standard
way of overriding any template that lives inside a bundle.

.. _cookbook-error-pages-by-status-code:

Customizing the 404 Page and other Error Pages
----------------------------------------------

You can also customize specific error templates according to the HTTP status
code. For instance, create a
``app/Resources/TwigBundle/views/Exception/error404.html.twig`` template to
display a special page for 404 (page not found) errors.

Symfony uses the following algorithm to determine which template to use:

* First, it looks for a template for the given format and status code (like
  ``error404.json.twig``);

* If it does not exist, it looks for a template for the given format (like
  ``error.json.twig``);

* If it does not exist, it falls back to the HTML template (like
  ``error.html.twig``).

.. tip::

    To see the full list of default error templates, see the
    ``Resources/views/Exception`` directory of the TwigBundle. In a
    standard Symfony2 installation, the TwigBundle can be found at
    ``vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle``. Often, the easiest way
    to customize an error page is to copy it from the TwigBundle into
    ``app/Resources/TwigBundle/views/Exception`` and then modify it.

.. note::

    The debug-friendly exception pages shown to the developer can even be
    customized in the same way by creating templates such as
    ``exception.html.twig`` for the standard HTML exception page or
    ``exception.json.twig`` for the JSON exception page.

.. _`WebfactoryExceptionsBundle`: https://github.com/webfactory/exceptions-bundle

Replace the default Exception Controller
----------------------------------------

If you need a little more flexibility beyond just overriding the template
(e.g. you need to pass some additional variables into your template),
then you can override the controller that renders the error page.

The default exception controller is registered as a service - the actual
class is ``Symfony\Bundle\TwigBundle\Controller\ExceptionController``.

To do this, create a new controller class and make it extend Symfony's default
``Symfony\Bundle\TwigBundle\Controller\ExceptionController`` class.

There are several methods you can override to customize different parts of how
the error page is rendered. You could, for example, override the entire
``showAction`` or just the ``findTemplate`` method, which locates which
template should be rendered.

To make Symfony use your exception controller instead of the default, set the
:ref:`twig.exception_controller <config-twig-exception-controller>` option
in app/config/config.yml.

.. tip::

    The customization of exception handling is actually much more powerful
    than what's written here. An internal event, ``kernel.exception``, is thrown
    which allows complete control over exception handling. For more
    information, see :ref:`kernel-kernel.exception`.
