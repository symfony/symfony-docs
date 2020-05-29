Enabling React.js
=================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `React.js screencast series`_.

Using React? First enable support for it in ``webpack.config.js``:

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
