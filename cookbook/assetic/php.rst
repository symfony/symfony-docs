.. index::
    single: Front-end; Assetic, Bootstrap

Combining, Compiling and Minimizing Web Assets with PHP Libraries
=================================================================

.. include:: /cookbook/assetic/_standard_edition_warning.inc

The official Symfony Best Practices recommend to use Assetic to
:doc:`manage web assets </best_practices/web-assets>`, unless you are
comfortable with JavaScript-based front-end tools.

Even if those JavaScript-based solutions are the most suitable ones from a
technical point of view, using pure PHP alternative libraries can be useful in
some scenarios:

* If you can't install or use ``npm`` and the other JavaScript solutions;
* If you prefer to limit the amount of different technologies used in your
  applications;
* If you want to simplify application deployment.

In this article, you'll learn how to combine and minimize CSS and JavaScript files
and how to compile Sass files using PHP-only libraries with Assetic.

Installing the Third-Party Compression Libraries
------------------------------------------------

Assetic includes a lot of ready-to-use filters, but it doesn't include their
associated libraries. Therefore, before enabling the filters used in this article,
you must install two libraries. Open a command console, browse to your project
directory and execute the following commands:

.. code-block:: bash

    $ composer require leafo/scssphp
    $ composer require patchwork/jsqueeze:"~1.0"

It's very important to maintain the ``~1.0`` version constraint for the ``jsqueeze``
dependency because the most recent stable version is not compatible with Assetic.

Organizing your Web Asset Files
-------------------------------

This example will include a setup using the Bootstrap CSS framework, jQuery, FontAwesome
and some regular CSS and and JavaScript application files (called ``main.css`` and
``main.js``). The recommended directory structure for this set-up looks like this:

.. code-block:: text

    web/assets/
    ├── css
    │   ├── main.css
    │   └── code-highlight.css
    ├── js
    │   ├── bootstrap.js
    │   ├── jquery.js
    │   └── main.js
    └── scss
        ├── bootstrap
        │   ├── _alerts.scss
        │   ├── ...
        │   ├── _variables.scss
        │   ├── _wells.scss
        │   └── mixins
        │       ├── _alerts.scss
        │       ├── ...
        │       └── _vendor-prefixes.scss
        ├── bootstrap.scss
        ├── font-awesome
        │   ├── _animated.scss
        │   ├── ...
        │   └── _variables.scss
        └── font-awesome.scss

Combining and Minimizing CSS Files and Compiling SCSS Files
-----------------------------------------------------------

First, configure a new ``scssphp`` Assetic filter:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                scssphp:
                    formatter: 'Leafo\ScssPhp\Formatter\Compressed'
                # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config>
                <filter name="scssphp" formatter="Leafo\ScssPhp\Formatter\Compressed" />
                <!-- ... -->
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                 'scssphp' => array(
                     'formatter' => 'Leafo\ScssPhp\Formatter\Compressed',
                 ),
                 // ...
            ),
        ));

The value of the ``formatter`` option is the fully qualified class name of the
formatter used by the filter to produce the compiled CSS file. Using the
compressed formatter will minimize the resulting file, regardless of whether
the original files are regular CSS files or SCSS files.

Next, update your Twig template to add the ``{% stylesheets %}`` tag defined
by Assetic:

.. code-block:: html+twig

    {# app/Resources/views/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <!-- ... -->

            {% stylesheets filter="scssphp" output="css/app.css"
                "assets/scss/bootstrap.scss"
                "assets/scss/font-awesome.scss"
                "assets/css/*.css"
            %}
                <link rel="stylesheet" href="{{ asset_url }}" />
            {% endstylesheets %}

This simple configuration compiles, combines and minifies the SCSS files into a
regular CSS file that's put in ``web/css/app.css``. This is the only CSS file
which will be served to your visitors.

Combining and Minimizing JavaScript Files
-----------------------------------------

First, configure a new ``jsqueeze`` Assetic filter as follows:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jsqueeze: ~
                # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config>
                <filter name="jsqueeze" />
                <!-- ... -->
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                 'jsqueeze' => null,
                 // ...
            ),
        ));

Next, update the code of your Twig template to add the ``{% javascripts %}`` tag
defined by Assetic:

.. code-block:: html+twig

    <!-- ... -->

        {% javascripts filter="?jsqueeze" output="js/app.js"
            "assets/js/jquery.js"
            "assets/js/bootstrap.js"
            "assets/js/main.js"
        %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

        </body>
    </html>

This simple configuration combines all the JavaScript files, minimizes the contents
and saves the output in the ``web/js/app.js`` file, which is the one that is
served to your visitors.

The leading ``?`` character in the ``jsqueeze`` filter name tells Assetic to only
apply the filter when *not* in ``debug`` mode. In practice, this means that you'll
see unminified files while developing and minimized files in the ``prod`` environment.
