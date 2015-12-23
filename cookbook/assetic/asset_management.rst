.. index::
   single: Assetic; Introduction

How to Use Assetic for Asset Management
=======================================

Installing and Enabling Assetic
-------------------------------

Starting from Symfony 2.8, Assetic is no longer included by default in the
Symfony Standard Edition. Before using any of its features, install the
AsseticBundle executing this console command in your project:

.. code-block:: bash

    $ composer require symfony/assetic-bundle

Then, enable the bundle in the ``AppKernel.php`` file of your Symfony application::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            );

            // ...
        }
    }

Finally, add the following minimal configuration to enable Assetic support in
your application:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            debug:          '%kernel.debug%'
            use_controller: '%kernel.debug%'
            filters:
                cssrewrite: ~

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config debug="%kernel.debug%" use-controller="%kernel.debug%">
                <assetic:filters cssrewrite="null" />
            </assetic:config>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'debug' => '%kernel.debug%',
            'use_controller' => '%kernel.debug%',
            'filters' => array(
                'cssrewrite' => null,
            ),
            // ...
        ));

        // ...

Introducing Assetic
-------------------

Assetic combines two major ideas: :ref:`assets <cookbook-assetic-assets>` and
:ref:`filters <cookbook-assetic-filters>`. The assets are files such as CSS,
JavaScript and image files. The filters are things that can be applied to
these files before they are served to the browser. This allows a separation
between the asset files stored in the application and the files actually presented
to the user.

Without Assetic, you just serve the files that are stored in the application
directly:

.. configuration-block::

    .. code-block:: html+twig

        <script src="{{ asset('js/script.js') }}"></script>

    .. code-block:: php

        <script src="<?php echo $view['assets']->getUrl('js/script.js') ?>"></script>

But *with* Assetic, you can manipulate these assets however you want (or
load them from anywhere) before serving them. This means you can:

* Minify and combine all of your CSS and JS files

* Run all (or just some) of your CSS or JS files through some sort of compiler,
  such as LESS, SASS or CoffeeScript

* Run image optimizations on your images

.. _cookbook-assetic-assets:

Assets
------

Using Assetic provides many advantages over directly serving the files.
The files do not need to be stored where they are served from and can be
drawn from various sources such as from within a bundle.

You can use Assetic to process :ref:`CSS stylesheets <cookbook-assetic-including-css>`,
:ref:`JavaScript files <cookbook-assetic-including-javascript>` and
:ref:`images <cookbook-assetic-including-image>`. The philosophy
behind adding either is basically the same, but with a slightly different syntax.

.. _cookbook-assetic-including-javascript:

Including JavaScript Files
~~~~~~~~~~~~~~~~~~~~~~~~~~

To include JavaScript files, use the ``javascripts`` tag in any template:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. note::

    If your application templates use the default block names from the Symfony
    Standard Edition, the ``javascripts`` tag will most commonly live in the
    ``javascripts`` block:

    .. code-block:: html+twig

        {# ... #}
        {% block javascripts %}
            {% javascripts '@AppBundle/Resources/public/js/*' %}
                <script src="{{ asset_url }}"></script>
            {% endjavascripts %}
        {% endblock %}
        {# ... #}

.. tip::

    You can also include CSS stylesheets: see :ref:`cookbook-assetic-including-css`.

In this example, all files in the ``Resources/public/js/`` directory of the
AppBundle will be loaded and served from a different location. The actual
rendered tag might simply look like:

.. code-block:: html

    <script src="/app_dev.php/js/abcd123.js"></script>

This is a key point: once you let Assetic handle your assets, the files are
served from a different location. This *will* cause problems with CSS files
that reference images by their relative path. See :ref:`cookbook-assetic-cssrewrite`.

.. _cookbook-assetic-including-css:

Including CSS Stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~

To bring in CSS stylesheets, you can use the same technique explained above,
except with the ``stylesheets`` tag:

.. configuration-block::

    .. code-block:: html+twig

        {% stylesheets 'bundles/app/css/*' filter='cssrewrite' %}
            <link rel="stylesheet" href="{{ asset_url }}" />
        {% endstylesheets %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->stylesheets(
            array('bundles/app/css/*'),
            array('cssrewrite')
        ) as $url): ?>
            <link rel="stylesheet" href="<?php echo $view->escape($url) ?>" />
        <?php endforeach ?>

.. note::

    If your application templates use the default block names from the Symfony
    Standard Edition, the ``stylesheets`` tag will most commonly live in the
    ``stylesheets`` block:

    .. code-block:: html+twig

        {# ... #}
        {% block stylesheets %}
            {% stylesheets 'bundles/app/css/*' filter='cssrewrite' %}
                <link rel="stylesheet" href="{{ asset_url }}" />
            {% endstylesheets %}
        {% endblock %}
        {# ... #}

But because Assetic changes the paths to your assets, this *will* break any
background images (or other paths) that uses relative paths, unless you use
the :ref:`cssrewrite <cookbook-assetic-cssrewrite>` filter.

.. note::

    Notice that in the original example that included JavaScript files, you
    referred to the files using a path like ``@AppBundle/Resources/public/file.js``,
    but that in this example, you referred to the CSS files using their actual,
    publicly-accessible path: ``bundles/app/css``. You can use either, except
    that there is a known issue that causes the ``cssrewrite`` filter to fail
    when using the ``@AppBundle`` syntax for CSS stylesheets.

.. _cookbook-assetic-including-image:

Including Images
~~~~~~~~~~~~~~~~

To include an image you can use the ``image`` tag.

.. configuration-block::

    .. code-block:: html+twig

        {% image '@AppBundle/Resources/public/images/example.jpg' %}
            <img src="{{ asset_url }}" alt="Example" />
        {% endimage %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->image(
            array('@AppBundle/Resources/public/images/example.jpg')
        ) as $url): ?>
            <img src="<?php echo $view->escape($url) ?>" alt="Example" />
        <?php endforeach ?>

You can also use Assetic for image optimization. More information in
:doc:`/cookbook/assetic/jpeg_optimize`.

.. tip::

    Instead of using Assetic to include images, you may consider using the
    `LiipImagineBundle`_ community bundle, which allows to compress and
    manipulate images (rotate, resize, watermark, etc.) before serving them.

.. _cookbook-assetic-cssrewrite:

Fixing CSS Paths with the ``cssrewrite`` Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since Assetic generates new URLs for your assets, any relative paths inside
your CSS files will break. To fix this, make sure to use the ``cssrewrite``
filter with your ``stylesheets`` tag. This parses your CSS files and corrects
the paths internally to reflect the new location.

You can see an example in the previous section.

.. caution::

    When using the ``cssrewrite`` filter, don't refer to your CSS files using
    the ``@AppBundle`` syntax. See the note in the above section for details.

Combining Assets
~~~~~~~~~~~~~~~~

One feature of Assetic is that it will combine many files into one. This helps
to reduce the number of HTTP requests, which is great for front-end performance.
It also allows you to maintain the files more easily by splitting them into
manageable parts. This can help with re-usability as you can easily split
project-specific files from those which can be used in other applications,
but still serve them as a single file:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts
            '@AppBundle/Resources/public/js/*'
            '@AcmeBarBundle/Resources/public/js/form.js'
            '@AcmeBarBundle/Resources/public/js/calendar.js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array(
                '@AppBundle/Resources/public/js/*',
                '@AcmeBarBundle/Resources/public/js/form.js',
                '@AcmeBarBundle/Resources/public/js/calendar.js',
            )
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

In the ``dev`` environment, each file is still served individually, so that
you can debug problems more easily. However, in the ``prod`` environment
(or more specifically, when the ``debug`` flag is ``false``), this will be
rendered as a single ``script`` tag, which contains the contents of all of
the JavaScript files.

.. tip::

    If you're new to Assetic and try to use your application in the ``prod``
    environment (by using the ``app.php`` controller), you'll likely see
    that all of your CSS and JS breaks. Don't worry! This is on purpose.
    For details on using Assetic in the ``prod`` environment, see :ref:`cookbook-assetic-dumping`.

And combining files doesn't only apply to *your* files. You can also use Assetic to
combine third party assets, such as jQuery, with your own into a single file:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts
            '@AppBundle/Resources/public/js/thirdparty/jquery.js'
            '@AppBundle/Resources/public/js/*' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array(
                '@AppBundle/Resources/public/js/thirdparty/jquery.js',
                '@AppBundle/Resources/public/js/*',
            )
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

Using Named Assets
~~~~~~~~~~~~~~~~~~

AsseticBundle configuration directives allow you to define named asset sets.
You can do so by defining the input files, filters and output files in your
configuration under the ``assetic`` section. Read more in the
:doc:`assetic config reference </reference/configuration/assetic>`.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            assets:
                jquery_and_ui:
                    inputs:
                        - '@AppBundle/Resources/public/js/thirdparty/jquery.js'
                        - '@AppBundle/Resources/public/js/thirdparty/jquery.ui.js'

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
                <assetic:asset name="jquery_and_ui">
                    <assetic:input>@AppBundle/Resources/public/js/thirdparty/jquery.js</assetic:input>
                    <assetic:input>@AppBundle/Resources/public/js/thirdparty/jquery.ui.js</assetic:input>
                </assetic:asset>
            </assetic:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'assets' => array(
                'jquery_and_ui' => array(
                    'inputs' => array(
                        '@AppBundle/Resources/public/js/thirdparty/jquery.js',
                        '@AppBundle/Resources/public/js/thirdparty/jquery.ui.js',
                    ),
                ),
            ),
        );

After you have defined the named assets, you can reference them in your templates
with the ``@named_asset`` notation:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts
            '@jquery_and_ui'
            '@AppBundle/Resources/public/js/*' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array(
                '@jquery_and_ui',
                '@AppBundle/Resources/public/js/*',
            )
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. _cookbook-assetic-filters:

Filters
-------

Once they're managed by Assetic, you can apply filters to your assets before
they are served. This includes filters that compress the output of your assets
for smaller file sizes (and better frontend optimization). Other filters
can compile CoffeeScript files to JavaScript and process SASS into CSS.
In fact, Assetic has a long list of available filters.

Many of the filters do not do the work directly, but use existing third-party
libraries to do the heavy-lifting. This means that you'll often need to install
a third-party library to use a filter. The great advantage of using Assetic
to invoke these libraries (as opposed to using them directly) is that instead
of having to run them manually after you work on the files, Assetic will
take care of this for you and remove this step altogether from your development
and deployment processes.

To use a filter, you first need to specify it in the Assetic configuration.
Adding a filter here doesn't mean it's being used - it just means that it's
available to use (you'll use the filter below).

For example to use the UglifyJS JavaScript minifier the following configuration
should be defined:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                uglifyjs2:
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
                    'bin' => '/usr/local/bin/uglifyjs',
                ),
            ),
        ));

Now, to actually *use* the filter on a group of JavaScript files, add it
into your template:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' filter='uglifyjs2' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array('uglifyjs2')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

A more detailed guide about configuring and using Assetic filters as well as
details of Assetic's debug mode can be found in :doc:`/cookbook/assetic/uglifyjs`.

Controlling the URL Used
------------------------

If you wish to, you can control the URLs that Assetic produces. This is
done from the template and is relative to the public document root:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' output='js/compiled/main.js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array(),
            array('output' => 'js/compiled/main.js')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. note::

    Symfony also contains a method for cache *busting*, where the final URL
    generated by Assetic contains a query parameter that can be incremented
    via configuration on each deployment. For more information, see the
    :ref:`ref-framework-assets-version` configuration option.

.. _cookbook-assetic-dumping:

Dumping Asset Files
-------------------

In the ``dev`` environment, Assetic generates paths to CSS and JavaScript
files that don't physically exist on your computer. But they render nonetheless
because an internal Symfony controller opens the files and serves back the
content (after running any filters).

This kind of dynamic serving of processed assets is great because it means
that you can immediately see the new state of any asset files you change.
It's also bad, because it can be quite slow. If you're using a lot of filters,
it might be downright frustrating.

Fortunately, Assetic provides a way to dump your assets to real files, instead
of being generated dynamically.

Dumping Asset Files in the ``prod`` Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the ``prod`` environment, your JS and CSS files are represented by a single
tag each. In other words, instead of seeing each JavaScript file you're including
in your source, you'll likely just see something like this:

.. code-block:: html

    <script src="/js/abcd123.js"></script>

Moreover, that file does **not** actually exist, nor is it dynamically rendered
by Symfony (as the asset files are in the ``dev`` environment). This is on
purpose - letting Symfony generate these files dynamically in a production
environment is just too slow.

.. _cookbook-assetic-dump-prod:

Instead, each time you use your application in the ``prod`` environment (and therefore,
each time you deploy), you should run the following command:

.. code-block:: bash

    $ php bin/console assetic:dump --env=prod --no-debug

This will physically generate and write each file that you need (e.g. ``/js/abcd123.js``).
If you update any of your assets, you'll need to run this again to regenerate
the file.

Dumping Asset Files in the ``dev`` Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, each asset path generated in the ``dev`` environment is handled
dynamically by Symfony. This has no disadvantage (you can see your changes
immediately), except that assets can load noticeably slow. If you feel like
your assets are loading too slowly, follow this guide.

First, tell Symfony to stop trying to process these files dynamically. Make
the following change in your ``config_dev.yml`` file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config_dev.yml
        assetic:
            use_controller: false

    .. code-block:: xml

        <!-- app/config/config_dev.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config use-controller="false" />
        </container>

    .. code-block:: php

        // app/config/config_dev.php
        $container->loadFromExtension('assetic', array(
            'use_controller' => false,
        ));

Next, since Symfony is no longer generating these assets for you, you'll
need to dump them manually. To do so, run the following command:

.. code-block:: bash

    $ php bin/console assetic:dump

This physically writes all of the asset files you need for your ``dev``
environment. The big disadvantage is that you need to run this each time
you update an asset. Fortunately, by using the ``assetic:watch`` command,
assets will be regenerated automatically *as they change*:

.. code-block:: bash

    $ php bin/console assetic:watch

The ``assetic:watch`` command was introduced in AsseticBundle 2.4. In prior
versions, you had to use the ``--watch`` option of the ``assetic:dump``
command for the same behavior.

Since running this command in the ``dev`` environment may generate a bunch
of files, it's usually a good idea to point your generated asset files to
some isolated directory (e.g. ``/js/compiled``), to keep things organized:

.. configuration-block::

    .. code-block:: html+twig

        {% javascripts '@AppBundle/Resources/public/js/*' output='js/compiled/main.js' %}
            <script src="{{ asset_url }}"></script>
        {% endjavascripts %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->javascripts(
            array('@AppBundle/Resources/public/js/*'),
            array(),
            array('output' => 'js/compiled/main.js')
        ) as $url): ?>
            <script src="<?php echo $view->escape($url) ?>"></script>
        <?php endforeach ?>

.. _`LiipImagineBundle`: https://github.com/liip/LiipImagineBundle
