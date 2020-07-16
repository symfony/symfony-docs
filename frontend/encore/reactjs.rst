Enabling React.js
=================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `React.js screencast series`_.

Using React? First enable react in your ``webpack.config.js``:

.. code-block:: diff

    // webpack.config.js
    // ...

    Encore
        // ...
    +     .enableReactPreset()
    ;

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. Install them with : 

.. code-block:: terminal

    $ yarn add @babel/preset-react@^7.0.0 --dev
    $ yarn add react react-dom prop-types

After running that command and restarting Encore, you're done!

Your ``.js`` and ``.jsx`` files will now be transformed through ``babel-preset-react``.

Create your first page with React. Start by editing your ``app.js`` file:

.. code-block:: javascript

    // assets/js/app.js
    
    // ...
    import ReactDOM from 'react-dom';

    ReactDOM.render('Hello from React', document.getElementById('app'));    

Now, we can add the Encore tags in our template :

.. code-block:: twig

    {% extends 'base.html.twig' %}

    {% block title %}Hello from React!{% endblock %}

    {% block stylesheets %}
        {{ encore_entry_link_tags('app') }}
    {% endblock %}

    {% block body %}
        <div id="app"></div>
    {% endblock %}

    {% block javascripts %}
        {{ encore_entry_script_tags('app') }}
    {% endblock %}

The ``app`` tag is a reference to the first paramter in the ``addEntry()`` method in
``webpack.config.js``.

Don't forget to activate the tag if you haven't done it and restart Encore if you make
any change in this file.

.. _`React.js screencast series`: https://symfonycasts.com/screencast/reactjs
