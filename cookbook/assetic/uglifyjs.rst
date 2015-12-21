.. index::
   single: Assetic; UglifyJS

How to Minify CSS/JS Files (Using UglifyJS and UglifyCSS)
=========================================================

.. include:: /cookbook/assetic/_standard_edition_warning.inc

`UglifyJS`_ is a JavaScript parser/compressor/beautifier toolkit. It can be used
to combine and minify JavaScript assets so that they require less HTTP requests
and make your site load faster. `UglifyCSS`_ is a CSS compressor/beautifier
that is very similar to UglifyJS.

In this cookbook, the installation, configuration and usage of UglifyJS is
shown in detail. UglifyCSS works pretty much the same way and is only
talked about briefly.

Install UglifyJS
----------------

UglifyJS is available as a `Node.js`_ module. First, you need to `install Node.js`_
and then, decide the installation method: global or local.

Global Installation
~~~~~~~~~~~~~~~~~~~

The global installation method makes all your projects use the very same UglifyJS
version, which simplifies its maintenance. Open your command console and execute
the following command (you may need to run it as a root user):

.. code-block:: bash

    $ npm install -g uglify-js

Now you can execute the global ``uglifyjs`` command anywhere on your system:

.. code-block:: bash

    $ uglifyjs --help

Local Installation
~~~~~~~~~~~~~~~~~~

It's also possible to install UglifyJS inside your project only, which is useful
when your project requires a specific UglifyJS version. To do this, install it
without the ``-g`` option and specify the path where to put the module:

.. code-block:: bash

    $ cd /path/to/your/symfony/project
    $ npm install uglify-js --prefix app/Resources

It is recommended that you install UglifyJS in your ``app/Resources`` folder and
add the ``node_modules`` folder to version control. Alternatively, you can create
an npm `package.json`_ file and specify your dependencies there.

Now you can execute the ``uglifyjs`` command that lives in the ``node_modules``
directory:

.. code-block:: bash

    $ "./app/Resources/node_modules/.bin/uglifyjs" --help

Configure the ``uglifyjs2`` Filter
----------------------------------

Now we need to configure Symfony to use the ``uglifyjs2`` filter when processing
your JavaScripts:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                uglifyjs2:
                    # the path to the uglifyjs executable
                    bin: /usr/local/bin/uglifyjs

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config>
                <!-- bin: the path to the uglifyjs executable -->
                <assetic:filter
                    name="uglifyjs2"
                    bin="/usr/local/bin/uglifyjs" />
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'uglifyjs2' => array(
                    // the path to the uglifyjs executable
                    'bin' => '/usr/local/bin/uglifyjs',
                ),
            ),
        ));

.. note::

    The path where UglifyJS is installed may vary depending on your system.
    To find out where npm stores the ``bin`` folder, execute the following command:

    .. code-block:: bash

        $ npm bin -g

    It should output a folder on your system, inside which you should find
    the UglifyJS executable.

    If you installed UglifyJS locally, you can find the ``bin`` folder inside
    the ``node_modules`` folder. It's called ``.bin`` in this case.

You now have access to the ``uglifyjs2`` filter in your application.

Configure the ``node`` Binary
-----------------------------

Assetic tries to find the node binary automatically. If it cannot be found, you
can configure its location using the ``node`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            # the path to the node executable
            node: /usr/bin/nodejs
            filters:
                uglifyjs2:
                    # the path to the uglifyjs executable
                    bin: /usr/local/bin/uglifyjs

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config
                node="/usr/bin/nodejs" >
                <assetic:filter
                    name="uglifyjs2"
                    bin="/usr/local/bin/uglifyjs" />
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'node' => '/usr/bin/nodejs',
            'uglifyjs2' => array(
                    // the path to the uglifyjs executable
                    'bin' => '/usr/local/bin/uglifyjs',
                ),
        ));

Minify your Assets
------------------

In order to apply UglifyJS on your assets, add the ``filter`` option in the
asset tags of your templates to tell Assetic to use the ``uglifyjs2`` filter:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' filter='uglifyjs2' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array('uglifyj2s')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. note::

    The above example assumes that you have a bundle called AppBundle and your
    JavaScript files are in the ``Resources/public/js`` directory under your
    bundle. However you can include your JavaScript files no matter where they are.

With the addition of the ``uglifyjs2`` filter to the asset tags above, you
should now see minified JavaScripts coming over the wire much faster.

Disable Minification in Debug Mode
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Minified JavaScripts are very difficult to read, let alone debug. Because of
this, Assetic lets you disable a certain filter when your application is in
debug (e.g. ``app_dev.php``) mode. You can do this by prefixing the filter name
in your template with a question mark: ``?``. This tells Assetic to only
apply this filter when debug mode is off (e.g. ``app.php``):

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' filter='?uglifyjs2' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array('?uglifyjs2')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

To try this out, switch to your ``prod`` environment (``app.php``). But before
you do, don't forget to :ref:`clear your cache <book-page-creation-prod-cache-clear>`
and :ref:`dump your assetic assets <cookbook-assetic-dump-prod>`.

.. tip::

    Instead of adding the filters to the asset tags, you can also configure which
    filters to apply for each file in your application configuration file.
    See :ref:`cookbook-assetic-apply-to` for more details.

Install, Configure and Use UglifyCSS
------------------------------------

The usage of UglifyCSS works the same way as UglifyJS. First, make sure
the node package is installed:

.. code-block:: bash

    # global installation
    $ npm install -g uglifycss

    # local installation
    $ cd /path/to/your/symfony/project
    $ npm install uglifycss --prefix app/Resources

Next, add the configuration for this filter:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                uglifycss:
                    bin: /usr/local/bin/uglifycss

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config>
                <assetic:filter
                    name="uglifycss"
                    bin="/usr/local/bin/uglifycss" />
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'uglifycss' => array(
                    'bin' => '/usr/local/bin/uglifycss',
                ),
            ),
        ));

To use the filter for your CSS files, add the filter to the Assetic ``stylesheets``
helper:

.. configuration-block::

    .. code-block:: html+twig

        {% stylesheets 'bundles/App/css/*' filter='uglifycss' filter='cssrewrite' %}
             <link rel="stylesheet" href="{{ asset_url }}" />
        {% endstylesheets %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->stylesheets(
            array('bundles/App/css/*'),
            array('uglifycss'),
            array('cssrewrite')
        ) as $url): ?>
            <link rel="stylesheet" href="<?php echo $view->escape($url) ?>" />
        <?php endforeach ?>

Just like with the ``uglifyjs2`` filter, if you prefix the filter name with
``?`` (i.e. ``?uglifycss``), the minification will only happen when you're
not in debug mode.

.. _`UglifyJS`: https://github.com/mishoo/UglifyJS
.. _`UglifyCSS`: https://github.com/fmarcia/UglifyCSS
.. _`Node.js`: https://nodejs.org/
.. _`install Node.js`: https://nodejs.org/
.. _`package.json`: http://browsenpm.org/package.json
