.. index::
   single: Assetic; YUI Compressor

How to Minify JavaScripts and Stylesheets with YUI Compressor
=============================================================

.. caution::

    The YUI Compressor is `no longer maintained by Yahoo`_. That's why you are
    **strongly advised to avoid using YUI utilities** unless strictly necessary.
    Read :doc:`/assetic/uglifyjs` for a modern and up-to-date alternative.

.. include:: /assetic/_standard_edition_warning.rst.inc

Yahoo! provides an excellent utility for minifying JavaScripts and stylesheets
so they travel over the wire faster, the `YUI Compressor`_. Thanks to Assetic,
you can take advantage of this tool very easily.

Download the YUI Compressor JAR
-------------------------------

The YUI Compressor is written in Java and distributed as a JAR. `Download the JAR`_
from the Yahoo! website and save it to ``app/Resources/java/yuicompressor.jar``.

Configure the YUI Filters
-------------------------

Now you need to configure two Assetic filters in your application, one for
minifying JavaScripts with the YUI Compressor and one for minifying
stylesheets:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            # java: '/usr/bin/java'
            filters:
                yui_css:
                    jar: '%kernel.root_dir%/Resources/java/yuicompressor.jar'
                yui_js:
                    jar: '%kernel.root_dir%/Resources/java/yuicompressor.jar'

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
                    name="yui_css"
                    jar="%kernel.root_dir%/Resources/java/yuicompressor.jar" />
                <assetic:filter
                    name="yui_js"
                    jar="%kernel.root_dir%/Resources/java/yuicompressor.jar" />
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            // 'java' => '/usr/bin/java',
            'filters' => array(
                'yui_css' => array(
                    'jar' => '%kernel.root_dir%/Resources/java/yuicompressor.jar',
                ),
                'yui_js' => array(
                    'jar' => '%kernel.root_dir%/Resources/java/yuicompressor.jar',
                ),
            ),
        ));

.. note::

    Windows users need to remember to update config to proper Java location.
    In Windows7 x64 bit by default it's ``C:\Program Files (x86)\Java\jre6\bin\java.exe``.

You now have access to two new Assetic filters in your application:
``yui_css`` and ``yui_js``. These will use the YUI Compressor to minify
stylesheets and JavaScripts, respectively.

Minify your Assets
------------------

You have YUI Compressor configured now, but nothing is going to happen until
you apply one of these filters to an asset. Since your assets are a part of
the view layer, this work is done in your templates:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' filter='yui_js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array('yui_js')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. note::

    The above example assumes that you have a bundle called AppBundle and your
    JavaScript files are in the ``Resources/public/js`` directory under your
    bundle. This isn't important however - you can include your JavaScript
    files no matter where they are.

With the addition of the ``yui_js`` filter to the asset tags above, you should
now see minified JavaScripts coming over the wire much faster. The same process
can be repeated to minify your stylesheets.

.. configuration-block::

    .. code-block:: html+twig

        {% stylesheets '@AppBundle/Resources/public/css/*' filter='yui_css' %}
            <link rel="stylesheet" type="text/css" media="screen" href="{{ asset_url }}" />
        {% endstylesheets %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->stylesheets(
            array('@AppBundle/Resources/public/css/*'),
            array('yui_css')
        ) as $url): ?>
            <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $view->escape($url) ?>" />
        <?php endforeach ?>

Disable Minification in Debug Mode
----------------------------------

Minified JavaScripts and stylesheets are very difficult to read, let alone
debug. Because of this, Assetic lets you disable a certain filter when your
application is in debug mode. You can do this by prefixing the filter name
in your template with a question mark: ``?``. This tells Assetic to only
apply this filter when debug mode is off.

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' filter='?yui_js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array('?yui_js')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. tip::

    Instead of adding the filter to the asset tags, you can also globally
    enable it by adding the ``apply_to`` attribute to the filter configuration, for
    example in the ``yui_js`` filter ``apply_to: "\.js$"``. To only have the filter
    applied in production, add this to the ``config_prod`` file rather than the
    common config file. For details on applying filters by file extension,
    see :ref:`assetic-apply-to`.

.. _`YUI Compressor`: http://yui.github.io/yuicompressor/
.. _`Download the JAR`: https://github.com/yui/yuicompressor/releases
.. _`no longer maintained by Yahoo`: http://yuiblog.com/blog/2013/01/24/yui-compressor-has-a-new-owner/
