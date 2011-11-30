How to Apply an Assetic Filter to a Specific File Extension
===========================================================

Assetic filters can be applied to individual files, groups of files or even,
as you'll see here, files that have a specific extension. To show you how
to handle each option, let's suppose that you want to use Assetic's CoffeeScript
filter, which compiles CoffeeScript files into Javascript.

The main configuration is just the paths to coffee and node. These default
respectively to ``/usr/bin/coffee`` and ``/usr/bin/node``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                coffee:
                    bin: /usr/bin/coffee
                    node: /usr/bin/node

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="coffee"
                bin="/usr/bin/coffee"
                node="/usr/bin/node" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'coffee' => array(
                    'bin' => '/usr/bin/coffee',
                    'node' => '/usr/bin/node',
                ),
            ),
        ));

Filter a Single File
--------------------

You can now serve up a single CoffeeScript file as JavaScript from within your
templates:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/example.coffee'
            filter='coffee'
        %}
        <script src="{{ asset_url }}" type="text/javascript"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/example.coffee'),
            array('coffee')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>" type="text/javascript"></script>
        <?php endforeach; ?>

This is all that's needed to compile this CoffeeScript file and server it
as the compiled JavaScript.

Filter Multiple Files
---------------------

You can also combine multiple CoffeeScript files into a single output file:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/example.coffee'
                       '@AcmeFooBundle/Resources/public/js/another.coffee'
            filter='coffee'
        %}
        <script src="{{ asset_url }}" type="text/javascript"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/example.coffee',
                  '@AcmeFooBundle/Resources/public/js/another.coffee'),
            array('coffee')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>" type="text/javascript"></script>
        <?php endforeach; ?>

Both the files will now be served up as a single file compiled into regular
JavaScript.

Filtering based on a File Extension
-----------------------------------

One of the great advantages of using Assetic is reducing the number of asset
files to lower HTTP requests. In order to make full use of this, it would
be good to combine *all* your JavaScript and CoffeeScript files together
since they will ultimately all be served as JavaScript. Unfortunately just
adding the JavaScript files to the files to be combined as above will not
work as the regular JavaScript files will not survive the CoffeeScript compilation.

This problem can be avoided by using the ``apply_to`` option in the config,
which allows you to specify that a filter should always be applied to particular
file extensions. In this case you can specify that the Coffee filter is
applied to all ``.coffee`` files:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                coffee:
                    bin: /usr/bin/coffee
                    node: /usr/bin/node
                    apply_to: "\.coffee$"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="coffee"
                bin="/usr/bin/coffee"
                node="/usr/bin/node"
                apply_to="\.coffee$" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'coffee' => array(
                    'bin' => '/usr/bin/coffee',
                    'node' => '/usr/bin/node',
                    'apply_to' => '\.coffee$',
                ),
            ),
        ));

With this, you no longer need to specify the ``coffee`` filter in the template.
You can also list regular JavaScript files, all of which will be combined
and rendered as a single JavaScript file (with only the ``.coffee`` files
being run through the CoffeeScript filter):

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/example.coffee'
                       '@AcmeFooBundle/Resources/public/js/another.coffee'
                       '@AcmeFooBundle/Resources/public/js/regular.js'
        %}
        <script src="{{ asset_url }}" type="text/javascript"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/example.coffee',
                  '@AcmeFooBundle/Resources/public/js/another.coffee',
                  '@AcmeFooBundle/Resources/public/js/regular.js'),
            as $url): ?>
        <script src="<?php echo $view->escape($url) ?>" type="text/javascript"></script>
        <?php endforeach; ?>
