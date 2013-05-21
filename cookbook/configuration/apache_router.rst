.. index::
   single: Apache Router

How to use the Apache Router
============================

Symfony2, while fast out of the box, also provides various ways to increase that speed with a little bit of tweaking.
One of these ways is by letting apache handle routes directly, rather than using Symfony2 for this task.

Change Router Configuration Parameters
--------------------------------------

To dump Apache routes you must first tweak some configuration parameters to tell
Symfony2 to use the ``ApacheUrlMatcher`` instead of the default one:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_prod.yml
        parameters:
            router.options.matcher.cache_class: ~ # disable router cache
            router.options.matcher_class: Symfony\Component\Routing\Matcher\ApacheUrlMatcher

    .. code-block:: xml

        <!-- app/config/config_prod.xml -->
        <parameters>
            <parameter key="router.options.matcher.cache_class">null</parameter> <!-- disable router cache -->
            <parameter key="router.options.matcher_class">
                Symfony\Component\Routing\Matcher\ApacheUrlMatcher
            </parameter>
        </parameters>

    .. code-block:: php

        // app/config/config_prod.php
        $container->setParameter('router.options.matcher.cache_class', null); // disable router cache
        $container->setParameter(
            'router.options.matcher_class',
            'Symfony\Component\Routing\Matcher\ApacheUrlMatcher'
        );

.. tip::

    Note that :class:`Symfony\\Component\\Routing\\Matcher\\ApacheUrlMatcher`
    extends :class:`Symfony\\Component\\Routing\\Matcher\\UrlMatcher` so even
    if you don't regenerate the url_rewrite rules, everything will work (because
    at the end of ``ApacheUrlMatcher::match()`` a call to ``parent::match()``
    is done).

Generating mod_rewrite rules
----------------------------

To test that it's working, let's create a very basic route for demo bundle:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        hello:
            path:  /hello/{name}
            defaults: { _controller: AcmeDemoBundle:Demo:hello }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <route id="hello" path="/hello/{name}">
            <default key="_controller">AcmeDemoBundle:Demo:hello</default>
        </route>

    .. code-block:: php

        // app/config/routing.php
        $collection->add('hello', new Route('/hello/{name}', array(
            '_controller' => 'AcmeDemoBundle:Demo:hello',
        )));

Now generate **url_rewrite** rules:

.. code-block:: bash

    $ php app/console router:dump-apache -e=prod --no-debug

Which should roughly output the following:

.. code-block:: apache

    # skip "real" requests
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule .* - [QSA,L]

    # hello
    RewriteCond %{REQUEST_URI} ^/hello/([^/]+?)$
    RewriteRule .* app.php [QSA,L,E=_ROUTING__route:hello,E=_ROUTING_name:%1,E=_ROUTING__controller:AcmeDemoBundle\:Demo\:hello]

You can now rewrite `web/.htaccess` to use the new rules, so with this example
it should look like this:

.. code-block:: apache

    <IfModule mod_rewrite.c>
        RewriteEngine On

        # skip "real" requests
        RewriteCond %{REQUEST_FILENAME} -f
        RewriteRule .* - [QSA,L]

        # hello
        RewriteCond %{REQUEST_URI} ^/hello/([^/]+?)$
        RewriteRule .* app.php [QSA,L,E=_ROUTING__route:hello,E=_ROUTING_name:%1,E=_ROUTING__controller:AcmeDemoBundle\:Demo\:hello]
    </IfModule>

.. note::

   Procedure above should be done each time you add/change a route if you want to take full advantage of this setup

That's it!
You're now all set to use Apache Route rules.

Additional tweaks
-----------------

To save a little bit of processing time, change occurrences of ``Request``
to ``ApacheRequest`` in ``web/app.php``::

    // web/app.php

    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';
    //require_once __DIR__.'/../app/AppCache.php';

    use Symfony\Component\HttpFoundation\ApacheRequest;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    //$kernel = new AppCache($kernel);
    $kernel->handle(ApacheRequest::createFromGlobals())->send();
