.. index::
    single: Cache; CSRF; Forms

Caching Pages that Contain CSRF Protected Forms
===============================================

CSRF tokens are meant to be different for every user. This is why you
need to be cautious if you try to cache pages with forms including them.

For more information about how CSRF protection works in Symfony, please
check :doc:`CSRF Protection </security/csrf>`.

Why Caching Pages with a CSRF token is Problematic
--------------------------------------------------

Typically, each user is assigned a unique CSRF token, which is stored in
the session for validation. This means that if you *do* cache a page with
a form containing a CSRF token, you'll cache the CSRF token of the *first*
user only. When a user submits the form, the token won't match the token
stored in the session and all users (except for the first) will fail CSRF
validation when submitting the form.

In fact, many reverse proxies (like Varnish) will refuse to cache a page
with a CSRF token. This is because a cookie is sent in order to preserve
the PHP session open and Varnish's default behavior is to not cache HTTP
requests with cookies.

How to Cache Most of the Page and still be able to Use CSRF Protection
----------------------------------------------------------------------

To cache a page that contains a CSRF token, you can use more advanced caching
techniques like :doc:`ESI fragments </http_cache/esi>`, where you cache the full
page and embedding the form or just the CSRF token inside an ESI tag with no
cache at all. When you have your custom form theme you can do this by create a
new token_widget block and call render_esi there:

.. code-block:: twig

    {%- block token_widget %}
       {{ render_esi(controller('App\\Controller\\FormController::token', { 'form': form.parent.vars.name })) }}
    {%- endblock token_widget -%}
    
You can use the ``security.csrf.token_manager`` service to generate a token for your given form:

.. code-block:: php

    public function token(Request $request, TokenGeneratorInterface $generator)
    {
        $formName = $request->attributes->get('form');
        $csrfToken = $csrfTokenManager->getToken($formName)->getValue();

        return new Response(sprintf(
            '<input type="hidden" id="%s__token" name="%s[_token]" value="%s" />',
            $formName,
            $formName,
            $csrfToken
        ));
    }

Another option would be to load the form via an uncached AJAX request, but
cache the rest of the HTML response.

Or you can even load just the CSRF token with an AJAX request and replace the
form field value with it. Take a look at :doc:`hinclude.js </templating/hinclude>`
for a nice solution.



.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`Security CSRF Component`: https://github.com/symfony/security-csrf
