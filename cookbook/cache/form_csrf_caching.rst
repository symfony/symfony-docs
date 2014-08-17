.. index::
    single: Cache; CSRF; Forms

Caching Pages that Contain CSRF Protected Forms
===============================================

CSRF tokens are meant to be different for every user. This is why you
need to be cautious if you try to cache pages with forms including them.

For more information about how CSRF protection works in Symfony, please
check :ref:`CSRF Protection <forms-csrf>`.

Why Reverse Proxy Caches do not Cache these Pages by Default
------------------------------------------------------------

There are many ways to generate unique tokens for each user but in order get
them validated when the form is submitted, you need to store them inside the
PHP Session.

If you are using Varnish or some similar reverse proxy cache and you try to cache
pages containing forms with CSRF token protection, you will see that, by default,
the reverse proxy cache refuses to cache.

This happens because a cookie is sent in order to preserve the PHP session open and
Varnish default behaviour is to not cache HTTP requests with cookies.

If you think about it, if you managed to cache the form you would end up
with many users getting the same token in the form generation. When these
users try to send the form to the server, the CSRF validation will fail for
them because the expected token is stored in their session and different
for each user.

How to Cache Most of the Page and still Be Able to Use CSRF Protection
----------------------------------------------------------------------

To cache a page that contains a CSRF token you can use more advanced caching
techniques like `ESI`_ fragments, having a TTL for the full page and embedding
the form inside an ESI tag with no cache at all.

Another option to be able to cache that heavy page would be loading the form
via an uncached AJAX request but cache the rest of the HTML response.

Or you can even load just the CSRF token with an AJAX request and replace the
form field value with it.

.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`ESI`: http://www.w3.org/TR/esi-lang
.. _`Security CSRF Component`: https://github.com/symfony/security-csrf