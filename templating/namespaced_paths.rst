.. index::
   single: Templating; Namespaced Twig Paths

How to Use and Register Namespaced Twig Paths
=============================================

Usually, when you refer to a template, you'll use the ``MyBundle:Subdir:filename.html.twig``
format (see :ref:`template-naming-locations`).

Twig also natively offers a feature called "namespaced paths", and support
is built-in automatically for all of your bundles.

Take the following paths as an example:

.. code-block:: twig

    {% extends "AppBundle::layout.html.twig" %}
    {{ include('AppBundle:Foo:bar.html.twig') }}

With namespaced paths, the following works as well:

.. code-block:: twig

    {% extends "@App/layout.html.twig" %}
    {{ include('@App/Foo/bar.html.twig') }}

Both paths are valid and functional by default in Symfony.

.. tip::

    As an added bonus, the namespaced syntax is faster.

Registering your own Namespaces
-------------------------------

You can also register your own custom namespaces. Suppose that you're using
some third-party library that includes Twig templates that live in
``vendor/acme/foo-bar/templates``. First, register a namespace for this
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
``vendor/acme/foo-bar/templates`` directory. Assuming there's a file
called ``sidebar.twig`` in that directory, you can use it easily:

.. code-block:: twig

    {{ include('@foo_bar/sidebar.twig') }}

Multiple Paths per Namespace
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also assign several paths to the same template namespace. The order in
which paths are configured is very important, because Twig will always load
the first template that exists, starting from the first configured path. This
feature can be used as a fallback mechanism to load generic templates when the
specific template doesn't exist.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            paths:
                "%kernel.root_dir%/../vendor/acme/themes/theme1": theme
                "%kernel.root_dir%/../vendor/acme/themes/theme2": theme
                "%kernel.root_dir%/../vendor/acme/themes/common": theme

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:twig="http://symfony.com/schema/dic/twig"
        >

            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%">
                <twig:path namespace="theme">%kernel.root_dir%/../vendor/acme/themes/theme1</twig:path>
                <twig:path namespace="theme">%kernel.root_dir%/../vendor/acme/themes/theme2</twig:path>
                <twig:path namespace="theme">%kernel.root_dir%/../vendor/acme/themes/common</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.root_dir%/../vendor/acme/themes/theme1' => 'theme',
                '%kernel.root_dir%/../vendor/acme/themes/theme2' => 'theme',
                '%kernel.root_dir%/../vendor/acme/themes/common' => 'theme',
            ),
        ));

Now, you can use the same ``@theme`` namespace to refer to any template located
in the previous three directories:

.. code-block:: twig

    {{ include('@theme/header.twig') }}
