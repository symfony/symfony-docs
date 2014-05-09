.. index::
   single: Request; Trusted Proxies

Trusting Proxies
================

If you find yourself behind some sort of proxy - like a load balancer - then
certain header information may be sent to you using special ``X-Forwarded-*``
headers. For example, the ``Host`` HTTP header is usually used to return
the requested host. But when you're behind a proxy, the true host may be
stored in a ``X-Forwarded-Host`` header.

Since HTTP headers can be spoofed, Symfony2 does *not* trust these proxy
headers by default. If you are behind a proxy, you should manually whitelist
your proxy.

.. versionadded:: 2.3
    CIDR notation support was introduced in Symfony 2.3, so you can whitelist whole
    subnets (e.g. ``10.0.0.0/8``, ``fc00::/7``).

.. code-block:: php

    use Symfony\Component\HttpFoundation\Request;

    // only trust proxy headers coming from this IP addresses
    Request::setTrustedProxies(array('192.0.0.1', '10.0.0.0/8'));

.. note::

   When using Symfony's internal reverse proxy (``AppCache.php``) make sure to add
   ``127.0.0.1`` to the list of trusted proxies.


Configuring Header Names
------------------------

By default, the following proxy headers are trusted:

* ``X-Forwarded-For`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getClientIp`;
* ``X-Forwarded-Host`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getHost`;
* ``X-Forwarded-Port`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getPort`;
* ``X-Forwarded-Proto`` Used in :method:`Symfony\\Component\\HttpFoundation\\Request::getScheme` and :method:`Symfony\\Component\\HttpFoundation\\Request::isSecure`;

If your reverse proxy uses a different header name for any of these, you
can configure that header name via :method:`Symfony\\Component\\HttpFoundation\\Request::setTrustedHeaderName`::

    Request::setTrustedHeaderName(Request::HEADER_CLIENT_IP, 'X-Proxy-For');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_HOST, 'X-Proxy-Host');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_PORT, 'X-Proxy-Port');
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_PROTO, 'X-Proxy-Proto');

Not Trusting certain Headers
----------------------------

By default, if you whitelist your proxy's IP address, then all four headers
listed above are trusted. If you need to trust some of these headers but
not others, you can do that as well::

    // disables trusting the ``X-Forwarded-Proto`` header, the default header is used
    Request::setTrustedHeaderName(Request::HEADER_CLIENT_PROTO, '');
