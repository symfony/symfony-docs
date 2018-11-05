Enabling Vue.js (vue-loader)
============================

Want to use `Vue.js`_? No problem! First enable it in ``webpack.config.js``:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        // ...
        .addEntry('main', './assets/main.js')

    +     .enableVueLoader()
    ;

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

Any ``.vue`` files that you require will be processed correctly. You can also
configure the `vue-loader options`_ by passing an options callback to
``enableVueLoader()``. See the `Encore's index.js file`_ for detailed documentation.

Hot Module Replacement (HMR)
----------------------------

The ``vue-loader`` supports hot module replacement: just update your code and watch
your Vue.js app update *without* a browser refresh! To activate it, use the
``dev-server`` with the ``--hot`` option:

.. code-block:: terminal

    $ yarn encore dev-server --hot

That's it! Change one of your ``.vue`` files and watch your browser update. But
note: this does *not* currently work for *style* changes in a ``.vue`` file. Seeing
updated styles still requires a page refresh.

See :doc:`/frontend/encore/dev-server` for more details.

.. _`babel-preset-react`: https://babeljs.io/docs/plugins/preset-react/
.. _`Vue.js`: https://vuejs.org/
.. _`vue-loader options`: https://vue-loader.vuejs.org/options.html
.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
