Enabling Vue.js (vue-loader)
============================

Want to use `Vue.js`_? No problem! First, install Vue and some dependencies:

.. code-block:: terminal

    $ yarn add --dev vue vue-loader vue-template-compiler

Then, activate the ``vue-loader`` in ``webpack.config.js``:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        // ...
        .addEntry('main', './assets/main.js')

    +     .enableVueLoader()
    ;

That's it! Any ``.vue`` files that you require will be processed correctly.

Hot Module Replacement (HMR)
----------------------------

The ``vue-loader`` supports hot module replacement: just update your code and watch
your Vue.js app update *without* a browser refresh! To activate it, just use the
``dev-server`` with the ``--hot`` option:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev-server --hot

That's it! Change one of your ``.vue`` files and watch your browser update. But
note: this does *not* currently work for *style* changes in a ``.vue`` file. Seeing
updated styles still requires a page refresh.

See :doc:`/frontend/encore/dev-server` for more details.

.. _`babel-preset-react`: https://babeljs.io/docs/plugins/preset-react/
.. _`Vue.js`: https://vuejs.org/