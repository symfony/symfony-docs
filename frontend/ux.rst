.. index::
   single: Symfony UX

The Symfony UX Initiative & Packages
====================================

.. tip::

    Check out live demos of Symfony UX at `https://ux.symfony.com`_!

Symfony UX is an initiative and set of libraries to seamlessly
integrate JavaScript tools into your application. For example,
want to render a chart with `Chart.js`_? Use `UX Chart.js`_ to build the
chart in PHP. The JavaScript is handled for you automatically.

Behind the scenes, the UX packages leverage `Stimulus`_: a small, but
powerful library for binding JavaScript functionality to elements on
your page.

Installing Symfony UX
---------------------

Before you install any specific UX library, make sure you've installed
:doc:`Webpack Encore </frontend/encore/installation>`.

If you already have it installed, make sure you have an
``assets/bootstrap.js`` file (this initializes Stimulus & the UX packages),
an ``assets/controllers.json`` file (this controls the 3rd party UX packages that
you've installed) and ``.enableStimulusBridge('./assets/controllers.json')`` in
your ``webpack.config.js`` file. If these are missing, try upgrading the
``symfony/webpack-encore-bundle`` Flex recipe. See
:ref:`Upgrading Flex Recipes <updating-flex-recipes>`.

.. _ux-packages-list:

All Symfony UX Packages
-----------------------

.. include:: /frontend/_ux-libraries.rst.inc

Stimulus Tools around the World
-------------------------------

Because Stimulus is used by developers outside of Symfony, many tools
exist beyond the UX packages:

* `stimulus-use`_: Add composable behaviors to your Stimulus controllers, like
  debouncing, detecting outside clicks and many other things.

* `stimulus-components`_ A large number of pre-made Stimulus controllers, like for
  Copying to clipboard, Sortable, Popover (similar to tooltips) and much more.

How does Symfony UX Work?
-------------------------

When you install a UX PHP package, Symfony Flex will automatically update your
``package.json`` file to point to a "virtual package" that lives inside the
PHP package. For example:

.. code-block:: json

    {
        "devDependencies": {
            "...": "",
            "@symfony/ux-chartjs": "file:vendor/symfony/ux-chartjs/Resources/assets"
        }
    }

This gives you a *real* Node package (e.g. ``@symfony/ux-chartjs``) that, instead
of being downloaded, points directly to files that already live in your ``vendor/``
directory.

The Flex recipe will usually also update your ``assets/controllers.json`` file
to add a new Stimulus controller to your app. For example:

.. code-block:: json

    {
        "controllers": {
            "@symfony/ux-chartjs": {
                "chart": {
                    "enabled": true,
                    "fetch": "eager"
                }
            }
        },
        "entrypoints": []
    }

Finally, your ``assets/bootstrap.js`` file - working with the `@symfony/stimulus-bridge`_ -
package will automatically register:

* All files in ``assets/controllers/`` as Stimulus controllers;
* And all controllers described in ``assets/controllers.json`` as Stimulus controllers.

The end result: you install a package, and you instantly have a Stimulus
controller available! In this example, it's called
``@symfony/ux-chartjs/chart``. Well, technically, it will be called
``symfony--ux-chartjs--chart``. However, you can pass the original name
into the ``{{ stimulus_controller() }}`` function from WebpackEncoreBundle, and
it will normalize it:

.. code-block:: html+twig

    <div {{ stimulus_controller('@symfony/ux-chartjs/chart') }}>

    <!-- will render as: -->
    <div data-controller="symfony--ux-chartjs--chart">

Lazy Controllers
----------------

By default, all of your controllers (i.e. files in ``assets/controllers/`` +
controllers in ``assets/controllers.json``) will be downloaded and loaded on
every page.

Sometimes you may have a controller that is only used on some pages, or maybe
only in your admin area. In that case, you can make the controller "lazy". When
a controller is lazy, it is *not* downloaded on initial page load. Instead, as
soon as an element appears on the page matching the controller (e.g.
``<div data-controller="hello">``), the controller - and anything else it imports -
will be lazyily-loaded via Ajax.

To make one of your custom controllers lazy, add a special comment on top:

.. code-block:: javascript

    import { Controller } from '@hotwired/stimulus';

    /* stimulusFetch: 'lazy' */
    export default class extends Controller {
        // ...
    }

To make a third-party controller lazy, in ``assets/controllers.json``, set
``fetch`` to ``lazy``.

.. note::

    If you write your controllers using TypeScript, make sure
    ``removeComments`` is not set to ``true`` in your TypeScript config.

More Advanced Setup
-------------------

To learn about more advanced options, read about `@symfony/stimulus-bridge`_,
the Node package that is responsible for a lot of the magic.

.. _`Chart.js`: https://www.chartjs.org/
.. _`UX Chart.js`: https://symfony.com/bundles/ux-chartjs/current/index.html
.. _`Stimulus`: https://stimulus.hotwired.dev/
.. _`@symfony/stimulus-bridge`: https://github.com/symfony/stimulus-bridge
.. _`stimulus-use`: https://stimulus-use.github.io/stimulus-use
.. _`stimulus-components`: https://stimulus-components.netlify.app/
.. _`https://ux.symfony.com`: https://ux.symfony.com
