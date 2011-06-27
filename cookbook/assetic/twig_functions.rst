How to Use Assetic For Image Optimisation with Twig Functions
=============================================================

Amongst its many filters, Assetic has four which can be used for on-the-fly
image optimisation. This way you can get the benefits of smaller file sizes
without having to use an image editor to process each image. The results
are cached and can be dumped for production so there is no performance
hit for your end users.

To use Jpegoptim to optimise JPEG images it needs adding to the Assetic
config:

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


It can then be used from templates:

.. configuration-block::

    .. code-block:: html+jinja

        {% image '@AcmeFooBundle/Resources/public/images/example.jpg'
            filter='jpegoptim' output='/images/example.jpg'
        %}
        <img src="{{ asset_url }}" alt="Example"/>
        {% endimage %}

    .. code-block:: html+php

        <?php foreach ($view['assetic']->images(
            array('@AcmeFooBundle/Resources/public/images/example.jpg'),
            array('jpegoptim')) as $url): ?>
        <img src="<?php echo $view->escape($url) ?>" alt="Example"/>
        <?php endforeach; ?>

By default running this filter only removes some of the meta information
stored in the file. Any EXIF data and comments are not removed, you can
removed these by using as well by specifying the ``strip_all` option:

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
                    'bin' => 'path/to/jpegoptim',
                    'strip_all' => 'true',
                ),
            ),
        ));


The quality level of the JPEG is not affected by default. You can gain
further file size reductions by setting the max quality setting lower than
the current level of the images. This will of course be at the expense
of image quality:

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


Twig Functions
--------------

It is possible to achieve this with a much simpler Twig template by setting
up a Twig function from the Assetic config. By adding the following config:

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


The Twig template can be changed to the following:

.. code-block:: html+jinja

    <img src="{{ jpegoptim('@AcmeFooBundle/Resources/public/images/example.jpg') }}"
         alt="Example"/>


You can specify the output directory in the config in the following way:

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


The function name maps directly to the filter name so these must be the
same for this to work.
