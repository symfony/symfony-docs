Installation
============

First, make sure you `install Node.js`_ and also the `yarn package manager`_.

Then, install Encore into your project with yarn:

.. code-block:: terminal

    $ yarn add @symfony/webpack-encore --dev

.. note::

    If you want to use `npm`_ instead of `yarn`_, replace ``yarn add xxx --dev`` by
    ``npm install xxx --save-dev``.

This command creates (or modifies) a ``package.json`` file and downloads
dependencies into a ``node_modules/`` directory. When using Yarn, a file called
``yarn.lock`` is also created/updated. When using npm 5, a ``package-lock.json``
file is created/updated.

.. tip::

    You should commit ``package.json`` and ``yarn.lock`` (or ``package-lock.json``
    if using npm) to version control, but ignore ``node_modules/``.

Next, create your ``webpack.config.js`` in :doc:`/frontend/encore/simple-example`!

.. _`install Node.js`: https://nodejs.org/en/download/
.. _`yarn package manager`: https://yarnpkg.com/lang/en/docs/install/
.. _`npm`: https://www.npmjs.com/
.. _`yarn`: https://yarnpkg.com/
