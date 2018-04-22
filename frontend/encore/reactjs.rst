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

Now that Encore can manage any React instance, it's time to build a "Hello World" React component!

Start by creating an ``App.jsx`` file in the ``assets/components`` folder: 

.. code-block:: javascript

    // assets/components/App.jsx
    import React, { Component } from 'react';

    export class App extends Component {

        render() {
            return (
                <div>
                    <p>{ this.props.name }</p>
                </div>
            );
        }
    }
    
In this component, React allow us to call props. Thanks to this, you can pass
pass data from the ``hello.js`` file (created next) either by fetching that
data via an AJAX call or :ref:`passing data from Twig to JavaScript <twig-data>`.

Now it's time to render it! First, add a new ``hello.js`` file inside the ``assets/`` folder:

.. code-block:: javascript

    // assets/react.js
    import React from "react";
    import ReactDOM from "react-dom";

    import { App } from './components/App.jsx';

    // you could use AJAX to get this message dynamically
    // or print it in Twig on a DOM element
    const message = 'Hello World from React!';

    ReactDOM.render(
        <App name="{message}"/>,
        document.getElementById("hello-app")
    );

Finally, you need to tell Encore how to compile this file:

.. code-block:: diff

    // ...

    Encore
        // ...

    +     .enableReactPreset()
    +     .addEntry('hello', './assets/hello.js')
    ;

    // ...

And finally, make sure the new JavaScript file is loaded in Twig:

.. code-block:: twig

    {# templates/some_template.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <div id="hello-app"></div>
    {% endblock %}

    {% block javascript %}
        <script src="{{ asset('build/hello.js') }}"></script>
    {% endblock %}

Execute Encore, then reload the page to see ``Hello World from React !``.
