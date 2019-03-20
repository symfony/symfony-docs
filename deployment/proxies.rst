How to Configure Symfony to Work behind a Load Balancer or a Reverse Proxy
==========================================================================

When you deploy your application, you may be behind a load balancer (e.g.
an AWS Elastic Load Balancing) or a reverse proxy (e.g. Varnish for
:doc:`caching</http_cache>`).

For the most part, this doesn't cause any problems with Symfony. But, when
a request passes through a proxy, certain request information is sent using
either the standard ``Forwarded`` header or ``X-Forwarded-*`` headers. For example,
instead of reading the ``REMOTE_ADDR`` header (which will now be the IP address of
your reverse proxy), the user's true IP will be stored in a standard ``Forwarded: for="..."``
header or a ``X-Forwarded-For`` header.

If you don't configure Symfony to look for these headers, you'll get incorrect
information about the client's IP address, whether or not the client is connecting
via HTTPS, the client's port and the hostname being requested.

.. _request-set-trusted-proxies:

Solution: setTrustedProxies()
-----------------------------

To fix this, you need to tell Symfony which reverse proxy IP addresses to trust
and what headers your reverse proxy uses to send information::

    // public/index.php

    // ...
    $request = Request::createFromGlobals();

    // tell Symfony about your reverse proxy
    Request::setTrustedProxies(
        // the IP address (or range) of your proxy
        ['192.0.0.1', '10.0.0.0/8'],

        // trust *all* "X-Forwarded-*" headers
        Request::HEADER_X_FORWARDED_ALL

        // or, if your proxy instead uses the "Forwarded" header
        // Request::HEADER_FORWARDED

        // or, if you're using AWS ELB
        // Request::HEADER_X_FORWARDED_AWS_ELB
    );

The Request object has several ``Request::HEADER_*`` constants that control exactly
*which* headers from your reverse proxy are trusted. The argument is a bit field,
so you can also pass your own value (e.g. ``0b00110``).

But what if the IP of my Reverse Proxy Changes Constantly!
----------------------------------------------------------

Some reverse proxies (like AWS Elastic Load Balancing) don't have a
static IP address or even a range that you can target with the CIDR notation.
In this case, you'll need to - *very carefully* - trust *all* proxies.

#. Configure your web server(s) to *not* respond to traffic from *any* clients
   other than your load balancers. For AWS, this can be done with `security groups`_.

#. Once you've guaranteed that traffic will only come from your trusted reverse
   proxies, configure Symfony to *always* trust incoming request::

       // public/index.php

       // ...
       Request::setTrustedProxies(
           // trust *all* requests
           ['127.0.0.1', $request->server->get('REMOTE_ADDR')],

           // if you're using ELB, otherwise use a constant from above
           Request::HEADER_X_FORWARDED_AWS_ELB
       );

That's it! It's critical that you prevent traffic from all non-trusted sources.
If you allow outside traffic, they could "spoof" their true IP address and
other information.

.. _`security groups`: http://docs.aws.amazon.com/elasticloadbalancing/latest/classic/elb-security-groups.html
.. _`RFC 7239`: http://tools.ietf.org/html/rfc7239
