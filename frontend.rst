Introduction
============

Symfony gives you the flexibility to choose any front-end tools you want. This could
be dead-simple - like putting CSS & JS directly in the ``public/`` directory - or
more advanced - like scaffolding your front-end with a tool like Next.js.

However, Symfony *does* come with two powerful options to help you build a modern,
fast frontend, *and* enjoy the process:

* :ref:`Webpack Encore <frontend-webpack-encore>` is a powerful tool built with `Node.js`_
  on top of `Webpack`_ that allows you to write modern CSS & JavaScript and handle
  things like JSX (React), Vue or TypeScript.

* :ref:`AssetMapper <frontend-asset-mapper>`, is a production-ready simpler alternative
  to Webpack Encore that runs entirely in PHP. It's currently experimental.

================================  =================  ======================================================
                                  Encore             AssetMapper
================================  =================  ======================================================
Production Ready?                 yes                yes
Stable?                           yes                :doc:`experimental </contributing/code/experimental>`
Requirements                      Node.js            none: pure PHP
Requires a build step?            yes                no
Works in all browsers?            yes                yes
Supports `Stimulus/UX`_           yes                yes
Supports Sass/Tailwind            yes                :ref:`yes <asset-mapper-tailwind>`
Supports React, Vue, Svelte?      yes                yes (but read note below)
Supports TypeScript               yes                no (but read note below)
================================  =================  ======================================================

.. note::

    Using JSX (React), Vue or TypeScript with AssetMapper is possible, but you'll
    need to use their native tools for pre-compilation. Also, some features (like
    Vue single-file components) cannot be compiled down to pure JavaScript that can
    be executed by a browser.

.. _frontend-webpack-encore:

Webpack Encore
--------------

.. screencast::

    Do you prefer video tutorials? Check out the `Webpack Encore screencast series`_.

`Webpack Encore`_ is a simpler way to integrate `Webpack`_ into your application.
It *wraps* Webpack, giving you a clean & powerful API for bundling JavaScript modules,
pre-processing CSS & JS and compiling and minifying assets. Encore gives you professional
asset system that's a *delight* to use.

Encore is inspired by `Webpacker`_ and `Mix`_, but stays in the spirit of Webpack:
using its features, concepts and naming conventions for a familiar feel. It aims
to solve the most common Webpack use cases.

.. tip::

    Encore is made by `Symfony`_ and works *beautifully* in Symfony applications.
    But it can be used in any PHP application and even with other server side
    programming languages!

.. _encore-toc:

Encore Documentation
--------------------

Getting Started
...............

* :doc:`Installation </frontend/encore/installation>`
* :doc:`Using Webpack Encore </frontend/encore/simple-example>`

Adding more Features
....................

* :doc:`CSS Preprocessors: Sass, LESS, etc </frontend/encore/css-preprocessors>`
* :doc:`PostCSS and autoprefixing </frontend/encore/postcss>`
* :doc:`Enabling React.js </frontend/encore/reactjs>`
* :doc:`Enabling Vue.js (vue-loader) </frontend/encore/vuejs>`
* :doc:`/frontend/encore/copy-files`
* :doc:`Configuring Babel </frontend/encore/babel>`
* :doc:`Source maps </frontend/encore/sourcemaps>`
* :doc:`Enabling TypeScript (ts-loader) </frontend/encore/typescript>`

Optimizing
..........

* :doc:`Versioning (and the entrypoints.json/manifest.json files) </frontend/encore/versioning>`
* :doc:`Using a CDN </frontend/encore/cdn>`
* :doc:`/frontend/encore/code-splitting`
* :doc:`/frontend/encore/split-chunks`
* :doc:`/frontend/encore/url-loader`

Guides
......

* :doc:`Using Bootstrap CSS & JS </frontend/encore/bootstrap>`
* :doc:`jQuery and Legacy Applications </frontend/encore/legacy-applications>`
* :doc:`Passing Information from Twig to JavaScript </frontend/encore/server-data>`
* :doc:`webpack-dev-server and Hot Module Replacement (HMR) </frontend/encore/dev-server>`
* :doc:`Adding custom loaders & plugins </frontend/encore/custom-loaders-plugins>`
* :doc:`Advanced Webpack Configuration </frontend/encore/advanced-config>`
* :doc:`Using Encore in a Virtual Machine </frontend/encore/virtual-machine>`

Issues & Questions
..................

* :doc:`FAQ & Common Issues </frontend/encore/faq>`

Full API
........

* `Full API`_

.. _frontend-asset-mapper:

AssetMapper
-----------

AssetMapper is an alternative to Webpack Encore that runs entirely in PHP
without any complex build steps. It leverages the ``importmap`` feature of
your browser, which is available in all browsers thanks to a polyfill.
AssetMapper is currently :doc:`experimental </contributing/code/experimental>`.

:doc:`Read the AssetMapper Documentation </frontend/asset_mapper>`

Stimulus & Symfony UX Components
--------------------------------

To learn about Stimulus & the UX Components, see:
the `StimulusBundle Documentation`_

Other Front-End Articles
------------------------

* :doc:`/frontend/create_ux_bundle`
* :doc:`/frontend/custom_version_strategy`

.. toctree::
    :hidden:
    :glob:

    frontend/encore/installation
    frontend/encore/simple-example
    frontend/encore/*
    frontend/asset_mapper
    frontend/*

.. _`Webpack Encore`: https://www.npmjs.com/package/@symfony/webpack-encore
.. _`Webpack`: https://webpack.js.org/
.. _`Node.js`: https://nodejs.org/
.. _`Webpacker`: https://github.com/rails/webpacker
.. _`Mix`: https://laravel.com/docs/mix
.. _`Symfony`: https://symfony.com/
.. _`Full API`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`Webpack Encore screencast series`: https://symfonycasts.com/screencast/webpack-encore
.. _StimulusBundle Documentation: https://symfony.com/bundles/StimulusBundle/current/index.html
.. _Stimulus/UX: https://symfony.com/bundles/StimulusBundle/current/index.html
