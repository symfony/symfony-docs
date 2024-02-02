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

Symfony comes with two powerful options to help you build a modern and fast frontend:

* :ref:`AssetMapper <frontend-asset-mapper>` (recommended for new projects) runs
  entirely in PHP, doesn't require any build step and leverages modern web standards.

* :ref:`Webpack Encore <frontend-webpack-encore>` is built with `Node.js`_
  on top of `Webpack`_.

================================  ==================================  ==========
                                  AssetMapper                         Encore
================================  ==================================  ==========
Production Ready?                 yes                                 yes
Stable?                           yes                                 yes
Requirements                      none                                Node.js
Requires a build step?            no                                  yes
Works in all browsers?            yes                                 yes
Supports `Stimulus/UX`_           yes                                 yes
Supports Sass/Tailwind            :ref:`yes <asset-mapper-tailwind>`  yes
Supports React, Vue, Svelte?      yes :ref:`[1] <ux-note-1>`          yes
Supports TypeScript               :ref:`yes <asset-mapper-ts>`        yes
Removes comments from JavaScript  no                                  yes
Removes comments from CSS         no                                  no :ref:`[2] <ux-note-2>`
Versioned assets                  always                              optional
================================  ==================================  ==========

.. _ux-note-1:

**[1]** Using JSX (React), Vue, etc with AssetMapper is possible, but you'll
need to use their native tools for pre-compilation. Also, some features (like
Vue single-file components) cannot be compiled down to pure JavaScript that can
be executed by a browser.

.. _ux-note-2:

**[2]** There are some `PostCSS`_ plugins available to remove comments from CSS files.

.. _frontend-asset-mapper:

AssetMapper (Recommended)
~~~~~~~~~~~~~~~~~~~~~~~~~

AssetMapper is the recommended system for handling your assets. It runs entirely
in PHP with no complex build step or dependencies. It does this by leveraging
the ``importmap`` feature of your browser, which is available in all browsers thanks
to a polyfill.

:doc:`Read the AssetMapper Documentation </frontend/asset_mapper>`

.. _frontend-webpack-encore:

Webpack Encore
~~~~~~~~~~~~~~

.. screencast::

    Do you prefer video tutorials? Check out the `Webpack Encore screencast series`_.

`Webpack Encore`_ is a simpler way to integrate `Webpack`_ into your application.
It wraps Webpack, giving you a clean & powerful API for bundling JavaScript modules,
pre-processing CSS & JS and compiling and minifying assets.

:doc:`Read the Encore Documentation </frontend/encore/index>`

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
.. _`PostCSS`: https://postcss.org/
