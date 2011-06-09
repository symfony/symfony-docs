How to Minify JavaScripts and Stylesheets with YUI Compressor
=============================================================

Yahoo! provides an excellent utility for minifying JavaScripts and stylesheets
so they travel over the wire faster, the `YUI Compressor`_. Thanks to Assetic,
you can take advantage of this tool very easily.

Download the YUI Compressor JAR
-------------------------------

The YUI Compressor is written in Java and distributed as a JAR. `Download the JAR`_
from the Yahoo! site and save it to ``app/Resources/java/yuicompressor.jar``.

Configure the YUI Filters
-------------------------

Now you need to configure two Assetic filters in your application, one for
minifying JavaScripts with the YUI Compressor and one for minifying
stylesheets:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                yui_css:
                    jar: "%kernel.root_dir%/Resources/java/yuicompressor.jar"
                yui_js:
                    jar: "%kernel.root_dir%/Resources/java/yuicompressor.jar"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="yui_css"
                jar="%kernel.root_dir%/Resources/java/yuicompressor.jar" />
            <assetic:filter
                name="yui_js"
                jar="%kernel.root_dir%/Resources/java/yuicompressor.jar" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'yui_css' => array(
                    'jar' => '%kernel.root_dir%/Resources/java/yuicompressor.jar',
                ),
                'yui_js' => array(
                    'jar' => '%kernel.root_dir%/Resources/java/yuicompressor.jar',
                ),
            ),
        ));

You now have access to two new Assetic filters in your application:
``yui_css`` and ``yui_js``. These will use the YUI Compressor to minify
stylesheets and JavaScripts, respectively.

Minify your Assets
------------------

You have YUI Compressor configured now, but nothing is going to happen until
you apply one of these filters to an asset. Since your assets are a part of
the view layer, this work is done in your templates:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='yui_js' %}
        <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('yui_js')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

.. note::

    The above example assumes that you have a bundle called ``AcmeFooBundle``
    and your JavaScript files are in the ``Resources/public/js`` directory under
    your bundle. This isn't important however - you can include your Javascript
    files no matter where they are.

With the addition of the ``yui_js`` filter to the asset tags above, you should
now see minified JavaScripts coming over the wire much faster. The same process
can be repeated to minify your stylesheets.

.. configuration-block::

    .. code-block:: html+jinja

        {% stylesheets '@AcmeFooBundle/Resources/public/css/*' filter='yui_css' %}
        <link rel="stylesheet" type="text/css" media="screen" href="{{ asset_url }}" />
        {% endstylesheets %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->stylesheets(
            array('@AcmeFooBundle/Resources/public/css/*'),
            array('yui_css')) as $url): ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $view->escape($url) ?>" />
        <?php endforeach; ?>

Disable Minification in Debug Mode
----------------------------------

Minified JavaScripts and Stylesheets are very difficult to read, let alone
debug. Because of this, Assetic lets you disable a certain filter when your
application is in debug mode. You can do this be prefixing the filter name
in your template with a question mark: ``?``. This tells Assetic to only
apply this filter when debug mode is off.

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='?yui_js' %}
        <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('?yui_js')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

.. _`YUI Compressor`: http://developer.yahoo.com/yui/compressor/
.. _`Download the JAR`: http://yuilibrary.com/downloads/#yuicompressor