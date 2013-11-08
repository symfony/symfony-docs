.. index::
   single: Templating; Namespaced Twig Paths

How to use and Register namespaced Twig Paths
=============================================

.. versionadded:: 2.2
    Namespaced path support was added in 2.2.

Usually, when you refer to a template, you'll use the ``MyBundle:Subdir:filename.html.twig``
format (see :ref:`template-naming-locations`).

Twig also natively offers a feature called "namespaced paths", and support
is built-in automatically for all of your bundles.

Take the following paths as an example:

.. code-block:: jinja

    {% extends "AcmeDemoBundle::layout.html.twig" %}
    {% include "AcmeDemoBundle:Foo:bar.html.twig" %}

With namespaced paths, the following works as well:

.. code-block:: jinja

    {% extends "@AcmeDemo/layout.html.twig" %}
    {% include "@AcmeDemo/Foo/bar.html.twig" %}

Both paths are valid and functional by default in Symfony2.

.. tip::

    As an added bonus, the namespaced syntax is faster.

Registering your own namespaces
-------------------------------

You can also register your own custom namespaces. Suppose that you're using
some third-party library that includes Twig templates that live in
``vendor/acme/foo-project/templates``. First, register a namespace for this
directory:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            paths:
                "%kernel.root_dir%/../vendor/acme/foo-bar/templates": foo_bar

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xmlns:twig="http://symfony.com/schema/dic/twig"
        >

            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%">
                <twig:path namespace="foo_bar">%kernel.root_dir%/../vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.root_dir%/../vendor/acme/foo-bar/templates' => 'foo_bar',
            );
        ));

The registered namespace is called ``foo_bar``, which refers to the
``vendor/acme/foo-project/templates`` directory. Assuming there's a file
called ``sidebar.twig`` in that directory, you can use it easily:

.. code-block:: jinja

    {% include '@foo_bar/side.bar.twig` %}