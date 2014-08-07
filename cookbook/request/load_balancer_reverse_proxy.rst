How to Configure Symfony to Work behind a Load Balancer or Reverse Proxy
========================================================================

When you deploy your application, you may be behind a load balancer (e.g.
an AWS Elastic Load Balancer) or a reverse proxy (e.g. Varnish for
:doc:`caching</book/http_cache>`).

For the most part, this doesn't cause any problems with Symfony. But, when
a request passes through a proxy, certain request information is sent using
special ``X-Forwarded-*`` headers. For example, instead of reading the ``REMOTE_ADDR``
header (which will now be the IP address of your reverse proxy), the user's
true IP will be stored in a ``X-Forwarded-For`` header.

.. tip::

    If your using Symfonys :ref:`AppCache<symfony-gateway-cache>` for caching,
    then you *are* using a reverse proxy with the IP address ``127.0.0.1``.
    You'll need to configure that address as a trusted proxy below.

If you don't configure Symfony to look for these headers, you'll get incorrect
information about the client's IP address, whether or not the client is connecting
via HTTPS, the client's port and the hostname being requested.

Solution: trusted_proxies
-------------------------

This is no problem, but you *do* need to tell Symfony that this is happening
and which reverse proxy IP addresses will be doing this type of thing:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        # ...
        framework:
            trusted_proxies:  [192.0.0.1, 10.0.0.0/8]

    .. code-block:: xml

        <!-- app/config/config.xyml -->
        <framework:config trusted-proxies="192.0.0.1, 10.0.0.0/8">
            <!-- ... -->
        </framework>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'trusted_proxies' => array('192.0.0.1', '10.0.0.0/8'),
        ));

In this example, you're saying that your reverse proxy (or proxies) have
the IP address ``192.0.0.1`` or match the range of IP addresses that use
the CIDR notation ``10.0.0.0/8``. For more details, see :ref:`reference-framework-trusted-proxies`.

That's it! Symfony will now look for the correct ``X-Forwarded-*`` headers
to get information like the client's IP address, host, port and whether or
not the request is using HTTPS.

But I the IP of my Reverse Proxy Changes Constantly!
----------------------------------------------------

Some reverse proxies (like Amazon's Elastic Load Balancers) don't have a
static IP address or even a range that you can target with the CIDR notation.
In this case, you'll need to - *very carefully* - trust *all* proxies.

1. Configure your web server(s) to not respond to traffic from *any* servers
   other than your load balancers. For AWS, this can be done with `security groups`_.

1. Once you've guaranteed that traffic will only come from your trusted reverse
   proxies, configure Symfony to *always* trust incoming request. This is
   done inside of your front controller:

.. code-block:: php

    // web/app.php
    // ...

    Request::setTrustedProxies(array($request->server->get('REMOTE_ADDR')));

    $response = $kernel->handle($request);
    // ...

That's it! It's critical that you prevent traffic from all non-trusted sources.
If you allow outside traffic, they could "spoof" their true true IP address
and other information.

My Reverse Proxy Uses Non-Standard (not X-Forwarded-*) Headers
--------------------------------------------------------------

Most reverse proxies store information on specific ``X-Forwarded-*`` headers.
But if your reverse proxy uses non-standard header names, you can configure
these. See :doc:`/components/http_foundation/trusting_proxies`. The code
for doing this will need to live in your front controller (e.g. ``web/app.php``).

.. _`security groups`: http://docs.aws.amazon.com/ElasticLoadBalancing/latest/DeveloperGuide/using-elb-security-groups.html
