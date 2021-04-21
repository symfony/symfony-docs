.. index::
    single: Cache; ESI
    single: ESI

.. _edge-side-includes:

Working with Edge Side Includes
===============================

Gateway caches are a great way to make your website perform better. But they
have one limitation: they can only cache whole pages. If your pages contain
dynamic sections, such as the user name or a shopping cart, you are out of
luck. Fortunately, Symfony provides a solution for these cases, based on a
technology called `ESI`_, or Edge Side Includes. Akamai wrote this specification
in 2001 and it allows specific parts of a page to have a different
caching strategy than the main page.

The ESI specification describes tags you can embed in your pages to communicate
with the gateway cache. Only one tag is implemented in Symfony, ``include``,
as this is the only useful one outside of Akamai context:

.. code-block:: html

    <!DOCTYPE html>
    <html>
        <body>
            <!-- ... some content -->

            <!-- Embed the content of another page here -->
            <esi:include src="http://..."/>

            <!-- ... more content -->
        </body>
    </html>

.. note::

    Notice from the example that each ESI tag requires a fully-qualified URL.
    An ESI tag represents a page fragment that can be fetched via the given
    URL.

When a request is handled, the gateway cache fetches the entire page from
its cache or requests it from the backend application. If the response contains
one or more ESI tags, these are processed in the same way. In other words,
the gateway cache either retrieves the included page fragment from its cache
or requests the page fragment from the backend application again. When all
the ESI tags have been resolved, the gateway cache merges each into the main
page and sends the final content to the client.

All of this happens transparently at the gateway cache level (i.e. outside
of your application). As you'll see, if you choose to take advantage of ESI
tags, Symfony makes the process of including them almost effortless.

.. _using-esi-in-symfony2:

Using ESI in Symfony
~~~~~~~~~~~~~~~~~~~~

First, to use ESI, be sure to enable it in your application configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            esi: { enabled: true }

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
                <!-- ... -->
                <framework:esi enabled="true"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->esi()
                ->enabled(true)
            ;
        };

Now, suppose you have a page that is relatively static, except for a news
ticker at the bottom of the content. With ESI, you can cache the news ticker
independently of the rest of the page::

    // src/Controller/DefaultController.php
    namespace App\Controller;

    // ...
    class DefaultController extends AbstractController
    {
        public function about()
        {
            $response = $this->render('static/about.html.twig');
            $response->setPublic();
            $response->setMaxAge(600);

            return $response;
        }
    }

In this example, the response is marked as public to make the full page
cacheable for all requests with a lifetime of ten minutes.
Next, include the news ticker in the template by embedding an action.
This is done via the ``render()`` helper (for more details, see how to
:ref:`embed controllers in templates <templates-embed-controllers>`).

As the embedded content comes from another page (or controller for that
matter), Symfony uses the standard ``render`` helper to configure ESI tags:

.. code-block:: twig

    {# templates/static/about.html.twig #}

    {# you can use a controller reference #}
    {{ render_esi(controller('App\\Controller\\NewsController::latest', { 'maxPerPage': 5 })) }}

    {# ... or a URL #}
    {{ render_esi(url('latest_news', { 'maxPerPage': 5 })) }}

By using the ``esi`` renderer (via the ``render_esi()`` Twig function), you
tell Symfony that the action should be rendered as an ESI tag. You might be
wondering why you would want to use a helper instead of just writing the ESI
tag yourself. That's because using a helper makes your application work even
if there is no gateway cache installed.

.. tip::

    As you'll see below, the ``maxPerPage`` variable you pass is available
    as an argument to your controller (i.e. ``$maxPerPage``). The variables
    passed through ``render_esi`` also become part of the cache key so that
    you have unique caches for each combination of variables and values.

When using the default ``render()`` function (or setting the renderer to
``inline``), Symfony merges the included page content into the main one
before sending the response to the client. But if you use the ``esi`` renderer
(i.e. call ``render_esi()``) *and* if Symfony detects that it's talking to a
gateway cache that supports ESI, it generates an ESI include tag. But if there
is no gateway cache or if it does not support ESI, Symfony will just merge
the included page content within the main one as it would have done if you had
used ``render()``.

.. note::

    Symfony considers that a gateway cache supports ESI if its request include
    the ``Surrogate-Capability`` HTTP header and the value of that header
    contains the ``ESI/1.0`` string anywhere.

The embedded action can now specify its own caching rules entirely independently
of the main page::

    // src/Controller/NewsController.php
    namespace App\Controller;

    // ...
    class NewsController extends AbstractController
    {
        public function latest($maxPerPage)
        {
            // ...
            $response->setPublic();
            $response->setMaxAge(60);

            return $response;
        }
    }

In this example, the embedded action is cached publicly too because the contents
are the same for all requests. However, in other cases you may need to make this
response non-public and even non-cacheable, depending on your needs.

Putting all the above code together, with ESI the full page cache will be valid
for 600 seconds, but the news component cache will only last for 60 seconds.

.. _http_cache-fragments:

When using a controller reference, the ESI tag should reference the embedded
action as an accessible URL so the gateway cache can fetch it independently of
the rest of the page. Symfony takes care of generating a unique URL for any
controller reference and it is able to route them properly thanks to the
:class:`Symfony\\Component\\HttpKernel\\EventListener\\FragmentListener`
that must be enabled in your configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            fragments: { path: /_fragment }

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

            <!-- ... -->
            <framework:config>
                <framework:fragment path="/_fragment"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->fragments()
                ->path('/_fragment')
            ;
        };

One great advantage of the ESI renderer is that you can make your application
as dynamic as needed and at the same time, hit the application as little as
possible.

.. caution::

    The fragment listener only responds to signed requests. Requests are only
    signed when using the fragment renderer and the ``render_esi`` Twig
    function.

The ``render_esi`` helper supports two other useful options:

``alt``
    Used as the ``alt`` attribute on the ESI tag, which allows you to specify an
    alternative URL to be used if the ``src`` cannot be found.

``ignore_errors``
    If set to true, an ``onerror`` attribute will be added to the ESI with a value
    of ``continue`` indicating that, in the event of a failure, the gateway cache
    will remove the ESI tag silently.

.. _`ESI`: http://www.w3.org/TR/esi-lang
