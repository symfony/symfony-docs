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
your Vue.js app update *without* a browser refresh! To activate it, just use the
``dev-server`` with the ``--hot`` option:

.. code-block:: terminal

    $ yarn encore dev-server --hot

That's it! Change one of your ``.vue`` files and watch your browser update. But
note: this does *not* currently work for *style* changes in a ``.vue`` file. Seeing
updated styles still requires a page refresh.

See :doc:`/frontend/encore/dev-server` for more details.

JSX Support
-----------

You can enable `JSX with Vue.js`_ by configuring the 2nd parameter of ``.enableVueLoader`` method:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        // ...
        .addEntry('main', './assets/main.js')

    -     .enableVueLoader()
    +     .enableVueLoader(() => {}, {
    +         useJsx: true
    +     })
    ;

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

Your ``.jsx`` files will now be transformed through ``@vue/babel-preset-jsx``.

Using styles
~~~~~~~~~~~~

You can't use ``<style>`` in ``.jsx`` files.

As a workaround, you can import ``.css``, ``.scss``, ``.less`` and ``.styl`` files manually:

.. code-block:: js

    // App.jsx

    import './App.css'

    export default {
        name: 'App',
        render() {
            return (
                <div>
                    ...
                </div>
            )
        }
    }

Note that importing styles like this make them global.
See the next section for scoping them to your component.

Using Scoped Styles
~~~~~~~~~~~~~~~~~~~

Same, you can't use `Scoped Styles`_ (``<style scoped>``) in ``.jsx`` files.

As a workaround, you can use `CSS Modules`_ by suffixing import paths with ``?module``:

.. code-block:: js

    // Component.jsx

    import styles from './Component.css?module' // suffix with "?module"

    export default {
        name: 'Component',
        render() {
            return (
                <div>
                    <h1 class={styles.title}>
                        Hello World
                    </h1>
                </div>
            )
        }
    }

.. code-block:: css

    /* Component.css */

    .title {
        color: red
    }

The output will be something like ``<h1 class="h1_a3dKp">Hello World</h1>``.

Using images
~~~~~~~~~~~~

You can't use ``<img src="./image.png">`` in ``.jsx`` files.

As a workaround, you can import them with ``require()`` function:

.. code-block:: js

    export default {
        name: 'Component',
        render() {
            return (
                <div>
                    <img src={require("./image.png")} />
                </div>
            )
        }
    }

.. _`babel-preset-react`: https://babeljs.io/docs/plugins/preset-react/
.. _`Vue.js`: https://vuejs.org/
.. _`vue-loader options`: https://vue-loader.vuejs.org/options.html
.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`JSX with Vue.js`: https://github.com/vuejs/jsx
.. _`Scoped Styles`: https://vue-loader.vuejs.org/guide/scoped-css.html
.. _`CSS Modules`: https://github.com/css-modules/css-modules
