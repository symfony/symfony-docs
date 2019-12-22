.. index::
    single: Templating; hinclude.js

How to Embed Asynchronous Content with HInclude
===============================================

Controllers can be embedded asynchronously using the `HInclude`_ JavaScript library.
As the embedded content comes from another page (or controller for that matter),
Symfony uses a version of the standard ``render()`` function to configure ``hinclude``
tags:

.. code-block:: twig

    {{ render_hinclude(controller('...')) }}
    {{ render_hinclude(url('...')) }}

.. note::

    HInclude_ needs to be included in your page to work.

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
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <!-- ... -->
                <framework:config>
                    <framework:fragment path="/_fragment"/>
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', [
                // ...
                'fragments' => ['path' => '/_fragment'],
            ]);

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
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:templating hinclude-default-template="hinclude.html.twig"/>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', [
            // ...
            'templating' => [
                'hinclude_default_template' => 'hinclude.html.twig',
            ],
        ]);

You can define default templates per ``render()`` function (which will override
any global default template that is defined):

.. code-block:: twig

    {{ render_hinclude(controller('...'),  {
        'default': 'default/content.html.twig'
    }) }}

Or you can also specify a string to display as the default content:

.. code-block:: twig

    {{ render_hinclude(controller('...'), {'default': 'Loading...'}) }}

.. _`HInclude`: http://mnot.github.io/hinclude/
