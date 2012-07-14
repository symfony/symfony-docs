.. index::
   single: Routing; Configure redirect to another route without a custom controller

How to configure a redirect to another route without a custom controller
========================================================================

This guide explains how to configure a redirect from one route to another
without using a custom controller.

Let's assume that there is no useful default controller for the ``/`` path of
your application and you want to redirect theses requests to ``/app``.

Your configuration will look like this:

.. code-block:: yaml

    AppBundle:
        resource: "@App/Controller/"
        type:     annotation
        prefix:   /app

    root:
        pattern: /
        defaults:
            _controller: FrameworkBundle:Redirect:urlRedirect
            path: /app
            permanent: true

Your ``AppBundle`` is registered to handle all requests under ``/app``.

We configure a route for the ``/`` path and let :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController`
handle it. This controller is built-in and offers two methods for redirecting request:

* ``redirect`` redirects to another *route*. You must provide the ``route``
  parameter with the *name* of the route you want to redirect to.

* ``urlRedirect`` redirects to another *path*. You must provide the ``path``
  parameter containing the path of the resource you want to redirect to.

The ``permanent`` switch tells both methods to issue a 301 HTTP status code.
