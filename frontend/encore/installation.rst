Encore Installation
===================

First, make sure you `install Node.js`_ and also the `Yarn package manager`_.

Then, install Encore into your project with Yarn:

.. code-block:: terminal

    $ yarn add @symfony/webpack-encore --only=dev

.. note::

    If you want to use `npm`_ instead of `yarn`_:

    .. code-block:: terminal

        $ npm install @symfony/webpack-encore --save-dev

.. tip::

    If you are using Flex for your project, you can initialize your project for Encore via:

    .. code-block:: terminal

        $ composer require encore
        $ yarn install

    This will create a ``webpack.config.js`` file, add the ``assets/`` directory, and add ``node_modules/`` to
    ``.gitignore``.

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
