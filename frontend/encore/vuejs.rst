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

That's it! Any ``.vue`` files that you require will be processed correctly. You can
also configure the `vue-loader options`_ via a callback:

.. code-block:: javascript

    .enableVueLoader(function(options) {
        // https://vue-loader.vuejs.org/en/configurations/advanced.html

        options.preLoaders = {
            js: '/path/to/custom/loader'
        };
    });

Building a simple Component
---------------------------

Once Vue is installed and ``webpack.config.js`` has been updated,
you can build your first component and display a simple ``Hello World from Vue !``.

In order to ease the process, the usage of ``.vue`` files is recommended.
Start by adding a ``main.js`` file inside the assets folder:

.. code-block:: javascript

    <!-- assets/main.js -->
    import Vue from 'vue'
    import Hello from './components/Hello.vue'

    new Vue({
        el: '#app',
        render: h => h(Hello)
    });

First, this file's gonna call the component Hello from the components folder.
Once the component is found, Vue gonna instantiate a new Root component and 
attach your component to the HTML element who contain the app identifier.

Now, let's build the Hello component:

.. code-block:: html

    <!-- assets/components/Hello.vue -->
    <template>
        <div id="app">
            <p>{{ msg }}</p>
        </div>
    </template>

    <script>
        export default {
            name: 'Hello',
            data () {
                return {
                    msg: 'Hello World from Vue !'
                }
            }
        }
    </script>

This component is pretty simple at this stage but once you're aware about ``Vue`` and his internal logic,
you can easily integrate it into your own Twig views and logic.

Now that your component is ready and that your ``Vue`` instance is configured,
time to use Encore to build everything and call ``Vue`` inside your views,
in order to ease the process, let's use the watcher:

.. code-block:: terminal

  $ ./node_modules/.bin/encore dev --watch

If everything goes right, Encore should compile ``main.js`` and produce a new ``main.js`` file
inside the ``public/build`` directory, once the file is packed into the folder,
let's call it inside your Twig views:

.. code-block:: twig

    {# templates/index.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <div id="app"></div>
    {% endblock %}

    {% block javascripts %}
        <script src="{{ asset('build/main.js') }}"></script>
    {% endblock %}

Once the file is called, reload your webpage and the DOM should display the message ``Hello World from Vue !``.

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
.. _`vue-loader options`: https://vue-loader.vuejs.org/en/configurations/advanced.html
