Enabling React.js
=================

Using React? Make sure you have React installed, along with the `babel-preset-react`_:

.. code-block:: terminal

    $ yarn add --dev react react-dom prop-types babel-preset-react

Enable react in your ``webpack.config.js``:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...
        .enableReactPreset()
    ;

That's it! Your ``.js`` and ``.jsx`` files will now be transformed through
``babel-preset-react``.

.. _`babel-preset-react`: https://babeljs.io/docs/plugins/preset-react/

Building a component
--------------------

Now that Encore can manage any React instance, let's build a simple component
which display ``Hello World from React !``.

In order to do this, let's add a new ``react.js`` file inside the ``assets`` folder:

.. code-block:: javascript

    // assets/react.js
    import React from "react";
    import ReactDOM from "react-dom";

    import { App } from './components/App.jsx';

    ReactDOM.render(
        <App name="Hello World from React !"/>,
        document.getElementById("react")
    );

Here's, React is called along with ReactDOM for the rendering part,
once the import is done, React need to display a component, here,
that comes with the App component:

.. code-block:: javascript

    // assets/components/App.jsx
    import React, { Component } from 'react';

    export class App extends Component {

        render () {
            return (
                <div>
                    <p>{ this.props.name }</p>
                </div>
            );
        }
    }

In this component, React allow us to call props, this way,
passing data from the ``react.js`` file is as simple as a
property call, by default, if the component need to receive dynamic data,
it's best to do an Ajax request or to :ref:`pass data from Twig to JavaScript <twig-data>`.

Once the component is created, time to alert Encore about compiling this files :

let Encore = require('@symfony/webpack-encore');

.. code-block:: javascript

    Encore
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .enableReactPreset()
        .addEntry('react', './assets/react.js')
    ;

    module.exports = Encore.getWebpackConfig();

Here, Encore gonna find the ``react.js`` file and compile it into a ``react.js``
into the ``public/build`` folder.
In order to tell Twig to load the file, here's the modifications needed:

.. code-block:: twig

    {% extends 'base.html.twig' %}

    {% block body %}
        <div id="react"></div>
    {% endblock %}

    {% block javascript %}
        <script src="{{ asset('build/react.js') }}"></script>
    {% endblock %}

If the webpack command for developement is launched, reload the webpage
and the DOM should display ``Hello World from React !``.
