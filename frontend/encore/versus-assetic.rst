Encore Versus Assetic?
======================

Symfony originally shipped with support for :doc:`Assetic </frontend/assetic>`: a
pure PHP library capable of processing, combining and minifying CSS and JavaScript
files. And while Encore is now the recommended way of processing your assets, Assetic
still works well.

So what are the differences between Assetic and Encore?

+--------------------------+-------------------------------+-------------------------+
|                          | Assetic                       | Encore                  +
+--------------------------+-------------------------------+-------------------------+
| Language                 | Pure PHP, relies on other     | Node.js                 |
|                          | language tools for some tasks |                         |
+--------------------------+-------------------------------+-------------------------+
| Combine assets?          | Yes                           | Yes                     |
+--------------------------+-------------------------------+-------------------------+
| Minify assets?           | Yes (when configured)         | Yes (out-of-the-box)    |
+--------------------------+-------------------------------+-------------------------+
| Process Sass/Less?       | Yes                           | Yes                     |
+--------------------------+-------------------------------+-------------------------+
| Loads JS Modules? [1]_   | No                            | Yes                     |
+--------------------------+-------------------------------+-------------------------+
| Load CSS Deps in JS? [1] | No                            | Yes                     |
+--------------------------+-------------------------------+-------------------------+
| React, Vue.js support?   | No [2]_                       | Yes                     |
+--------------------------+-------------------------------+-------------------------+
| Support                  | Not actively maintained       | Actively maintained     |
+--------------------------+-------------------------------+-------------------------+

.. [1] JavaScript modules allow you to organize your JavaScript into small files
       called modules and import them:

       .. code-block:: javascript

           // require third-party modules
           var $ = require('jquery');

           // require your own CoolComponent.js modules
           var coolComponent = require('./components/CoolComponent');

       Encore (via Webpack) parses these automatically and creates a JavaScript
       file that contains all needed dependencies. You can even require CSS or
       images.

.. [2] Assetic has outdated support for React.js only. Encore ships with modern
       support for React.js, Vue.js, Typescript, etc.

Should I Upgrade from Assetic to Encore
---------------------------------------

If you already have Assetic working in an application, and haven't needed any of
the features that Encore offers over Assetic, continuting to use Assetic is fine.
If you *do* start to need more features, then you might have a business case for
changing to Encore.
