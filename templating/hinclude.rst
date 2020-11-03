.. index::
    single: Templating; hinclude.js

How to Embed Asynchronous Content with hinclude.js
==================================================

:ref:`Embedding controllers in templates <templates-embed-controllers>` is one
of the ways to reuse contents across multiple templates. To further improve
performance you can use the `hinclude.js`_ JavaScript library to embed
controllers asynchronously.

First, include the `hinclude.js`_ library in your page
:ref:`linking to it <templates-link-to-assets>` from the template or adding it
to your application JavaScript :doc:`using Webpack Encore </frontend>`.

As the embedded content comes from another page (or controller for that matter),
Symfony uses a version of the standard ``render()`` function to configure
``hinclude`` tags in templates:

.. code-block:: twig

    {{ render_hinclude(controller('...')) }}
    {{ render_hinclude(url('...')) }}

.. note::

    When using the ``controller()`` function, you must also configure the
    :ref:`fragments path option <fragments-path-config>`.

When JavaScript is disabled or it takes a long time to load you can display a
default content rendering some template:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            fragments:
                hinclude_default_template: hinclude.html.twig

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:fragments hinclude-default-template="hinclude.html.twig"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework->fragments()
                ->hincludeDefaultTemplate('hinclude.html.twig')
            ;
        };

You can define default templates per ``render()`` function (which will override
any global default template that is defined):

.. code-block:: twig

    {{ render_hinclude(controller('...'),  {
        default: 'default/content.html.twig'
    }) }}

Or you can also specify a string to display as the default content:

.. code-block:: twig

    {{ render_hinclude(controller('...'), {default: 'Loading...'}) }}

Use the ``attributes`` option to define the value of hinclude.js options:

.. code-block:: twig

    {# by default, cross-site requests don't use credentials such as cookies, authorization
       headers or TLS client certificates; set this option to 'true' to use them #}
    {{ render_hinclude(controller('...'), {attributes: {'data-with-credentials': 'true'}}) }}

    {# by default, the JavaScript code included in the loaded contents is not run;
       set this option to 'true' to run that JavaScript code #}
    {{ render_hinclude(controller('...'), {attributes: {evaljs: 'true'}}) }}

.. _`hinclude.js`: http://mnot.github.io/hinclude/
