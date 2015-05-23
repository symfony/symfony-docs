.. index::
   single: Assetic; Image optimization

How to Use Assetic for Image Optimization with Twig Functions
=============================================================

Amongst its many filters, Assetic has four filters which can be used for on-the-fly
image optimization. This allows you to get the benefits of smaller file sizes
without having to use an image editor to process each image. The results
are cached and can be dumped for production so there is no performance hit
for your end users.

Using Jpegoptim
---------------

`Jpegoptim`_ is a utility for optimizing JPEG files. To use it with Assetic, make
sure to have it already installed on your system and then, configure its location
using the ``bin`` option of the ``jpegoptim`` filter:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jpegoptim:
                    bin: path/to/jpegoptim

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="jpegoptim"
                bin="path/to/jpegoptim" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'jpegoptim' => array(
                    'bin' => 'path/to/jpegoptim',
                ),
            ),
        ));

It can now be used from a template:

.. configuration-block::

    .. code-block:: html+jinja

        {% image '@AppBundle/Resources/public/images/example.jpg'
            filter='jpegoptim' output='/images/example.jpg' %}
            <img src="{{ asset_url }}" alt="Example"/>
        {% endimage %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->image(
            array('@AppBundle/Resources/public/images/example.jpg'),
            array('jpegoptim')
        ) as $url): ?>
            <img src="<?php echo $view->escape($url) ?>" alt="Example"/>
        <?php endforeach ?>

Removing all EXIF Data
~~~~~~~~~~~~~~~~~~~~~~

By default, the ``jpegoptim`` filter removes some of the meta information stored
in the image. To remove all EXIF data and comments, set the ``strip_all`` option
to ``true``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jpegoptim:
                    bin: path/to/jpegoptim
                    strip_all: true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="jpegoptim"
                bin="path/to/jpegoptim"
                strip_all="true" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'jpegoptim' => array(
                    'bin'       => 'path/to/jpegoptim',
                    'strip_all' => 'true',
                ),
            ),
        ));

Lowering Maximum Quality
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the ``jpegoptim`` filter doesn't alter the quality level of the JPEG
image. Use the ``max`` option to configure the maximum quality setting (in a
scale of ``0`` to ``100``). The reduction in the image file size will of course
be at the expense of its quality:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jpegoptim:
                    bin: path/to/jpegoptim
                    max: 70

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="jpegoptim"
                bin="path/to/jpegoptim"
                max="70" />
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'jpegoptim' => array(
                    'bin' => 'path/to/jpegoptim',
                    'max' => '70',
                ),
            ),
        ));

Shorter Syntax: Twig Function
-----------------------------

If you're using Twig, it's possible to achieve all of this with a shorter
syntax by enabling and using a special Twig function. Start by adding the
following configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jpegoptim:
                    bin: path/to/jpegoptim
            twig:
                functions:
                    jpegoptim: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="jpegoptim"
                bin="path/to/jpegoptim" />
            <assetic:twig>
                <assetic:twig_function
                    name="jpegoptim" />
            </assetic:twig>
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'jpegoptim' => array(
                    'bin' => 'path/to/jpegoptim',
                ),
            ),
            'twig' => array(
                'functions' => array('jpegoptim'),
                ),
            ),
        ));

The Twig template can now be changed to the following:

.. code-block:: html+jinja

    <img src="{{ jpegoptim('@AppBundle/Resources/public/images/example.jpg') }}" alt="Example"/>

You can also specify the output directory for images in the Assetic configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        assetic:
            filters:
                jpegoptim:
                    bin: path/to/jpegoptim
            twig:
                functions:
                    jpegoptim: { output: images/*.jpg }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <assetic:config>
            <assetic:filter
                name="jpegoptim"
                bin="path/to/jpegoptim" />
            <assetic:twig>
                <assetic:twig_function
                    name="jpegoptim"
                    output="images/*.jpg" />
            </assetic:twig>
        </assetic:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('assetic', array(
            'filters' => array(
                'jpegoptim' => array(
                    'bin' => 'path/to/jpegoptim',
                ),
            ),
            'twig' => array(
                'functions' => array(
                    'jpegoptim' => array(
                        output => 'images/*.jpg'
                    ),
                ),
            ),
        ));

.. _`Jpegoptim`: http://www.kokkonen.net/tjko/projects.html
