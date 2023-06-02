Asset Mapper: Simple, Modern CSS & JS Management
================================================

Asset Mapper lets you write modern JavaScript and CSS without the complexity
of using a bundler. Browsers *already* support modern JavaScript features
like the ``import`` statement and ES6 classes. With just a little help,
you can have a production-ready setup without a build system.

Asset Mapper has two main features:

1. :ref:`Mapping & Versioning Asset <mapping-assets>`: All files inside of ``assets/``
   are automatically made available publicly and **versioned**. If you have an
   ``assets/styles/app.css`` file, you can reference it in a template with
   ``{{ asset('styles/app.css') }}``. The final URL will include a version hash, like
   ``/assets/styles/app-3c16d9220694c0e56d8648f25e6035e9.css``.

1. :ref:`Importmaps <importmaps-javascript>`: A native browser feature that makes it easier
   to use the JavaScript ``import`` statement (e.g. ``import { Modal } from 'bootstrap'``)
   without a build system. It's supported in all browsers (thanks to a polyfill)
   and is a W3C standard.

AssetMapper is currently an `experimental`_ component and may change in future
releases.

Installation
------------

To install the AssetMapper component, run:

.. code-block:: terminal

    $ composer require symfony/asset-mapper symfony/asset symfony/twig-pack

This will install AssetMapper and also make sure that you have the Asset Component
and Twig available.

If you're using :ref:`Symfony Flex <symfony-flex>`, you're done! The recipe just
added a number of files:

* ``assets/app.js`` Your main JavaScript file
* ``assets/styles/app.css`` Your main CSS file
* ``config/packages/asset_mapper.yaml`` Config file where you define your asset "paths"
* ``importmap.php`` Your importmap config file

It also *updated* your ``templates/base.html.twig`` file:

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

Asset mapper works by defining directories/paths of assets that you want to expose
publicly. These assets are then versioned and easy to reference. Thanks to the
``asset_mapper.yaml`` file, your app starts with one mapped path: the ``assets/``
directory.

For example, if you create an ``assets/images/cat.png`` file, you can reference
it in a template with:

.. code-block:: twig

    <img src="{{ asset('images/cat.png') }}">

The path - ``images/cat.png`` - is relative to your mapped directory (``assets/``).
This is known as the **logical path** to your asset.

If you refresh the page and look at the final HTML, the URL will be something
like: ``/assets/images/cat-3c16d9220694c0e56d8648f25e6035e9.png``. If you change
the file, the version part of the URL will change automatically!

Serving Assets in dev vs prod
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the ``dev`` environment, the URL - ``/assets/images/cat-3c16d9220694c0e56d8648f25e6035e9.png``
is handled and returned by your Symfony app. For the ``prod`` environment, before
you deploy, you'll run:

.. code-block:: terminal

    $ php bin/console asset-map:compile

This will physically copy all of the files from your mapped directories into
the ``public/assets/`` directory so that they're served directly by your web server.
See :doc:`/frontend/asset_mapper/deploy` for more details.

Paths Inside of CSS Files
~~~~~~~~~~~~~~~~~~~~~~~~~

If you have a CSS file, like ``assets/styles/app.css``, you can reference other
assets inside of it using the normal CSS ``url()`` function with a relative path
to the target file:

.. code-block:: css

    /* assets/styles/app.css */
    .quack {
        background-image: url('../images/duck.png');
    }

The final ``app.css`` file will automatically be updated to include the correct,
versioned URL for ``cat.png``:

.. code-block:: css

    /* public/assets/styles/app-3c16d9220694c0e56d8648f25e6035e9.css */
    .quack {
        background-image: url('../images/duck-3c16d9220694c0e56d8648f25e6035e9.png');
    }

Debugging: Seeing All Mapped Assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To see all of the mapped assets in your app, run:

.. code-block:: terminal

    $ php bin/console debug:asset-map

This will print out a list of all the mapped paths:

.. code-block:: text

    Asset Mapper Paths
    ------------------

    --------- ------------------
     Path      Namespace prefix
    --------- ------------------
    assets

And all of the assets inside each path:

.. code-block:: text

    Mapped Assets
    -------------

    ------------------ ----------------------------------------------------
     Logical Path       Filesystem Path
    ------------------ ----------------------------------------------------
     app.js             assets/app.js
     styles/app.css     assets/styles/app.css
     images/duck.png    assets/images/duck.png

The "Logical Path" is the path that to use when referencing the asset, like
in a template.

.. _importmaps-javascript:

Importmaps & Writing JavaScript
-------------------------------

All modern browsers support the JavaScript `import statement`_ and modern
`ES6`_ features like classes. So, code like this "just works":

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

The ``assets/app.js`` file is already being loaded onto your page thanks
to the `{{ importmap() }}` Twig function, which we'll learn more about
soon. So, this code will work!

.. tip::

    When importing relative files, be sure to include the ``.js`` extension.
    Unlike in Node, the extension is required in the browser.

But to import a 3rd party library, like ``lodash``, you need to reference
the full URL:

.. code-block:: javascript

    import Duck from './duck.js';
    import _ from 'https://cdn.jsdelivr.net/npm/lodash@4.17.21/+esm';

    const duck = new Duck('Waddles');
    duck.quack(_.random(1, 5));

Needing to reference the full URL is a bad experience. Fortunately, AssetMapper
leverages a native `importmap`_ browser feature to fix this.

.. note::

    AssetMapper automatically includes a polyfill for older browsers that
    don't natively support importmaps.

importmap.php & Adding Packages to the ImportMap
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to use the `bootstrap`_ JavaScript package. First, add it to
your importmap via the ``importmap:require`` command. This command can be
used to download any Node package from `npmjs.com`_.

.. code-block:: terminal

    $ php bin/console importmap:require bootstrap

This will add the ``bootstrap`` package to ``importmap.php`` (the
``app`` entry that was already there):

    // importmap.php
    return [
        'app' => [
            'path' => 'app.js',
            'preload' => true,
        ],
        'bootstrap' => [
            'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm',
        ],
    ];

Now you import the ``bootstrap`` package like normal:

.. code-block:: javascript

    import { Alert } from 'bootstrap';
    // ...

This works thanks to the `{{ importmap() }}` Twig function in ``base.html.twig``,
which outputs an "importmap":

.. code-block:: html

    <script type="importmap">{
        "imports": {
            "app": "/assets/app-4e986c1a2318dd050b1d47db8d856278.js",
            "/assets/duck.js": "/assets/duck-1b7a64b3b3d31219c262cf72521a5267.js",
            "bootstrap": "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm"
        }
    }</script>

Your browser already knows how to read this importmap. Thanks to this, when
we import ``bootstrap`` in our code, the browser will know to download it from
the URL.

If you prefer to download the package locally, you can do that too:

.. code-block:: terminal

    $ php bin/console importmap:require bootstrap --download

This will download the package into an ``assets/vendor/`` directory and update
the ``importmap.php`` file to point to it. You *should* commit this file to
your repository.

.. note::

    Sometimes, a package - like ``bootstrap`` - will have one or more dependencies,
    like ``@popperjs/core``. The ``download`` option will download the main
    package *and* all of its dependencies.

The importmap & importing Relative Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``app.js`` file above imports ``./duck.js``. When you import a file using a
relative path, the browser looks for that file relative to the one importing
it. So, it would look for ``/assets/duck.js``. That URL *would* be correct,
except that the ``duck.js`` file is versioned. Fortunately, AssetMapper sees that
import and adds a mapping from ``/assets/duck.js`` to the correct, versioned
filename. The result: importing ``./duck.js`` just works!

Preloading, the Shim & Initializing "app.js"
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to the importmap, the ``{{ importmap() }}`` Twig function also renders
a few other things.

First, an `es module shim`_ is included to add support for ``importmap`` and a few related
features to older browsers.

.. code-block:: html

    <script async src="https://ga.jspm.io/npm:es-module-shims@1.7.2/dist/es-module-shims.js"></script>

Second, a set of "preloads" are rendered:

.. code-block:: html

    <link rel="modulepreload" href="/assets/app-4e986c1a2318dd050b1d47db8d856278.js">
    <link rel="modulepreload" href="/assets/duck-1b7a64b3b3d31219c262cf72521a5267.js">

In ``importmap.php``, each entry can have a ``preload`` option. If set to ``true``,
a ``<link rel="modulepreload">`` tag is rendered for that entry as well as for
any relative JavaScript files that it imports. This is a performance optimization
and you can learn more about it in the :doc:`AssetMapper Deploymeny </frontend/asset_mapper/deploy>`
guide.

Finally, the ``importmap()`` function renders one more line:

    <script type="module">import 'app';</script>

So far, we've output an ``importmap`` and even hinted to the browser that it
should preload some files. But we haven't actually told the browser to *load*
and execute any JavaScript. This line does that: it imports the ``app`` entry,
which causes the code in ``assets/app.js`` to be executed.

Importing Specific Files From a 3rd Party Package
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``bootstrap`` package contains a lot of files. But, in our JavaScript, we
only import the ``Alert`` class. If you look at the ``importmap.php`` file, you
can see that the ``bootstrap`` entry points to a directory:

    // importmap.php
    return [
        // ...
        'bootstrap' => [
            'url' => 'https://

Handling 3rd-Party CSS
----------------------

The ``importmap:require`` command

So far, we've only talked about JavaScript. But what about CSS? The ``bootstrap``
package also contains CSS files. To use them, you need to import them in your
JavaScript:

.. code-block:: javascript

    import 'bootstrap/dist/css/bootstrap.min.css';

---> 3rd party CSS
---> requiring paths inside a package
---> the imperfect import parsing
---> importmap:update
---> Sass, Tailwind

.. _latest asset-mapper recipe: https://github.com/symfony/recipes/tree/main/symfony/asset-mapper
.. _experimental: https://symfony.com/doc/current/contributing/code/experimental.html
.. _import statement: https://caniuse.com/es6-module-dynamic-import
.. _ES6: https://caniuse.com/es6
.. _importmap: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script/type/importmap
.. _bootstrap: https://www.npmjs.com/package/bootstrap
.. _`es module shim`: https://www.npmjs.com/package/es-module-shims
.. _npmjs.com: https://www.npmjs.com/
