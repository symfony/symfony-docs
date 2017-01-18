.. index::
   single: Request; Trusted Proxies

Trusting Proxies
================

.. tip::

    If you're using the Symfony Framework, start by reading
    :doc:`/request/load_balancer_reverse_proxy`.

If you find yourself behind some sort of proxy - like a load balancer - then
certain header information may be sent to you using special ``X-Forwarded-*``
headers or the ``Forwarded`` header. For example, the ``Host`` HTTP header is
usually used to return the requested host. But when you're behind a proxy,
the actual host may be stored in an ``X-Forwarded-Host`` header.

Since HTTP headers can be spoofed, Symfony does *not* trust these proxy
headers by default. If you are behind a proxy, you should manually whitelist
your proxy as follows:

.. code-block:: php

    use Symfony\Component\HttpFoundation\Request;

    // put this code as early as possible in your application (e.g. in your
    // front controller) to only trust proxy headers coming from these IP addresses
    Request::setTrustedProxies(array('192.0.0.1', '10.0.0.0/8'));

.. versionadded:: 2.3
    CIDR notation support was introduced in Symfony 2.3, so you can whitelist whole
    subnets (e.g. ``10.0.0.0/8``, ``fc00::/7``).

You should also make sure that your proxy filters unauthorized use of these
headers, e.g. if a proxy natively uses the ``X-Forwarded-For`` header, it
should not allow clients to send ``Forwarded`` headers to Symfony.

If your proxy does not filter headers appropriately, you need to configure
Symfony not to trust the headers your proxy does not filter (see below).

Configuring Header Names
------------------------

By default, the following proxy headers are trusted:

* ``Forwarded`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getClientIp`;
* ``X-Forwarded-For`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getClientIp`;
* ``X-Forwarded-Host`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getHost`;
* ``X-Forwarded-Port`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getPort`;
* ``X-Forwarded-Proto`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getScheme` and :method:`Symfony\\Component\\HttpFoundation\\Request::isSecure`;

If your reverse proxy uses a different header name for any of these, you
can configure that header name via :method:`Symfony\\Component\\HttpFoundation\\Request::setTrustedHeaderName`::

    Request::setTrustedHeaderName(Request::HEADER_FORWARDED, 'X-Forwarded');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_IP, 'X-Proxy-For');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_HOST, 'X-Proxy-Host');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_PORT, 'X-Proxy-Port');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_PROTO, 'X-Proxy-Proto');

Not Trusting certain Headers
----------------------------

By default, if you whitelist your proxy's IP address, then all five headers
listed above are trusted. If you need to trust some of these headers but
not others, you can do that as well::

    // disables trusting the ``Forwarded`` header
    Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
