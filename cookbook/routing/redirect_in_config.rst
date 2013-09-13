.. index::
   single: Routing; Configure redirect to another route without a custom controller

How to configure a redirect to another route without a custom controller
========================================================================

This guide explains how to configure a redirect from one route to another
without using a custom controller.

Assume that there is no useful default controller for the ``/`` path of
your application and you want to redirect these requests to ``/app``.

Your configuration will look like this:

.. code-block:: yaml

    AppBundle:
        resource: "@App/Controller/"
        type:     annotation
        prefix:   /app

    root:
        path:     /
        defaults:
            _controller: FrameworkBundle:Redirect:urlRedirect
            path: /app
            permanent: true

In this example, you configure a route for the ``/`` path and let :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController`
handle it. This controller comes standard with Symfony and offers two actions
for redirecting request:

* ``urlRedirect`` redirects to another *path*. You must provide the ``path``
  parameter containing the path of the resource you want to redirect to.

* ``redirect`` (not shown here) redirects to another *route*. You must provide the ``route``
  parameter with the *name* of the route you want to redirect to.

The ``permanent`` switch tells both methods to issue a 301 HTTP status code
instead of the default ``302`` status code.
