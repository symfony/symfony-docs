Web Assets
==========

Web assets are things like CSS, JavaScript and image files that make the
frontend of your site look and work great.

.. best-practice::

    Store your assets in the ``assets/`` directory at the root of your project.

Your designers' and front-end developers' lives will be much easier if all the
application assets are in one central location.

.. best-practice::

    Use `Webpack Encore`_ to compile, combine and minimize web assets.

`Webpack`_ is the leading JavaScript module bundler that compiles, transforms
and packages assets for usage in a browser. Webpack Encore is a JavaScript
library that gets rid of most of Webpack complexity without hiding any of its
features or distorting its usage and philosophy.

Webpack Encore was designed to bridge the gap between Symfony applications and
the JavaScript-based tools used in modern web applications. Check out the
`official Webpack Encore documentation`_ to learn more about all the available
features.

----

Next: :doc:`/best_practices/tests`

.. _`official Assetic documentation`: https://github.com/kriswallsmith/assetic
.. _`Webpack Encore`: https://github.com/symfony/webpack-encore
.. _`Webpack`: https://webpack.js.org/
.. _`official Webpack Encore documentation`: https://symfony.com/doc/current/frontend.html
