AssetMapper: Simple, Modern CSS & JS Management
===============================================

.. versionadded:: 6.3

    The AssetMapper component was introduced as an
    :doc:`experimental feature </contributing/code/experimental>` in
    Symfony 6.3.

The AssetMapper component lets you write modern JavaScript and CSS without the complexity
of using a bundler. Browsers *already* support many modern JavaScript features
like the ``import`` statement and ES6 classes. And the HTTP/2 protocol means that
combining your assets to reduce HTTP connections is no longer urgent. This component
is a light layer that helps serve your files directly to the browser.

The AssetMapper component has two main features:

* :ref:`Mapping & Versioning Assets <mapping-assets>`: All files inside of ``assets/``
  are made available publicly and **versioned**. For example, you can reference
  ``assets/styles/app.css`` in a template with ``{{ asset('styles/app.css') }}``.
  The final URL will include a version hash, like ``/assets/styles/app-3c16d9220694c0e56d8648f25e6035e9.css``.

* :ref:`Importmaps <importmaps-javascript>`: A native browser feature that makes it easier
  to use the JavaScript ``import`` statement (e.g. ``import { Modal } from 'bootstrap'``)
  without a build system. It's supported in all browsers (thanks to a shim)
  and is a `W3C standard <https://html.spec.whatwg.org/multipage/webappapis.html#import-maps>`_.

Installation
------------

To install the AssetMapper component, run:

.. code-block:: terminal

    $ composer require symfony/asset-mapper symfony/asset symfony/twig-pack

In addition to ``symfony/asset-mapper``, this also makes sure that you have the
:doc:`Asset Component </components/asset>` and Twig available.

If you're using :ref:`Symfony Flex <symfony-flex>`, you're done! The recipe just
added a number of files:

* ``assets/app.js`` Your main JavaScript file;
* ``assets/styles/app.css`` Your main CSS file;
* ``config/packages/asset_mapper.yaml`` Where you define your asset "paths";
* ``importmap.php`` Your importmap config file.

It also *updated* the ``templates/base.html.twig`` file:

.. code-block:: diff

    {% block stylesheets %}
    +    <link rel="stylesheet" href="{{ asset('styles/app.css') }}">
    {% endblock %}

    {% block javascripts %}
    +    {{ importmap() }}
    {% endblock %}

If you're not using Flex, you'll need to create & update these files manually. See
the `latest asset-mapper recipe`_ for the exact content of these files.

.. _mapping-assets:

Mapping and Referencing Assets
------------------------------

The AssetMapper component works by defining directories/paths of assets that you want to expose
publicly. These assets are then versioned and easy to reference. Thanks to the
``asset_mapper.yaml`` file, your app starts with one mapped path: the ``assets/``
directory.

If you create an ``assets/images/duck.png`` file, you can reference it in a template with:

.. code-block:: html+twig

    <img src="{{ asset('images/duck.png') }}">

The path - ``images/duck.png`` - is relative to your mapped directory (``assets/``).
This is known as the **logical path** to your asset.

If you look at the HTML in your page, the URL will be something
like: ``/assets/images/duck-3c16d9220694c0e56d8648f25e6035e9.png``. If you update
the file, the version part of the URL will change automatically!

Serving Assets in dev vs prod
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the ``dev`` environment, the URL - ``/assets/images/duck-3c16d9220694c0e56d8648f25e6035e9.png``
is handled and returned by your Symfony app. For the ``prod`` environment, before
deploy, you should run:

.. code-block:: terminal

    $ php bin/console asset-map:compile

This will physically copy all the files from your mapped directories into
``public/assets/`` so that they're served directly by your web server.
See :ref:`Deployment <asset-mapper-deployment>` for more details.

Paths Inside of CSS Files
~~~~~~~~~~~~~~~~~~~~~~~~~

From inside CSS, you can reference other files using the normal CSS ``url()``
function and a relative path to the target file:

.. code-block:: css

    /* assets/styles/app.css */
    .quack {
        /* file lives at assets/images/duck.png */
        background-image: url('../images/duck.png');
    }

The path in the final ``app.css`` file will automatically include the versioned URL
for ``duck.png``:

.. code-block:: css

    /* public/assets/styles/app-3c16d9220694c0e56d8648f25e6035e9.css */
    .quack {
        background-image: url('../images/duck-3c16d9220694c0e56d8648f25e6035e9.png');
    }

Debugging: Seeing All Mapped Assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To see all of the mapped assets in your app, run:

.. code-block:: terminal

    $ php bin/console debug:asset-map

This will show you all the mapped paths and the assets inside of each:

.. code-block:: text

    AssetMapper Paths
    ------------------

    --------- ------------------
     Path      Namespace prefix
    --------- ------------------
    assets

    Mapped Assets
    -------------

    ------------------ ----------------------------------------------------
     Logical Path       Filesystem Path
    ------------------ ----------------------------------------------------
     app.js             assets/app.js
     styles/app.css     assets/styles/app.css
     images/duck.png    assets/images/duck.png

The "Logical Path" is the path to use when referencing the asset, like
from a template.

.. _importmaps-javascript:

Importmaps & Writing JavaScript
-------------------------------

All modern browsers support the JavaScript `import statement`_ and modern
`ES6`_ features like classes. So this code "just works":

.. code-block:: javascript

    // assets/app.js
    import Duck from './duck.js';

    const duck = new Duck('Waddles');
    duck.quack();

.. code-block:: javascript

    // assets/duck.js
    export default class {
        constructor(name) {
            this.name = name;
        }
        quack() {
            console.log(`${this.name} says: Quack!`);
        }
    }

Thanks to the ``{{ importmap() }}`` Twig function, which you'll learn all about in
this section, the ``assets/app.js`` file is loaded & executed by the browser.

.. tip::

    When importing relative files, be sure to include the ``.js`` extension.
    Unlike in Node, the extension is required in the browser environment.

Importing 3rd Party JavaScript Packages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to use an `npm package`_, like `bootstrap`_. Technically,
this can be done by importing its full URL, like from a CDN:

.. code-block:: javascript

    import { Alert } from 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm';

But yikes! Needing to include that URL is a pain! Instead, we can add
this to our "importmap" via the ``importmap:require`` command. This command can
be used to download any `npm package`_:

.. code-block:: terminal

    $ php bin/console importmap:require bootstrap

This adds the ``bootstrap`` package to your ``importmap.php`` file::

    // importmap.php
    return [
        // ...

        'bootstrap' => [
            'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm',
        ],
    ];

Now you can import the ``bootstrap`` package like normal:

.. code-block:: javascript

    import { Alert } from 'bootstrap';
    // ...

If you want to download the package locally, use the ``--download`` option:

.. code-block:: terminal

    $ php bin/console importmap:require bootstrap --download

This will download the package into an ``assets/vendor/`` directory and update
the ``importmap.php`` file to point to it. You *should* commit this file to
your repository.

.. note::

    Sometimes, a package - like ``bootstrap`` - will have one or more dependencies,
    such as ``@popperjs/core``. The ``download`` option will download both the main
    package *and* its dependencies.

To update all 3rd party packages in your ``importmap.php`` file, run:

.. code-block:: terminal

    $ php bin/console importmap:update

How does the importmap Work?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

How does this ``importmap.php`` file allow you to import ``bootstrap``? That's
thanks to the ``{{ importmap() }}`` Twig function in ``base.html.twig``, which
outputs an `importmap`_:

.. code-block:: html

    <script type="importmap">{
        "imports": {
            "app": "/assets/app-4e986c1a2318dd050b1d47db8d856278.js",
            "/assets/duck.js": "/assets/duck-1b7a64b3b3d31219c262cf72521a5267.js",
            "bootstrap": "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm"
        }
    }</script>

Import maps are a native browser feature. They work in all browsers thanks to
a "shim" file that's included automatically by the AssetMapper component
(all *modern* browsers `support them natively <https://caniuse.com/import-maps>`_).

When you import ``bootstrap`` from your JavaScript, the browser will look at
the ``importmap`` and see that it should fetch the package from the URL.

.. _automatic-import-mapping:

But where did the ``/assets/duck.js`` import entry come from? Great question!

The ``assets/app.js`` file above imports ``./duck.js``. When you import a file using a
relative path, your browser looks for that file relative to the one importing
it. So, it would look for ``/assets/duck.js``. That URL *would* be correct,
except that the ``duck.js`` file is versioned. Fortunately, the AssetMapper component
sees that import and adds a mapping from ``/assets/duck.js`` to the correct, versioned
filename. The result: importing ``./duck.js`` just works!

Preloading and Initializing "app.js"
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the importmap, the ``{{ importmap() }}`` Twig function also renders
an `es module shim`_ and a few other things, like a set of "preloads":

.. code-block:: html

    <link rel="modulepreload" href="/assets/app-4e986c1a2318dd050b1d47db8d856278.js">
    <link rel="modulepreload" href="/assets/duck-1b7a64b3b3d31219c262cf72521a5267.js">

In ``importmap.php``, each entry can have a ``preload`` option. If set to ``true``,
a ``<link rel="modulepreload">`` tag is rendered for that entry as well as for
any JavaScript files it imports (this happens for "relative" - ``./`` or ``../`` -
imports only). This is a performance optimization and you can learn more about below
in :ref:`Performance: Add Preloading <performance-preloading>`.

.. _importmap-app-entry:

The ``importmap()`` function also renders one more line:

.. code-block:: html

    <script type="module">import 'app';</script>

So far, the snippets shown export an ``importmap`` and even hinted to the
browser that it should preload some files. But the browser hasn't yet been told to
actually parse and execute any JavaScript. This line does that: it imports the
``app`` entry, which causes the code in ``assets/app.js`` to be executed.

Importing Specific Files From a 3rd Party Package
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes you'll need to import a specific file from a package. For example,
suppose you're integrating `highlight.js`_ and want to import just the core
and a specific language:

.. code-block:: javascript

    import hljs from 'highlight.js/lib/core';
    import javascript from 'highlight.js/lib/languages/javascript';

    hljs.registerLanguage('javascript', javascript);
    hljs.highlightAll();

In this case, adding the ``highlight.js`` package to your ``importmap.php`` file
won't work: whatever your importing - e.g. ``highlight.js/lib/core`` - needs to
*exactly* match an entry in the ``importmap.php`` file.

Instead, use ``importmap:require`` and pass it the exact paths you need. This
also shows how you can require multiple packages at once:

.. code-block:: terminal

    $ php bin/console importmap:require highlight.js/lib/core highlight.js/lib/languages/javascript

Global Variables like jQuery
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You might be accustomed to relying on global variables - like jQuery's ``$``
variable:

.. code-block:: javascript

    // assets/app.js
    import 'jquery';

    // app.js or any other file
    $('.something').hide(); // WILL NOT WORK!

But in a module environment (like with AssetMapper), when you import
a library like ``jquery``, it does *not* create a global variable. Instead, you
should import it and set it to a variable in *every* file you need it:

.. code-block:: javascript

    import $ from 'jquery';
    $('.something').hide();

You can even do this from an inline script tag:

.. code-block:: html

    <script type="module">
        import $ from 'jquery';
        $('.something').hide();
    </script>

If you *do* need something to become a global variable, you do it manually
from inside ``app.js``:

.. code-block:: javascript

    import $ from 'jquery';
    // things on "window" become global variables
    window.$ = $;

Handling 3rd-Party CSS
----------------------

With the ``importmap:require`` command, you can quickly use any JavaScript
package. But what about CSS? For example, the ``bootstrap`` package also contains
a CSS file.

Including CSS is a bit more manual, but still easy enough. To find the CSS,
we recommend using `jsdelivr.com`_:

#. Search for the package on `jsdelivr.com`_.
#. Once on the package page (e.g. https://www.jsdelivr.com/package/npm/bootstrap),
   sometimes the ``link`` tag to the CSS file will already be shown in the "Install" box.
#. If not, click the "Files" tab and find the CSS file you need. For example,
   the ``bootstrap`` package has a ``dist/css/bootstrap.min.css`` file. If you're
   not sure which file to use, check the ``package.json`` file. Often
   this will have a ``main`` or ``style`` key that points to the CSS file.

Once you have the URL, include it in ``base.html.twig``:

.. code-block:: diff

    {% block stylesheets %}
    +   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ asset('styles/app.css') }}">
    {% endblock %}

If you'd rather download the CSS file and include it locally, you can do that.
For example, you could manually download, save it to ``assets/vendor/bootstrap.min.css``
and then include it with:

.. code-block:: html+twig

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.min.css') }}">

Lazily Importing CSS from a JavaScript File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using a bundler like :ref:`Encore <frontend-webpack-encore>`, you can
import CSS from a JavaScript file:

.. code-block:: javascript

    // this CAN work (keep reading), but will be loaded lazily
    import 'swiper/swiper-bundle.min.css';

This *can* work with importmaps, but it should *not* be used for critical CSS
that needs to be loaded before the page is rendered because the browser
won't download the CSS until the JavaScript file executed.

However, if you *do* want to lazily-load a CSS file, you can make this work
by using the ``importmap:require`` command and pointing it at a CSS file.

.. code-block:: terminal

    $ php bin/console importmap:require swiper/swiper-bundle.min.css

This works because ``jsdelivr`` returns a URL to a JavaScript file that,
when executed, adds the CSS to your page.

Issues and Debugging
--------------------

There are a few common errors and problems you might run into.

Missing importmap Entry
~~~~~~~~~~~~~~~~~~~~~~~

One of the most common errors will come from your browser's console, and
will something like this:

    Failed to resolve module specifier "    bootstrap". Relative references must start
    with either "/", "./", or "../".

Or:

    The specifier "bootstrap" was a bare specifier, but was not remapped to anything.
    Relative module specifiers must start with "./", "../" or "/".

This means that, somewhere in your JavaScript, you're importing a 3rd party
package - e.g. ``import 'bootstrap'``. The browser tries to find this
package in your ``importmap`` file, but it's not there.

The fix is almost always to add it to your ``importmap``:

.. code-block:: terminal

    $ php bin/console importmap:require bootstrap

.. note::

    Some browsers, like Firefox, show *where* this "import" code lives, while
    others like Chrome currently do not.

404 Not Found for a JavaScript, CSS or Image File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes a JavaScript file you're importing (e.g. ``import './duck.js'``),
or a CSS/image file you're referencing won't be found, and you'll see a 404
error in your browser's console. You'll also notice that the 404 URL is missing
the version hash in the filename (e.g. a 404 to ``/assets/duck.js`` instead of
a path like ``/assets/duck.1b7a64b3b3d31219c262cf72521a5267.js``).

This is usually because the path is wrong. If you're referencing the file
directly in a Twig template:

.. code-block:: html+twig

        <img src="{{ asset('images/duck.png') }}">

Then the path that you pass ``asset()`` should be the "logical path" to the
file. Use the ``debug:asset-map`` command to see all valid logical paths
in your app.

More likely, you're importing the failing asset from a CSS file (e.g.
``@import url('other.css')``) or a JavaScript file:

.. code-block:: javascript

    // assets/controllers/farm-controller.js
    import '../farm/chicken.js';

When doing this, the path should be *relative* to the file that's importing it
(and, in JavaScript files, should start with ``./`` or ``../``). In this case,
``../farm/chicken.js`` would point to ``assets/farm/chicken.js``. To
see a list of *all* invalid imports in your app, run:

.. code-block:: terminal

    $ php bin/console cache:clear
    $ php bin/console debug:asset-map

Any invalid imports will show up as warnings on top of the screen (make sure
you have ``symfony/monolog-bundle`` installed):

.. code-block:: text

    WARNING   [asset_mapper] Unable to find asset "../images/ducks.png" referenced in "assets/styles/app.css".
    WARNING   [asset_mapper] Unable to find asset "./ducks.js" imported from "assets/app.js".

Missing Asset Warnings on Commented-out Code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The AssetMapper component looks in your JavaScript files for ``import`` lines so
that it can :ref:`automatically add them to your importmap <automatic-import-mapping>`.
This is done via regex and works very well, though it isn't perfect. If you
comment-out an import, it will still be found and added to your importmap. That
doesn't harm anything, but could be surprising.

If the imported path cannot be found, you'll see warning log when that asset
is being built, which you can ignore.

.. _asset-mapper-deployment:

Deploying with the AssetMapper Component
----------------------------------------

When you're ready to deploy, "compile" your assets during deployment:

.. code-block:: terminal

    $ php bin/console asset-map:compile

That's it! This will write all your assets into the ``public/assets/`` directory,
along with a few JSON files so that the ``importmap`` can be rendered lightning fast.

But to make sure your site is performant, be sure that your web server
(or a proxy) is running HTTP/2, is compressing your assets and setting
long-lived Expires headers on them. See :ref:`Optimization <optimization>` for
more details.

.. _optimization:

Optimizing Performance
----------------------

To make your AssetMapper-powered site fly, there are a few things you need to
do. If you want to take a shortcut, you can use a service like `Cloudflare`_,
which will automatically do most of these things for you:

- **Use HTTP/2**: Your web server **must** be running HTTP/2 (or HTTP/3) so the
  browser can download assets in parallel. HTTP/2 is automatically enabled in Caddy
  and can be activated in Nginx and Apache. Or, proxy your site through a
  service like Cloudflare, which will automatically enable HTTP/2 for you.

- **Compress your assets**: Your web server should compress (e.g. using gzip)
  your assets (JavaScript, CSS, images) before sending them to the browser. This
  is automatically enabled in Caddy and can be activated in Nginx and Apache.
  Or, proxy your site through a service like Cloudflare, which will
  automatically compress your assets for you. In Cloudflare, you can also
  enable `auto minify`_ to further compress your assets (e.g. removing
  whitespace and comments from JavaScript and CSS files).

- **Set long-lived Expires headers**: Your web server should set long-lived
  Expires headers on your assets. Because the AssetMapper component includes a version
  hash in the filename of each asset, you can safely set the Expires header
  to a very long time in the future (e.g. 1 year). This isn't automatic in
  any web server, but can be easily enabled.

Once you've done these things, you can use a tool like `Lighthouse`_ to
validate the performance of your site!

.. _performance-preloading:

Performance: Add Preloading
~~~~~~~~~~~~~~~~~~~~~~~~~~~

One common issue that LightHouse may report is:

    Avoid Chaining Critical Requests

Some items in this list are fine. But if this list is long or some items are
multiple-levels deep, that *is* something you should fix with "preloading".
To understand the problem, imagine that you have this setup:

- ``assets/app.js`` imports ``./duck.js``
- ``assets/duck.js`` imports ``bootstrap``

When the browser downloads the page, this happens:

1. The browser downloads ``assets/app.js``;
2. It *then* sees the ``./duck.js`` import and downloads ``assets/duck.js``;
3. It *then* sees the ``bootstrap`` import and downloads ``assets/bootstrap.js``.

Instead of downloading all 3 files in parallel, the browser is forced to
download them one-by-one as it discovers them. This is hurts performance. To fix
this, in ``importmap.php``, add a ``preload`` key to the ``app`` entry, which
points to the ``assets/app.js`` file. Actually, this should already be
done for you::

    // importmap.php
    return [
        'app' => [
            'path' => 'app.js',
            'preload' => true,
        ],
        // ...
    ];

Thanks to this, the AssetMapper component will render a "preload" tag onto your page
for ``assets/app.js`` *and* any other JavaScripts files that it imports using
a relative path (i.e. starting with ``./`` or ``../``):

.. code-block:: html

    <link rel="preload" href="/assets/app.js" as="script">
    <link rel="preload" href="/assets/duck.js" as="script">

This tells the browser to start downloading both of these files immediately,
even though it hasn't yet seen the ``import`` statement for ``assets/duck.js``

You'll also want to preload ``bootstrap`` as well, which you can do in the
same way::

    // importmap.php
    return [
        // ...
        'bootstrap' => [
            'path' => '...',
            'preload' => true,
        ],
    ];

.. note::

    As described above, when you preload ``assets/app.js``, the AssetMapper component
    find all of the JavaScript files that it imports using a **relative** path
    and preloads those as well. However, it does not currently do this when
    you import "packages" (e.g. ``bootstrap``). These packages will already
    live in your ``importmap.php`` file, so their preload setting is handled
    explicitly in that file.

Frequently Asked Questions
--------------------------

Does the AssetMapper Component Combine Assets?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Nope! But that's because this is no longer necessary!

In the past, it was common to combine assets to reduce the number of HTTP
requests that were made. Thanks to advances in web servers like
HTTP/2, it's typically not a problem to keep your assets separate and let the
browser download them in parallel. In fact, by keeping them separate, when
you update one asset, the browser can continue to use the cached version of
all of your other assets.

See :ref:`Optimization <optimization>` for more details.

Does the AssetMapper Component Minify Assets?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Nope! Minifying or compressing assets *is* important, but can be
done by your web server or a service like Cloudflare. See
:ref:`Optimization <optimization>` for more details.

Is the AssetMapper Component Production Ready? Is it Performant?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Yes! Very! The AssetMapper component leverages advances in browser technology (like
importmaps and native ``import`` support) and web servers (like HTTP/2, which allows
assets to be downloaded in parallel). See the other questions about minimization
and combination and :ref:`Optimization <optimization>` for more details.

The https://ux.symfony.com site runs on the AssetMapper component and has a 99%
Google Lighthouse score.

Does the AssetMapper Component work in All Browsers?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Yup! Features like importmaps and the ``import`` statement are supported
in all modern browsers, but the AssetMapper component ships with an `es module shim`_
to support ``importmap`` in old browsers. So, it works everywhere (see note
below).

Inside your own code, if you're relying on modern `ES6`_ JavaScript features
like the `class syntax`_, this is supported in all but the oldest browsers.
If you *do* need to support very old browsers, you should use a tool like
:ref:`Encore <frontend-webpack-encore>` instead of the AssetMapper component.

.. note::

    The `import statement`_ can't be polyfilled or shimmed to work on *every*
    browser. However, only the **oldest** browsers don't support it - basically
    IE 11 (which is no longer supported by Microsoft and has less than .4%
    of global usage).

    The ``importmap`` feature **is** shimmed to work in **all** browsers by the
    AssetMapper component. However, the shim doesn't work with "dynamic" imports:

    .. code-block:: javascript

        // this works
        import { add } from './math.js';

        // this will not work in the oldest browsers
        import('./math.js').then(({ add }) => {
            // ...
        });

    If you want to use dynamic imports and need to support certain older browsers
    (https://caniuse.com/import-maps), you can use an ``importShim()`` function
    from the shim: https://www.npmjs.com/package/es-module-shims#user-content-polyfill-edge-case-dynamic-import

Can I Use with Sass or Tailwind?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sure! See :ref:`Using Tailwind CSS <asset-mapper-tailwind>` or :ref:`Using Sass <asset-mapper-sass>`.

Can I use with TypeScript, JSX or Vue?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Probably not.

TypeScript, by its very nature, requires a build step.

JSX *can* be compiled directly to a native JavaScript file but if you're using a lot of JSX,
you'll probably want to use a tool like :ref:`Encore <frontend-webpack-encore>`.
See the `UX React Documentation`_ for more details about using with the AssetMapper
component.

Vue files *can* be written in native JavaScript, and those *will* work with
the AssetMapper component. But you cannot write single-file components (i.e. ``.vue``
files) with component, as those must be used in a build system. See the
`UX Vue.js Documentation`_ for more details about using with the AssetMapper
component.

.. _asset-mapper-tailwind:

Using Tailwind CSS
------------------

.. seealso::

    Check out `symfonycasts/tailwind-bundle`_ for an even easier way to use
    Tailwind with Symfony.

Want to use the `Tailwind`_ CSS framework with the AssetMapper component? No problem.
First, install the ``tailwindcss`` binary. This can be installed via npm (run
``npm --init`` if you don't already have a ``package.json`` file):

.. code-block:: terminal

    $ npm install -D tailwindcss

Or you can install the `Tailwind standalone binary`_, which does not require Node.

Next, generate the ``tailwind.config.js`` file:

.. code-block:: terminal

    $ npx tailwindcss init

    # or with the standalone binary:
    $ ./tailwindcss init

Update ``tailwind.config.js`` to point to your template and JavaScript files:

.. code-block:: diff

    // tailwind.config.js
    // ....

    -   content: [],
    +   content: [
    +       "./assets/**/*.js",
    +       "./templates/**/*.html.twig",
    +   ],

Then add the base lines to your ``assets/styles/app.css`` file:

.. code-block:: css

    /* assets/styles/app.css */
    @tailwind base;
    @tailwind components;
    @tailwind utilities;

Now that Tailwind is setup, run the ``tailwindcss`` binary in "watch" mode
to build the CSS file to a new ``assets/app.built.css`` path:

.. code-block:: terminal

    $ npx tailwindcss build -i assets/styles/app.css -o assets/styles/app.built.css --watch

    # or with the standalone binary:
    $ ./tailwindcss build -i assets/styles/app.css -o assets/styles/app.built.css --watch

Finally, instead of pointing directly to ``styles/app.css`` in your template,
point to the new ``styles/app.built.css`` file:

.. code-block:: diff

    {# templates/base.html.twig #}

    - <link rel="stylesheet" href="{{ asset('styles/app.css') }}">
    + <link rel="stylesheet" href="{{ asset('styles/app.built.css') }}">

Done! You can choose to ignore the ``assets/styles/app.built.css`` file from Git
or commit it to ease deployment.

.. _asset-mapper-sass:

Using Sass
----------

To use Sass with the AssetMapper component, install the sass binary. You can
`download it from the latest GitHub release`_ (does not require Node) or
install it via npm:

.. code-block:: terminal

    $ npm install -D dart-sass

Next, create an ``assets/styles/app.scss`` file and write some dazzling CSS:

.. code-block:: scss

    /* assets/styles/app.scss */
    $primary-color: skyblue;

    body {
        background: $primary-color;
    }

Then, run the ``dart-sass`` binary in "watch" mode to build the CSS file to a
new ``assets/styles/app.css`` path:

.. code-block:: terminal

    $ npx dart-sass assets/styles/app.scss assets/styles/app.css --watch

    # or with the standalone binary:
    ./sass assets/styles/app.scss assets/styles/app.css --watch

In your template, point directly to the ``styles/app.css`` file (``base.html.twig``
points to ``styles/app.css`` by default):

.. code-block:: html+twig

    {# templates/base.html.twig #}
    <link rel="stylesheet" href="{{ asset('styles/app.css') }}">

Done! You can choose to ignore the ``assets/styles/app.css`` file from Git
or commit it to ease deployment. To prevent the source ``.scss`` files from being
exposed to the public, see :ref:`exclude_patterns <excluded_patterns>`.

Third-Party Bundles & Custom Asset Paths
----------------------------------------

All bundles that have a ``Resources/public/`` or ``public/`` directory will
automatically have that directory added as an "asset path", using the namespace:
``bundles/<BundleName>``. For example, if you're using `BabdevPagerfantaBundle`_
and you run the ``debug:asset-map`` command, you'll see an asset whose logical
path is ``bundles/babdevpagerfanta/css/pagerfanta.css``.

This means you can render these assets in your templates using the
``asset()`` function:

.. code-block:: html+twig

    <link rel="stylesheet" href="{{ asset('bundles/babdevpagerfanta/css/pagerfanta.css') }}">

Actually, this path - ``bundles/babdevpagerfanta/css/pagerfanta.css`` - already
works in applications *without* the AssetMapper component, because the ``assets:install``
command copies the assets from bundles into ``public/bundles/``. However, when
the AssetMapper component is enabled, the ``pagerfanta.css`` file will automatically
be versioned! It will output something like:

.. code-block:: html+twig

    <link rel="stylesheet" href="/assets/bundles/babdevpagerfanta/css/pagerfanta-ea64fc9c55f8394e696554f8aeb81a8e.css">

Overriding 3rd-Party Assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to override a 3rd-party asset, you can do that by creating a
file in your ``assets/`` directory with the same name. For example, if you
want to override the ``pagerfanta.css`` file, create a file at
``assets/bundles/babdevpagerfanta/css/pagerfanta.css``. This file will be
used instead of the original file.

.. note::

    If a bundle renders their *own* assets, but they use a non-default
    :ref:`asset package <asset-packages>`, then the AssetMapper component will
    not be used. This happens, for example, with `EasyAdminBundle`_.

Importing Assets Outside of the ``assets/`` Directory
-----------------------------------------------------

You cannot currently import assets that live outside of your asset path
(i.e. the ``assets/`` directory). For example, this won't work:

.. code-block:: css

    /* assets/styles/app.css */

    /* you cannot reach above assets/ */
    @import url('../../vendor/babdev/pagerfanta-bundle/Resources/public/css/pagerfanta.css');
    /* using a logical path won't work either */
    @import url('bundles/babdevpagerfanta/css/pagerfanta.css');

This wouldn't work either:

.. code-block:: javascript

    // assets/app.js

    // you cannot reach above assets/
    import '../vendor/symfony/ux-live-component/assets/dist/live_controller.js';
    // using a logical path won't work either (the "@symfony/ux-live-component" path is added by the LiveComponent library)
    import '@symfony/ux-live-component/live_controller.js';
    // importing like a JavaScript "package" won't work
    import '@symfony/ux-live-component';

For CSS files, you can solve this by adding a ``link`` tag to your template
instead of using the ``@import`` statement.

For JavaScript files, you can add an entry to your ``importmap`` file:

.. code-block:: terminal

    $ php bin/console importmap:require @symfony/ux-live-component --path=vendor/symfony/ux-live-component/assets/dist/live_controller.js

Then you can ``import '@symfony/ux-live-component'`` like normal. The ``--path``
option tells the command to point to a local file instead of a package.
In this case, the ``@symfony/ux-live-component`` argument could be anything:
whatever you use here will be the string that you can use in your ``import``.

If you get an error like this:

    The "some/package" importmap entry contains the path "vendor/some/package/assets/foo.js"
    but it does not appear to be in any of your asset paths.

It means that you're pointing to a valid file, but that file isn't in any of
your asset paths. You can fix this by adding the path to your ``asset_mapper.yaml``
file:

.. code-block:: yaml

    # config/packages/asset_mapper.yaml
    framework:
        asset_mapper:
            paths:
                - assets/
                - vendor/some/package/assets

Then try the command again.

Configuration Options
---------------------

You can see every available configuration option and some info by running:

.. code-block:: terminal

    $ php bin/console config:dump framework asset_mapper

Some of the more important options are described below.

``framework.asset_mapper.paths``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This config holds all of the directories that will be scanned for assets. This
can be a simple list:

.. code-block:: yaml

    framework:
        asset_mapper:
            paths:
                - assets/
                - vendor/some/package/assets

Of you can give each path a "namespace" that will be used in the asset map:

.. code-block:: yaml

    framework:
        asset_mapper:
            paths:
                assets/: ''
                vendor/some/package/assets/: 'some-package'

In this case, the "logical path" to all of the files in the ``vendor/some/package/assets/``
directory will be prefixed with ``some-package`` - e.g. ``some-package/foo.js``.

.. _excluded_patterns:

``framework.asset_mapper.excluded_patterns``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is a list of glob patterns that will be excluded from the asset map:

.. code-block:: yaml

    framework:
        asset_mapper:
            excluded_patterns:
                - '*/*.scss'

You can use the ``debug:asset-map`` command to double-check that the files
you expect are being included in the asset map.

``framework.asset_mapper.importmap_script_attributes``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is a list of attributes that will be added to the ``<script>`` tags
rendered by the ``{{ importmap() }}`` Twig function:

.. code-block:: yaml

    framework:
        asset_mapper:
            importmap_script_attributes:
                crossorigin: 'anonymous'

Page-Specific CSS & JavaScript
------------------------------

Sometimes you may choose to include CSS or JavaScript files only on certain
pages. To add a CSS file to a specific page, create the file, then add a
``link`` tag to it like normal:

.. code-block:: html+twig

    {# templates/products/checkout.html.twig #}
    {% block stylesheets %}
        {{ parent() }}

        <link rel="stylesheet" href="{{ asset('styles/checkout.css') }}">
    {% endblock %}

For JavaScript, first create the new file (e.g. ``assets/checkout.js``). Then,
add a ``script``` tag that imports it:

.. code-block:: html+twig

    {# templates/products/checkout.html.twig #}
    {% block javascripts %}
        {{ parent() }}

        <script type="module">
            import '{{ asset('checkout.js') }}';
        </script>
    {% endblock %}

This instructs your browser to download and execute the file.

In this setup, the normal ``app.js`` file will be executed first and *then*
``checkout.js``. If, for some reason, you want to execute *only* ``checkout.js``
and *not* ``app.js``, override the ``javascript`` block entirely and render
``checkout.js`` through the ``importmap()`` function:

.. code-block:: html+twig

    {# templates/products/checkout.html.twig #}
    {% block javascripts %}
        <script type="module">
            {{ importmap(asset('checkout.js')) }}
        </script>
    {% endblock %}

The important thing is that the ``importmap()`` function must be called exactly
*one* time on each page. It outputs the ``importmap`` and also adds a
``<script type="module">`` tag that loads the ``app.js`` file or whatever path
you pass to ``importmap()``.

.. note::

    If you look at the source of your page, by default, the ``<script type="module">``
    from ``importmap()`` will contain ``import 'app';`` - not something like
    ``import ``/assets/app-4e986c1a2318dd050b1d47.js``. Both would work - but
    because ``app`` appears in your ``importmap.php``, the browser will read ``app``
    from the ``importmap`` on the page and ultimately load ``/assets/app-4e986c1a2318dd050b1d47.js``

The AssetMapper Component Caching System in dev
-----------------------------------------------

When developing your app in debug mode, the AssetMapper component will calculate the
content of each asset file and cache it. Whenever that file changes, the component
will automatically re-calculate the content.

The system also accounts for "dependencies": If ``app.css`` contains
``@import url('other.css')``, then the ``app.css`` file contents will also be
re-calculated whenever ``other.css`` changes. This is because the version hash of ``other.css``
will change... which will cause the final content of ``app.css`` to change, since
it includes the final ``other.css`` filename inside.

Mostly, this system just works. But if you have a file that is not being
re-calculated when you expect it to, you can run:

.. code-block:: terminal

    $ php bin/console cache:clear

This will force the AssetMapper component to re-calculate the content of all files.

.. _latest asset-mapper recipe: https://github.com/symfony/recipes/tree/main/symfony/asset-mapper
.. _import statement: https://caniuse.com/es6-module-dynamic-import
.. _ES6: https://caniuse.com/es6
.. _npm package: https://www.npmjs.com
.. _importmap: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script/type/importmap
.. _bootstrap: https://www.npmjs.com/package/bootstrap
.. _es module shim: https://www.npmjs.com/package/es-module-shims
.. _jsdelivr.com: https://www.jsdelivr.com/
.. _highlight.js: https://www.npmjs.com/package/highlight.js
.. _class syntax: https://caniuse.com/es6-class
.. _UX React Documentation: https://symfony.com/bundles/ux-react/current/index.html
.. _UX Vue.js Documentation: https://symfony.com/bundles/ux-vue/current/index.html
.. _auto minify: https://developers.cloudflare.com/support/speed/optimization-file-size/using-cloudflare-auto-minify/
.. _Lighthouse: https://developers.google.com/web/tools/lighthouse
.. _Tailwind: https://tailwindcss.com/
.. _Tailwind standalone binary: https://tailwindcss.com/blog/standalone-cli
.. _download it from the latest GitHub release: https://github.com/sass/dart-sass/releases/latest
.. _BabdevPagerfantaBundle: https://github.com/BabDev/PagerfantaBundle
.. _Cloudflare: https://www.cloudflare.com/
.. _EasyAdminBundle: https://github.com/EasyCorp/EasyAdminBundle
.. _symfonycasts/tailwind-bundle: https://github.com/SymfonyCasts/tailwind-bundle
