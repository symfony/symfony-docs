.. index::
   single: Assetic; UglifyJs

How to Minify JavaScripts with UglifyJs
=======================================

`UglifyJs`_ is a javascript parser/compressor/beautifier toolkit. It can be used
to combine and minify javascript assets so they need less HTTP requests and makes
the website load faster.

Install UglifyJs
----------------

UglifyJs is build as an node.js npm module and can be installed using npm. First,
you need to `install node.js`_. Afterwards you can install UglifyJs using npm:

.. code-block:: bash
    
    $ npm install -g uglify-js@1
    
.. note::

    It's also possible to install UglifyJs for your symfony project only. To do this,
    install it without the ``-g`` option and specify the path where to put the module:
    
    .. code-block:: bash
    
        $ npm install uglify-js@1 /path/to/symfony/app/Resources
        
    It is recommended that you install UglifyJs in your ``app/Resources`` folder
    and add the ``node_modules`` folder to version control.
    
.. tip::
    
    This cookbook uses UglifyJs 1 instead of the newer version 2 to be compatible
    with old assetic versions. If you want to use UglifyJs version 2, make sure 
    to also use the assetic filter for this version and apply the correct configuration.

Configure the UglifyJs Filter
-----------------------------

Now we need to configure symfony2 to use the UglifyJs Filter when processing your
stylesheets:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                uglifyjs:
                    bin: /usr/local/bin/uglifyjs

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="uglifyjs"
                bin="/usr/local/bin/uglifyjs" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'uglifyjs' => array(
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

You now have access to the ``uglifyjs`` Filter in your application. 

Minify your Assets
------------------

In order to use UglifyJs on your assets, you need to apply it to them. Since 
your assets are a part of the view layer, this work is done in your templates:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='uglifyjs' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('uglifyjs')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

.. note::

    The above example assumes that you have a bundle called ``AcmeFooBundle``
    and your JavaScript files are in the ``Resources/public/js`` directory under
    your bundle. This isn't important however - you can include your Javascript
    files no matter where they are.

With the addition of the ``uglifyjs`` filter to the asset tags above, you should
now see minified JavaScripts coming over the wire much faster. 

Disable Minification in Debug Mode
----------------------------------

Minified JavaScripts are very difficult to read, let alone
debug. Because of this, Assetic lets you disable a certain filter when your
application is in debug mode. You can do this by prefixing the filter name
in your template with a question mark: ``?``. This tells Assetic to only
apply this filter when debug mode is off.

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*' filter='?uglifyjs' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array('?uglifyjs')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>


.. tip::

    Instead of adding the filter to the asset tags, you can also globally
    enable it by adding the apply-to attribute to the filter configuration, for
    example in the ``uglifyjs`` filter ``apply_to: "\.js$"``. To only have the filter
    applied in production, add this to the ``config_prod`` file rather than the
    common config file. For details on applying filters by file extension,
    see :ref:`cookbook-assetic-apply-to`.


.. _`UglifyJs`: https://github.com/mishoo/UglifyJS
.. _`install node.js`: http://nodejs.org/
