Installating Encore
===================

First, make sure you `install Node.js`_ and also the `Yarn package manager`_.

Option A) If you are using Symfony Flex
---------------------------------------

If you are using Symfony flex, you can easily setup your app by running:

.. code-block:: terminal

    $ composer require webpack-encore
    $ yarn install

This will create a ``webpack.config.js`` file, add the ``assets/`` directory, and
add ``node_modules/`` to ``.gitignore``.

Nice work! Write your first JavaScript and CSS by reading :doc:`/frontend/encore/simple-example`!

Option B) Without Symfony Flex
------------------------------

If your project doesn't use Symfony Flex, you can still install Encore easily via
yarn or npm. See :doc:`/frontend/encore/installation-no-flex`.

.. _`install Node.js`: https://nodejs.org/en/download/
.. _`Yarn package manager`: https://yarnpkg.com/lang/en/docs/install/
