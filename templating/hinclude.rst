.. index::
    single: Templating; hinclude.js

How to Embed Asynchronous Content with hinclude.js
==================================================

Controllers can be embedded asynchronously using the hinclude.js_ JavaScript library.
As the embedded content comes from another page (or controller for that matter),
Symfony uses a version of the standard ``render`` function to configure ``hinclude``
tags:

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...')) }}
        {{ render_hinclude(url('...')) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array('renderer' => 'hinclude')
        ) ?>

        <!-- The url() method was introduced in Symfony 2.8. Prior to 2.8, you
             had to use generate() with UrlGeneratorInterface::ABSOLUTE_URL
             passed as the third argument. -->
        <?php echo $view['actions']->render(
            $view['router']->url('...'),
            array('renderer' => 'hinclude')
        ) ?>

.. note::

   hinclude.js_ needs to be included in your page to work.

.. note::

    When using a controller instead of a URL, you must enable the Symfony
    ``fragments`` configuration:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            framework:
                # ...
                fragments: { path: /_fragment }

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <!-- ... -->
                <framework:config>
                    <framework:fragments path="/_fragment" />
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', array(
                // ...
                'fragments' => array('path' => '/_fragment'),
            ));

Default content (while loading or if JavaScript is disabled) can be set globally
in your application configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating:
                hinclude_default_template: hinclude.html.twig

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:templating hinclude-default-template="hinclude.html.twig" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'templating' => array(
                'hinclude_default_template' => array(
                    'hinclude.html.twig',
                ),
            ),
        ));

You can define default templates per ``render`` function (which will override
any global default template that is defined):

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...'),  {
            'default': 'default/content.html.twig'
        }) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array(
                'renderer' => 'hinclude',
                'default'  => 'default/content.html.twig',
            )
        ) ?>

Or you can also specify a string to display as the default content:

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...'), {'default': 'Loading...'}) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array(
                'renderer' => 'hinclude',
                'default'  => 'Loading...',
            )
        ) ?>

.. _`hinclude.js`: http://mnot.github.io/hinclude/
