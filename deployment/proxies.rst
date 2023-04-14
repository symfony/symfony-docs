How to Configure Symfony to Work behind a Load Balancer or a Reverse Proxy
==========================================================================

When you deploy your application, you may be behind a load balancer (e.g.
an AWS Elastic Load Balancing) or a reverse proxy (e.g. Varnish for
:doc:`caching </http_cache>`).

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

Solution: ``setTrustedProxies()``
---------------------------------

To fix this, you need to tell Symfony which reverse proxy IP addresses to trust
and what headers your reverse proxy uses to send information:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            # the IP address (or range) of your proxy
            trusted_proxies: '192.0.0.1,10.0.0.0/8'
            # trust *all* "X-Forwarded-*" headers
            trusted_headers: ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']
            # or, if your proxy instead uses the "Forwarded" header
            trusted_headers: ['forwarded']

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- the IP address (or range) of your proxy -->
                <framework:trusted-proxies>192.0.0.1,10.0.0.0/8</framework:trusted-proxies>

                <!-- trust *all* "X-Forwarded-*" headers -->
                <framework:trusted-header>x-forwarded-for</framework:trusted-header>
                <framework:trusted-header>x-forwarded-host</framework:trusted-header>
                <framework:trusted-header>x-forwarded-proto</framework:trusted-header>
                <framework:trusted-header>x-forwarded-port</framework:trusted-header>
                <framework:trusted-header>x-forwarded-prefix</framework:trusted-header>

                <!-- or, if your proxy instead uses the "Forwarded" header -->
                <framework:trusted-header>forwarded</framework:trusted-header>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework
                // the IP address (or range) of your proxy
                ->trustedProxies('192.0.0.1,10.0.0.0/8')
                // trust *all* "X-Forwarded-*" headers (the ! prefix means to not trust those headers)
                ->trustedHeaders(['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix'])
                // or, if your proxy instead uses the "Forwarded" header
                ->trustedHeaders(['forwarded'])
            ;
        };

.. caution::

    Enabling the ``Request::HEADER_X_FORWARDED_HOST`` option exposes the
    application to `HTTP Host header attacks`_. Make sure the proxy really
    sends an ``x-forwarded-host`` header.

The Request object has several ``Request::HEADER_*`` constants that control exactly
*which* headers from your reverse proxy are trusted. The argument is a bit field,
so you can also pass your own value (e.g. ``0b00110``).

.. caution::

    The "trusted proxies" feature does not work as expected when using the
    `nginx realip module`_. Disable that module when serving Symfony applications.

But what if the IP of my Reverse Proxy Changes Constantly!
----------------------------------------------------------

Some reverse proxies (like AWS Elastic Load Balancing) don't have a
static IP address or even a range that you can target with the CIDR notation.
In this case, you'll need to - *very carefully* - trust *all* proxies.

#. Configure your web server(s) to *not* respond to traffic from *any* clients
   other than your load balancers. For AWS, this can be done with `security groups`_.

#. Once you've guaranteed that traffic will only come from your trusted reverse
   proxies, configure Symfony to *always* trust incoming request:

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            # trust *all* requests (the 'REMOTE_ADDR' string is replaced at
            # run time by $_SERVER['REMOTE_ADDR'])
            trusted_proxies: '127.0.0.1,REMOTE_ADDR'

That's it! It's critical that you prevent traffic from all non-trusted sources.
If you allow outside traffic, they could "spoof" their true IP address and
other information.

.. tip::

    In applications using :ref:`Symfony Flex <symfony-flex>` you can set the
    ``TRUSTED_PROXIES`` env var:

    .. code-block:: bash

        # .env
        TRUSTED_PROXIES=127.0.0.1,REMOTE_ADDR

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            trusted_proxies: '%env(TRUSTED_PROXIES)%'

If you are also using a reverse proxy on top of your load balancer (e.g.
`CloudFront`_), calling ``$request->server->get('REMOTE_ADDR')`` won't be
enough, as it will only trust the node sitting directly above your application
(in this case your load balancer). You also need to append the IP addresses or
ranges of any additional proxy (e.g. `CloudFront IP ranges`_) to the array of
trusted proxies.

Custom Headers When Using a Reverse Proxy
-----------------------------------------

Some reverse proxies (like `CloudFront`_ with ``CloudFront-Forwarded-Proto``)
may force you to use a custom header. For instance you have
``Custom-Forwarded-Proto`` instead of ``X-Forwarded-Proto``.

In this case, you'll need to set the header ``X-Forwarded-Proto`` with the value
of ``Custom-Forwarded-Proto`` early enough in your application, i.e. before
handling the request::

    // public/index.php

    // ...
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = $_SERVER['HTTP_CUSTOM_FORWARDED_PROTO'];
    // ...
    $response = $kernel->handle($request);

.. _`security groups`: https://docs.aws.amazon.com/elasticloadbalancing/latest/classic/elb-security-groups.html
.. _`CloudFront`: https://en.wikipedia.org/wiki/Amazon_CloudFront
.. _`CloudFront IP ranges`: https://ip-ranges.amazonaws.com/ip-ranges.json
.. _`HTTP Host header attacks`: https://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
.. _`nginx realip module`: https://nginx.org/en/docs/http/ngx_http_realip_module.html
