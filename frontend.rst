Symfony Frontend Tools
======================

Symfony ships with two different options for handling the CSS and JavaScript in
your app:

* :ref:`Webpack Encore <frontend-webpack-encore>` is a powerful tool build with Node
  on top of `Webpack`_ that allows you to write modern CSS & JavaScript and handle
  things like JSX (React), Vur or TypeScript. It's the recommended option for
  new Symfony apps.

* :ref:`Asset Mapper <frontend-asset-mapper>`, is a production-ready simpler alternative
  to Webpack Encore that runs entirely in PHP. It's currently experimental.

Overall, `Asset Mapper` is powerful & simpler, but doesn't support certain
features like JSX or TypeScript:

=========================================  =================  =================
                                           Encore             AssetMapper
=========================================  =================  =================
Production Ready?                          yes                yes
Stable?                                    yes                experimental
Requirements                               node               none: pure PHP
Requires a build step?                     yes                no
Works in all browsers?                     yes                yes
Supports :doc:`Stimulus/UX </frontend/ux>` yes                yes
Supports Sass/Tailwind                     yes                :ref:`yes <asset-mapper-extras>`
Supports JSX, Vue?                         yes                no
Supports TypeScript                        yes                no
=========================================  =================  =================

.. _frontend-webpack-encore:

Webpack Encore
--------------

.. admonition:: Screencast
    :class: screencast

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

Asset Mapper
------------

Asset Mapper is an alternative to Webpack Encore that runs entirely in PHP
without any complex build steps. It leverages the ``importmap`` feature of
your browser, which is available in all browsers thanks to a polyfill.
Asset Mapper is currently experimental.

* :doc:`Install AssetMapper </frontend/asset_mapper/installation>`
* :doc:`Using AssetMapper </frontend/asset_mapper/usage>`
* :doc:`Handling CSS </frontend/asset_mapper/css>`
* :doc:`FAQ & Common Issues </frontend/asset_mapper/faq>`

Symfony UX Components
---------------------

* :doc:`/frontend/ux`

.. include:: /frontend/_ux-libraries.rst.inc

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
    frontend/asset_mapper/installation
    frontend/asset_mapper/usage
    frontend/asset_mapper/*
    frontend/*

.. _`Webpack Encore`: https://www.npmjs.com/package/@symfony/webpack-encore
.. _`Webpack`: https://webpack.js.org/
.. _`Webpacker`: https://github.com/rails/webpacker
.. _`Mix`: https://laravel.com/docs/mix
.. _`Symfony`: https://symfony.com/
.. _`Full API`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`Webpack Encore screencast series`: https://symfonycasts.com/screencast/webpack-encore
