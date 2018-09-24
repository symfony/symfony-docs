Installing Encore (with Symfony Flex)
=====================================

.. tip::

    If your project does **not** use Symfony Flex, read :doc:`/frontend/encore/installation-no-flex`.

First, make sure you `install Node.js`_ and also the `Yarn package manager`_. Then
run:

.. code-block:: terminal

    $ composer require symfony/webpack-encore-pack
    $ yarn install

This will create a ``webpack.config.js`` file, add the ``assets/`` directory, and
add ``node_modules/`` to ``.gitignore``.

Nice work! Write your first JavaScript and CSS by reading :doc:`/frontend/encore/simple-example`!

.. _`install Node.js`: https://nodejs.org/en/download/
.. _`Yarn package manager`: https://yarnpkg.com/lang/en/docs/install/
