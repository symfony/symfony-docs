.. index::
   single: Templating; Namespaced Twig Paths

How to Use and Register Namespaced Twig Paths
=============================================

Usually, when you refer to a template, you'll use the Twig namespaced paths, which
are automatically registered for your bundles:

.. code-block:: twig

    {% extends "@App/layout.html.twig" %}
    {{ include('@App/Foo/bar.html.twig') }}

.. note::

    In the past, Symfony used a different syntax to refer to templates. This
    format, which uses colons (``:``) to separate each template path section, is
    less consistent and has worse performance than the Twig syntax. For reference
    purposes, this is the equivalent notation of the previous example:

    .. code-block:: twig

        {# the following template syntax is no longer recommended #}
        {% extends "AppBundle::layout.html.twig" %}
        {{ include('AppBundle:Foo:bar.html.twig') }}

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
                '%kernel.project_dir%/vendor/acme/foo-bar/templates': foo_bar

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%">
                <twig:path namespace="foo_bar">%kernel.project_dir%/vendor/acme/foo-bar/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.project_dir%/vendor/acme/foo-bar/templates' => 'foo_bar',
            ),
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
                '%kernel.project_dir%/vendor/acme/themes/theme1': theme
                '%kernel.project_dir%/vendor/acme/themes/theme2': theme
                '%kernel.project_dir%/vendor/acme/themes/common': theme

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <twig:config debug="%kernel.debug%" strict-variables="%kernel.debug%">
                <twig:path namespace="theme">%kernel.project_dir%/vendor/acme/themes/theme1</twig:path>
                <twig:path namespace="theme">%kernel.project_dir%/vendor/acme/themes/theme2</twig:path>
                <twig:path namespace="theme">%kernel.project_dir%/vendor/acme/themes/common</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.project_dir%/vendor/acme/themes/theme1' => 'theme',
                '%kernel.project_dir%/vendor/acme/themes/theme2' => 'theme',
                '%kernel.project_dir%/vendor/acme/themes/common' => 'theme',
            ),
        ));

Now, you can use the same ``@theme`` namespace to refer to any template located
in the previous three directories:

.. code-block:: twig

    {{ include('@theme/header.twig') }}
