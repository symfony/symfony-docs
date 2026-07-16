Front-end Tools: Handling CSS & JavaScript
==========================================

Symfony gives you the flexibility to choose any front-end tools you want. There
are generally two approaches:

#. :ref:`building your HTML with PHP & Twig <frontend-twig-php>`;
#. :ref:`building your frontend with a JavaScript framework <frontend-js>` like React, Vue, Svelte, etc.

Both work great - and are discussed below.

.. _frontend-twig-php:

Using PHP & Twig
----------------

Symfony comes with several powerful options to help you build a modern and fast frontend:

* :ref:`AssetMapper <frontend-asset-mapper>` (recommended for new projects) runs
  entirely in PHP, doesn't require any build step and leverages modern web standards.

* :ref:`Symfony Reprise <frontend-reprise>` brings first-class Symfony integration
  to modern JavaScript bundlers (`Vite`_ and `Rsbuild`_), the way Webpack Encore did
  for Webpack.

* :ref:`Webpack Encore <frontend-webpack-encore>` is built with `Node.js`_ on top of
  `Webpack`_. It's the original bundler integration; for new bundler-based projects,
  Symfony Reprise is the more modern and faster alternative.

================================  ==================================  ===========================  ==========
                                  AssetMapper                         Reprise                      Encore
================================  ==================================  ===========================  ==========
Production Ready?                 yes                                 no                           yes
Stable?                           yes                                 no                           yes
Requirements                      none                                Node.js                      Node.js
Requires a build step?            no                                  yes                          yes
Works in all browsers?            yes                                 yes                          yes
Supports `Stimulus/UX`_           yes                                 yes                          yes
Supports Sass/Tailwind            :ref:`yes <asset-mapper-tailwind>`  yes                          yes
Supports React, Vue, Svelte?      yes :ref:`[1] <ux-note-1>`          yes                          yes
Supports TypeScript               :ref:`yes <asset-mapper-ts>`        yes                          yes
Removes comments from JavaScript  no :ref:`[2] <ux-note-2>`           yes                          yes
Removes comments from CSS         no :ref:`[2] <ux-note-2>`           yes                          yes :ref:`[4] <ux-note-4>`
Versioned assets                  always                              always                       optional
Can update 3rd party packages     yes                                 no :ref:`[3] <ux-note-3>`    no :ref:`[3] <ux-note-3>`
================================  ==================================  ===========================  ==========

.. _ux-note-1:

**[1]** Using JSX (React), Vue, etc with AssetMapper is possible, but you'll
need to use their native tools for pre-compilation. Also, some features (like
Vue single-file components) cannot be compiled down to pure JavaScript that can
be executed by a browser.

.. _ux-note-2:

**[2]** You can install the `SensioLabs Minify Bundle`_ to minify CSS/JS code
(and remove all comments) when compiling assets with AssetMapper.

.. _ux-note-3:

**[3]** If you use ``npm``, there are update checkers available (e.g. ``npm-check``).

.. _ux-note-4:

**[4]** CSS comments can be removed using `CssMinimizerPlugin`_, which is included
in Webpack Encore and configurable via ``Encore.configureCssMinimizerPlugin()``.

.. _frontend-asset-mapper:

AssetMapper (Recommended)
~~~~~~~~~~~~~~~~~~~~~~~~~

.. screencast::

    Do you prefer video tutorials? Check out the `AssetMapper screencast series`_.

AssetMapper is the recommended system for handling your assets. It runs entirely
in PHP with no complex build step or dependencies. It does this by leveraging
the ``importmap`` feature of your browser, which is available in all browsers thanks
to a polyfill.

:doc:`Read the AssetMapper Documentation </frontend/asset_mapper>`

.. _frontend-reprise:

Symfony Reprise
~~~~~~~~~~~~~~~

.. warning::

    Symfony Reprise is **experimental**: its API and behavior may still change,
    sometimes drastically.

`Symfony Reprise`_ is the modern, faster alternative to Webpack Encore. Instead of
wrapping Webpack, it brings first-class Symfony integration to today's JavaScript
bundlers, `Vite`_ and `Rsbuild`_, which build on much faster tooling (esbuild,
Rolldown and Rspack).

Vite and Rsbuild already handle Sass/PostCSS, TypeScript, JSX/Vue/Svelte, code
splitting, minification and Hot Module Replacement on their own. Reprise adds the
Symfony-side glue those bundlers leave out: ``entrypoints.json`` and ``manifest.json``
generation, asset versioning, the dev server wiring, Twig functions to render the
``<script>`` and ``<link>`` tags, and Symfony UX / Stimulus integration.

Because it generates the same ``entrypoints.json`` and ``manifest.json`` as Webpack
Encore, moving from Encore is mostly a tag-for-tag swap in your templates
(``encore_entry_*`` becomes ``reprise_entry_*``).

Install it with Composer and npm, then pick your bundler (Vite or Rsbuild):

.. code-block:: terminal

    $ composer require symfony/reprise
    $ npm install @symfony/reprise --save-dev

For the full configuration and usage, read the `Symfony Reprise documentation`_.

.. _frontend-webpack-encore:

Webpack Encore
~~~~~~~~~~~~~~

.. screencast::

    Do you prefer video tutorials? Check out the `Webpack Encore screencast series`_.

.. note::

    Webpack Encore is fully supported, but **for new projects AssetMapper is
    recommended**. Choose Encore when you already use it, or when you need a full
    JavaScript bundler.

`Webpack Encore`_ is a simpler way to integrate `Webpack`_ into your application.
It wraps Webpack, giving you a clean & powerful API for bundling JavaScript modules,
pre-processing CSS & JS and compiling and minifying assets.

.. note::

    Webpack Encore is now Symfony's legacy bundler-based option. It's still
    supported, but if you're starting a new project that needs a bundler, consider
    :ref:`Symfony Reprise <frontend-reprise>`, a more modern and faster alternative
    built on Vite and Rsbuild.

:doc:`Read the Encore Documentation </frontend/encore/index>`

Switch from AssetMapper
^^^^^^^^^^^^^^^^^^^^^^^

By default, new Symfony webapp projects (created with ``symfony new --webapp myapp``)
use AssetMapper. If you still need to use Webpack Encore, use the following steps to
switch. This is best done on a new project and provides the same features (Turbo/Stimulus)
as the default webapp.

.. code-block:: terminal

    # Remove AssetMapper & Turbo/Stimulus temporarily
    $ composer remove symfony/ux-turbo symfony/asset-mapper symfony/stimulus-bundle

    # Add Webpack Encore & Turbo/Stimulus back
    $ composer require symfony/webpack-encore-bundle symfony/ux-turbo symfony/stimulus-bundle

    # Install & Build Assets
    $ npm install
    $ npm run dev

Stimulus & Symfony UX Components
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once you've installed AssetMapper or Webpack Encore, it's time to start building your
front-end. You can write your JavaScript however you want, but we recommend
using `Stimulus`_, `Turbo`_ and a set of tools called `Symfony UX`_.

To learn about Stimulus & the UX Components, see
the `StimulusBundle Documentation`_

.. _frontend-js:

Using a Front-end Framework (React, Vue, Svelte, etc)
-----------------------------------------------------

.. screencast::

    Do you prefer video tutorials? Check out the `API Platform screencast series`_.

If you want to use a front-end framework (Next.js, React, Vue, Svelte, etc),
we recommend using their native tools and using Symfony as a pure API. A wonderful
tool to do that is `API Platform`_. Their standard distribution comes with a
Symfony-powered API backend, frontend scaffolding in Next.js (other frameworks
are also supported) and a React admin interface. It comes fully Dockerized and even
contains a web server.

Other Front-End Articles
------------------------

* :doc:`/frontend/create_ux_bundle`
* :doc:`/frontend/custom_version_strategy`
* :doc:`/frontend/server-data`

.. _`Webpack Encore`: https://www.npmjs.com/package/@symfony/webpack-encore
.. _`Webpack`: https://webpack.js.org/
.. _`Node.js`: https://nodejs.org/
.. _`Webpack Encore screencast series`: https://symfonycasts.com/screencast/webpack-encore
.. _`StimulusBundle Documentation`: https://symfony.com/bundles/StimulusBundle/current/index.html
.. _`Stimulus/UX`: https://symfony.com/bundles/StimulusBundle/current/index.html
.. _`Stimulus`: https://stimulus.hotwired.dev/
.. _`Turbo`: https://turbo.hotwired.dev/
.. _`Symfony UX`: https://ux.symfony.com
.. _`API Platform`: https://api-platform.com/
.. _`SensioLabs Minify Bundle`: https://github.com/sensiolabs/minify-bundle
.. _`AssetMapper screencast series`: https://symfonycasts.com/screencast/asset-mapper
.. _`API Platform screencast series`: https://symfonycasts.com/screencast/api-platform
.. _`CssMinimizerPlugin`: https://webpack.js.org/plugins/css-minimizer-webpack-plugin
.. _`Symfony Reprise`: https://github.com/symfony/reprise
.. _`Symfony Reprise documentation`: https://github.com/symfony/reprise/blob/main/doc/index.rst
.. _`Vite`: https://vite.dev/
.. _`Rsbuild`: https://rsbuild.dev/
