Encore Installation
===================

First, make sure you `install Node.js`_ and also the `Yarn package manager`_.

Then, install Encore into your project with Yarn:

.. code-block:: terminal

    $ yarn add @symfony/webpack-encore --dev

.. note::

    If you want to use `npm`_ instead of `yarn`_, replace ``yarn add xxx --dev`` by
    ``npm install xxx --save-dev``. 

    Encore includes a file that locks dependency versions when `yarn` is used (the `yarn.lock` file).
    It's highly encouraged to use `yarn` to avoid any issues and to maintain consistency across machines.
    
    If you already in trouble remove `node_modules` and use `yarn` to install dependencies.

.. tip::

    If you are using Flex for your project, you can install Encore via:

    .. code-block:: terminal

        $ composer require encore

This command creates (or modifies) a ``package.json`` file and downloads dependencies
into a ``node_modules/`` directory. When using Yarn, a file called ``yarn.lock``
is also created/updated. When using npm 5, a ``package-lock.json`` file is created/updated.

.. tip::

    You should commit ``package.json`` and ``yarn.lock`` (or ``package-lock.json``
    if using npm 5) to version control, but ignore ``node_modules/``.

Next, create your ``webpack.config.js`` in :doc:`/frontend/encore/simple-example`!

.. _`install Node.js`: https://nodejs.org/en/download/
.. _`Yarn package manager`: https://yarnpkg.com/lang/en/docs/install/
.. _`npm`: https://www.npmjs.com/
.. _`yarn`: https://yarnpkg.com/
