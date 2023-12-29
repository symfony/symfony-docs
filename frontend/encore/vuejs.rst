Enabling Vue.js (``vue-loader``) with Webpack Encore
====================================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Vue screencast series`_.

.. tip::

    Check out live demos of Symfony UX Vue.js component at `https://ux.symfony.com/vue`_!

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

Runtime Compiler Build
----------------------

By default, Encore uses a Vue "build" that allows you to compile templates at
runtime. This means that you *can* do either of these:

.. code-block:: javascript

    new Vue({
        template: '<div>{{ hi }}</div>'
    })

    new Vue({
        el: '#app', // where <div id="app"> in your DOM contains the Vue template
    });

If you do *not* need this functionality (e.g. you use single file components),
then you can tell Encore to create a *smaller* build following Content Security Policy:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .enableVueLoader(() => {}, { runtimeCompilerBuild: false })
    ;

You can also silence the recommendation by passing ``runtimeCompilerBuild: true``.

Hot Module Replacement (HMR)
----------------------------

The ``vue-loader`` supports hot module replacement: just update your code and watch
your Vue.js app update *without* a browser refresh! To activate it, use the
``dev-server``:

.. code-block:: terminal

    $ npm run dev-server

That's it! Change one of your ``.vue`` files and watch your browser update. But
note: this does *not* currently work for *style* changes in a ``.vue`` file. Seeing
updated styles still requires a page refresh.

See :doc:`/frontend/encore/dev-server` for more details.

JSX Support
-----------

You can enable `JSX with Vue.js`_ by configuring the second parameter of the
``.enableVueLoader()`` method:

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

Next, run or restart Encore. When you do, you will see an error message helping
you install any missing dependencies. After running that command and restarting
Encore, you're done!

Your ``.jsx`` files will now be transformed through ``@vue/babel-preset-jsx``.

Using styles
~~~~~~~~~~~~

You can't use ``<style>`` in ``.jsx`` files. As a workaround, you can import
``.css``, ``.scss``, etc. files manually:

.. code-block:: jsx

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

.. note::

    Importing styles this way makes them global. See the next section for
    scoping them to your component.

Using Scoped Styles
~~~~~~~~~~~~~~~~~~~

You can't use `Scoped Styles`_ (``<style scoped>``) either in ``.jsx`` files. As
a workaround, you can use `CSS Modules`_ by suffixing import paths with
``?module``:

.. code-block:: jsx

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

The output will be something like ``<h1 class="title_a3dKp">Hello World</h1>``.

Using images
~~~~~~~~~~~~

You can't use ``<img src="./image.png">`` in ``.jsx`` files. As a workaround,
you can import them with ``require()`` function:

.. code-block:: jsx

    export default {
        name: 'Component',
        render() {
            return (
                <div>
                    <img src={require("./image.png")}/>
                </div>
            )
        }
    }

Using Vue inside Twig templates
-------------------------------

Twig templates can instantiate a Vue.js app in the same way as any other
JavaScript code. However, given that both Twig and Vue.js use the same delimiters
for variables, you should configure the ``delimiters`` Vue.js option to change
the default variable delimiters.

If you set for example ``delimiters: ['${', '}$']``, then you can use the
following in your Twig templates:

 .. code-block:: twig

    {{ twig_variable }}   {# renders a Twig variable #}
    ${ vuejs_variable }$  {# renders a Vue.js variable #}

.. _`Vue.js`: https://vuejs.org/
.. _`vue-loader options`: https://vue-loader.vuejs.org/options.html
.. _`Encore's index.js file`: https://github.com/symfony/webpack-encore/blob/master/index.js
.. _`JSX with Vue.js`: https://github.com/vuejs/jsx
.. _`Scoped Styles`: https://vue-loader.vuejs.org/guide/scoped-css.html
.. _`CSS Modules`: https://github.com/css-modules/css-modules
.. _`Vue screencast series`: https://symfonycasts.com/screencast/vue
.. _`https://ux.symfony.com/vue`: https://ux.symfony.com/vue
