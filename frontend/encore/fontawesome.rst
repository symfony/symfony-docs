Using FontAwesome 5
========================

.. code-block:: terminal

    // Using yarn:
    $ yarn add @fortawesome/fontawesome-free-webfonts --dev
    
    // Using npm
    $ npm install @fortawesome/fontawesome-free-webfonts --save-dev

Importing FontAwesome Sass
------------------------

Now that ``@fortawesome`` lives in your ``node_modules`` directory, you can
import it from any Sass or JavaScript file. For example, if you already have
a ``global.scss`` file, import it from there:

.. code-block:: css

    // assets/css/global.scss
    
    @import '~@fortawesome/fontawesome-free-webfonts/scss/fontawesome';
    @import '~@fortawesome/fontawesome-free-webfonts/scss/fa-brands';
    @import '~@fortawesome/fontawesome-free-webfonts/scss/fa-regular';
    @import '~@fortawesome/fontawesome-free-webfonts/scss/fa-solid';
   
Now you can build your css with the command:

.. code-block:: terminal

    $ ./node_modules/.bin/encore dev
    
