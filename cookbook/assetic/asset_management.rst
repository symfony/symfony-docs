How to Use Assetic for Asset Management
=======================================

Assetic is a powerful asset management library which is packaged with the
Symfony2 Standard Edition and can be easily used in Symfony2 directly from
Twig or PHP templates.

Assetic combines two major ideas: assets and filters. The assets are files
such as CSS, JavaScript and images files. The filters are things that can
be applied to these files before they are served to the browser. This allows
a separation between the asset files stored in the application and the files
actually presented to the user.

Without Assetic, you just serve the files that are stored in the application
directly:

.. configuration-block::

    .. code-block:: html+jinja

        <script src="{{ asset('js/script.js') }}" type="text/javascript" />

    .. code-block:: php

        <script src="<?php echo $view['assets']->getUrl('js/script.js') ?>"
                type="text/javascript" />

But *with* Assetic, you can manipulate these assets however you want (or
load them from anywhere) before serving them. These means you can:

* Minify and combine all of your CSS and JS files

* Run all (or just some) of your CSS or JS files through some sort of compiler,
  such as LESS, SASS or CoffeeScript

* Run image optimizations on your images

Assets
------

Using Assetic provides many advantages over directly serving the files.
The files do not need to be stored where they are served from and can be
drawn from various sources such as from within a bundle:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*'
        %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*')) as $url): ?>
        <script type="text/javascript" src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

In this example, all of the files in the ``Resources/public/js/`` directory
of the ``AcmeFooBundle`` will be loaded and served from a different location.
The actual rendered tag might simply look like:

    <script src="/js/abcd123.js"></script>

You can also combine several files into one. This helps to reduce the number
of HTTP requests which is good for front end performance, as most browsers
will only process a limited number at a time slowing down page load times.
It also allows you to maintain the files more easily by splitting them into
manageable parts. This can also help with re-usability as you can easily
split project specific files from those which can be used in other applications
but still serve them as a single file:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*'
                       '@AcmeBarBundle/Resources/public/js/form.js'
                       '@AcmeBarBundle/Resources/public/js/calendar.js'
        %}
        <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*',
                  '@AcmeBarBundle/Resources/public/js/form.js',
                  '@AcmeBarBundle/Resources/public/js/calendar.js')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

This does not only apply to your own files you can also use Assetic to
combine third party assets, such as jQuery with your own into a single file:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/thirdparty/jquery.js'
                       '@AcmeFooBundle/Resources/public/js/*'
        %}
        <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/thirdparty/jquery.js',
                  '@AcmeFooBundle/Resources/public/js/*')) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

Filters
-------

Additionally to this Assetic can apply filters to the assets before they
are served. This includes tasks such as compressing the output for smaller
file sizes which is another valuable front end optimisation. Other filters
include compiling JavaScript file from CoffeeScript files and SASS to CSS.

Many of the filters do not do the work directly but use other libraries
to do it, this so you will often have to install that software as well.
The great advantage of using Assetic to invoke these libraries is that
instead of having to run them manually when you have worked on the files,
Assetic will take care of this for you and remove this step altogether
from your development and deployment processes.

To use a filter you must specify it in the Assetic configuration
as they are not enabled by default. For example to use the JavaScript YUI
Compressor the following config needs to be added:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                yui_js:
                    jar: "%kernel.root_dir%/Resources/java/yuicompressor.jar"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="yui_js"
                jar="%kernel.root_dir%/Resources/java/yuicompressor.jar" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'yui_js' => array(
                    'jar' => '%kernel.root_dir%/Resources/java/yuicompressor.jar',
                ),
            ),
        ));


You can then specify using the filter in the template:

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


A more detail guide to configuring and using Assetic filters as well as
details of Assetic's debug mode can be found in :doc:`/cookbook/assetic/yuicompressor`.

Controlling the URL used
------------------------

If you wish to you can control the URLs which Assetic produces. This is
done from the template and is relative to the public document root:

.. configuration-block::

    .. code-block:: html+jinja

        {% javascripts '@AcmeFooBundle/Resources/public/js/*'
           output='js/combined.js'
        %}
        <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AcmeFooBundle/Resources/public/js/*'),
            array(),
            array('output' => 'js/combined.js')
        ) as $url): ?>
        <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach; ?>

Caching the output
------------------

The process of creating the files served up can be quite slow especially
when using some of the filters which invoke third party software to the
actual work. Even when working in the development environment the slow
down in the page loads if this was to be done each time would quickly get
frustrating. Fortunately in the dev environment Assetic caches the output
so this will not happen, rather than having to clear the cache manually
though, it monitors for changes to the assets and regenerates the files
as needed. This means you can work on the asset files and see the results
on page load but without having to suffer continual slow page loads.

For production, where you will not be making changes to the asset files,
performance can be increased by avoiding the step of checking for changes.
Assetic allows you to go further than this and avoid touching Symfony2
and even PHP at all when serving the files. This is done by dumping all
of the output files using a console command. These can then be served directly
by the web server as static files, increasing performance and allowing the
web server to deal with caching headers. The console command to dump the files
is:

.. code-block:: bash

    php app/console assetic:dump

.. note::

    Once you have dumped the output you will need to run the console
    command again to see any new changes. If you run it on your development
    server you will need to remove the files in order to start letting Assetic
    process the assets on the fly again.
