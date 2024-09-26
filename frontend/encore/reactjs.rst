Enabling React.js with Webpack Encore
=====================================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `React.js screencast series`_.

.. tip::

    Check out live demos of Symfony UX React component at `https://ux.symfony.com/react`_!

Using React? First add some dependencies with npm:

.. code-block:: terminal

    $ npm install react react-dom prop-types --save

Enable react in your ``webpack.config.js``:

.. code-block:: diff

      // webpack.config.js
      // ...

      Encore
          // ...
    +     .enableReactPreset()
      ;

Then restart Encore. When you do, it will give you a command you can run to
install any missing dependencies. After running that command and restarting
Encore, you're done!

Your ``.js`` and ``.jsx`` files will now be transformed through ``babel-preset-react``.

.. _`React.js screencast series`: https://symfonycasts.com/screencast/reactjs
.. _`https://ux.symfony.com/react`: https://ux.symfony.com/react
