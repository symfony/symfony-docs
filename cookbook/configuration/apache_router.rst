.. index::
   single: Apache Router

How to use Apache Router
========================

Symfony2, while fast out of the box, also provides various ways to increase that speed with a little bit of tweaking.
One of these ways is by letting apache handle routes directly, rather than using Symfony2 for this task.

Change Router Configuration Parameters
--------------------------------------

To to be to dump Apache routes we must first tweak configuration parameters and tell
Symfony2 to use ApacheUrlMatcher instead of the default one:

.. code-block:: yaml
    
    # app/config/config_prod.yml
    parameters:
        router.options.matcher.cache_class: ~ # disable router cache
        router.options.matcher_class: Symfony\Component\Routing\Matcher\ApacheUrlMatcher

.. tip::

    Note that :class: `Symfony\\Component\\Routing\\Matcher\\ApacheUrlMatcher` extends :class: `Symfony\\Component\\Routing\\Matcher\\UrlMatcher` so even if you don't regenerate 
    the url_rewrite rules, everything will work (because at the end of **ApacheUrlMatcher::match()** 
    a call to **parent::match()** is done). 
    
Generating mod_rewrite rules
---------------------------
    
To test that it's working, let's create a very basic route for demo bundle:

.. code-block:: yaml
    
    # app/config/routing.yml
    hello:
        pattern:  /hello/{name}
        defaults: { _controller: AcmeDemoBundle:Demo:hello }
            
    
Now we generate **url_rewrite** rules:
    
.. code-block:: bash

    php app/console router:dump-apache -e=prod --no-debug
    
Which should roughly output the following:

.. code-block:: apache

    # skip "real" requests
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule .* - [QSA,L]

    # hello
    RewriteCond %{REQUEST_URI} ^/hello/([^/]+?)$
    RewriteRule .* app.php [QSA,L,E=_ROUTING__route:hello,E=_ROUTING_name:%1,E=_ROUTING__controller:AcmeDemoBundle\:Demo\:hello]

You can now rewrite `web/.htaccess` to use new rules, so with our example it should look like this:

.. code-block:: text

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

To save a little bit of processing time, change in **web/app.php** occurences of **Request** into **ApacheRequest**::

    // web/app.php
    
    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';
    //require_once __DIR__.'/../app/AppCache.php';

    use Symfony\Component\HttpFoundation\ApacheRequest;

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();
    //$kernel = new AppCache($kernel);
    $kernel->handle(ApacheRequest::createFromGlobals())->send();