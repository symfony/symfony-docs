.. index::
   single: Assetic; Apply filters

How to Apply an Assetic Filter to a specific File Extension
===========================================================

.. include:: /cookbook/assetic/_standard_edition_warning.inc

Assetic filters can be applied to individual files, groups of files or even,
as you'll see here, files that have a specific extension. To show you how
to handle each option, suppose that you want to use Assetic's CoffeeScript
filter, which compiles CoffeeScript files into JavaScript.

The main configuration is just the paths to ``coffee``, ``node`` and ``node_modules``.
An example configuration might look like this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                coffee:
                    bin:        /usr/bin/coffee
                    node:       /usr/bin/node
                    node_paths: [/usr/lib/node_modules/]

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
                    name="coffee"
                    bin="/usr/bin/coffee/"
                    node="/usr/bin/node/">
                    <assetic:node-path>/usr/lib/node_modules/</assetic:node-path>
                </assetic:filter>
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'coffee' => array(
                    'bin'  => '/usr/bin/coffee',
                    'node' => '/usr/bin/node',
                    'node_paths' => array('/usr/lib/node_modules/'),
                ),
            ),
        ));

Filter a single File
--------------------

You can now serve up a single CoffeeScript file as JavaScript from within your
templates:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/example.coffee' filter='coffee' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/example.coffee'),
            array('coffee')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

This is all that's needed to compile this CoffeeScript file and serve it
as the compiled JavaScript.

Filter multiple Files
---------------------

You can also combine multiple CoffeeScript files into a single output file:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/example.coffee'
                       '@AppBundle/Resources/public/js/another.coffee'
            filter='coffee' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array(
                '@AppBundle/Resources/public/js/example.coffee',
                '@AppBundle/Resources/public/js/another.coffee',
            ),
            array('coffee')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

Both files will now be served up as a single file compiled into regular JavaScript.

.. _cookbook-assetic-apply-to:

Filtering Based on a File Extension
-----------------------------------

One of the great advantages of using Assetic is reducing the number of asset
files to lower HTTP requests. In order to make full use of this, it would
be good to combine *all* your JavaScript and CoffeeScript files together
since they will ultimately all be served as JavaScript. Unfortunately just
adding the JavaScript files to the files to be combined as above will not
work as the regular JavaScript files will not survive the CoffeeScript compilation.

This problem can be avoided by using the ``apply_to`` option, which allows you
to specify which filter should always be applied to particular file extensions.
In this case you can specify that the ``coffee`` filter is applied to all
``.coffee`` files:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                coffee:
                    bin:        /usr/bin/coffee
                    node:       /usr/bin/node
                    node_paths: [/usr/lib/node_modules/]
                    apply_to:   '\.coffee$'

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
                    name="coffee"
                    bin="/usr/bin/coffee"
                    node="/usr/bin/node"
                    apply_to="\.coffee$" />
                    <assetic:node-paths>/usr/lib/node_modules/</assetic:node-path>
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'coffee' => array(
                    'bin'      => '/usr/bin/coffee',
                    'node'     => '/usr/bin/node',
                    'node_paths' => array('/usr/lib/node_modules/'),
                    'apply_to' => '\.coffee$',
                ),
            ),
        ));

With this option, you no longer need to specify the ``coffee`` filter in the
template. You can also list regular JavaScript files, all of which will be
combined and rendered as a single JavaScript file (with only the ``.coffee``
files being run through the CoffeeScript filter):

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/example.coffee'
                       '@AppBundle/Resources/public/js/another.coffee'
                       '@AppBundle/Resources/public/js/regular.js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array(
                '@AppBundle/Resources/public/js/example.coffee',
                '@AppBundle/Resources/public/js/another.coffee',
                '@AppBundle/Resources/public/js/regular.js',
            )
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>
