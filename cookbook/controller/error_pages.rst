.. index::
   single: Controller; Customize error pages
   single: Error pages

How to customize Error Pages
============================

When any exception is thrown in Symfony2, the exception is caught inside the
``Kernel`` class and eventually forwarded to a special controller,
``TwigBundle:Exception:show`` for handling. This controller, which lives
inside the core ``TwigBundle``, determines which error template to display and
the status code that should be set for the given exception.

Error pages can be customized in two different ways, depending on how much
control you need:

1. Customize the error templates of the different error pages (explained below);

2. Replace the default exception controller ``TwigBundle::Exception:show``
   with your own controller and handle it however you want (see
   :ref:`exception_controller in the Twig reference<config-twig-exception-controller>`);

.. tip::

    The customization of exception handling is actually much more powerful
    than what's written here. An internal event, ``kernel.exception``, is thrown
    which allows complete control over exception handling. For more
    information, see :ref:`kernel-kernel.exception`.

All of the error templates live inside ``TwigBundle``. To override the
templates, we simply rely on the standard method for overriding templates that
live inside a bundle. For more information, see
:ref:`overriding-bundle-templates`.

For example, to override the default error template that's shown to the
end-user, create a new template located at
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

.. tip::

    If you're not familiar with Twig, don't worry. Twig is a simple, powerful
    and optional templating engine that integrates with ``Symfony2``. For more
    information about Twig see :doc:`/book/templating`.

In addition to the standard HTML error page, Symfony provides a default error
page for many of the most common response formats, including JSON
(``error.json.twig``), XML (``error.xml.twig``) and even Javascript
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
    ``Resources/views/Exception`` directory of the ``TwigBundle``. In a
    standard Symfony2 installation, the ``TwigBundle`` can be found at
    ``vendor/symfony/src/Symfony/Bundle/TwigBundle``. Often, the easiest way
    to customize an error page is to copy it from the ``TwigBundle`` into
    ``app/Resources/TwigBundle/views/Exception`` and then modify it.

.. note::

    The debug-friendly exception pages shown to the developer can even be
    customized in the same way by creating templates such as
    ``exception.html.twig`` for the standard HTML exception page or
    ``exception.json.twig`` for the JSON exception page.
