.. index::
   single: Assetic; UglifyJs

How to Minify CSS/JS Files (using UglifyJs and UglifyCss)
=========================================================

`UglifyJs`_ is a javascript parser/compressor/beautifier toolkit. It can be used
to combine and minify javascript assets so that they require less HTTP requests
and make your site load faster. `UglifyCss`_ is a css compressor/beautifier
that is very similar to UglifyJs.

In this cookbook, the installation, configuration and usage of UglifyJs is
shown in detail. ``UglifyCss`` works pretty much the same way and is only
talked about briefly.

Install UglifyJs
----------------

UglifyJs is available as an `Node.js`_ npm module and can be installed using
npm. First, you need to `install node.js`_. Afterwards you can install UglifyJs
using npm:

.. code-block:: bash

    $ npm install -g uglify-js

This command will install UglifyJs globally and you may need to run it as
a root user.

.. note::

    It's also possible to install UglifyJs inside your project only. To do
    this, install it without the ``-g`` option and specify the path where
    to put the module:

    .. code-block:: bash

        $ cd /path/to/symfony
        $ mkdir app/Resources/node_modules
        $ npm install uglify-js --prefix app/Resources

    It is recommended that you install UglifyJs in your ``app/Resources`` folder
    and add the ``node_modules`` folder to version control. Alternatively,
    you can create an npm `package.json`_ file and specify your dependencies
    there.

Depending on your installation method, you should either be able to execute
the ``uglifyjs`` executable globally, or execute the physical file that lives
in the ``node_modules`` directory:

.. code-block:: bash

    $ uglifyjs --help

    $ ./app/Resources/node_modules/.bin/uglifyjs --help

Configure the uglifyjs2 Filter
------------------------------

Now we need to configure Symfony2 to use the ``uglifyjs2`` filter when processing
your javascripts:

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
        <assetic:config>
            <assetic:filter
                name="uglifyjs2"
                bin="/usr/local/bin/uglifyjs" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'uglifyjs2' => array(
                    'bin' => '/usr/local/bin/uglifyjs',
                ),
            ),
        ));

.. note::

    The path where UglifyJs is installed may vary depending on your system.
    To find out where npm stores the ``bin`` folder, you can use the following
    command:

    .. code-block:: bash

        $ npm bin -g

    It should output a folder on your system, inside which you should find
    the UglifyJs executable.

    If you installed UglifyJs locally, you can find the bin folder inside
    the ``node_modules`` folder. It's called ``.bin`` in this case.

You now have access to the ``uglifyjs2`` filter in your application.

Minify your Assets
------------------

In order to use UglifyJs on your assets, you need to apply it to them. Since
your assets are a part of the view layer, this work is done in your templates:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='uglifyjs2' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('uglifyj2s')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

.. note::

    The above example assumes that you have a bundle called ``AcmeFooBundle``
    and your JavaScript files are in the ``Resources/public/js`` directory under
    your bundle. This isn't important however - you can include your JavaScript
    files no matter where they are.

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

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='?uglifyjs2' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('?uglifyjs2')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

To try this out, switch to your ``prod`` environment (``app.php``). But before
you do, don't forget to :ref:`clear your cache<book-page-creation-prod-cache-clear>`
and :ref:`dump your assetic assets<cookbook-asetic-dump-prod>`.

.. tip::

    Instead of adding the filter to the asset tags, you can also globally
    enable it by adding the apply-to attribute to the filter configuration, for
    example in the ``uglifyjs2`` filter ``apply_to: "\.js$"``. To only have
    the filter applied in production, add this to the ``config_prod`` file
    rather than the common config file. For details on applying filters by
    file extension, see :ref:`cookbook-assetic-apply-to`.

Install, configure and use UglifyCss
------------------------------------

The usage of UglifyCss works the same way as UglifyJs. First, make sure
the node package is installed:

.. code-block:: bash

    $ npm install -g uglifycss

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
        <assetic:config>
            <assetic:filter
                name="uglifycss"
                bin="/usr/local/bin/uglifycss" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'uglifycss' => array(
                    'bin' => '/usr/local/bin/uglifycss',
                ),
            ),
        ));

To use the filter for your css files, add the filter to the Assetic ``stylesheets``
helper:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/css/*' filter='uglifycss' %}
             <link rel="stylesheet" href="{{ asset_url }}" />
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/css/*'),
            array('uglifycss')
        ) as $url): ?>
            <link rel="stylesheet" href="<?php echo $view->escape($url) ?>" />
        <?php endforeach; ?>

Just like with the ``uglifyjs2`` filter, if you prefix the filter name with
``?`` (i.e. ``?uglifycss``), the minification will only happen when you're
not in debug mode.

.. _`UglifyJs`: https://github.com/mishoo/UglifyJS
.. _`UglifyCss`: https://github.com/fmarcia/UglifyCSS
.. _`Node.js`: http://nodejs.org/
.. _`install node.js`: http://nodejs.org/
.. _`package.json`: http://package.json.nodejitsu.com/
