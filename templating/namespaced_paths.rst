.. index::
   single: Templating; Namespaced Twig Paths

How to Use and Register Namespaced Twig Paths
=============================================

Usually, when you refer to a template, you'll use the relative path from the
main ``templates/`` dir at the root of the project:

.. code-block:: twig

    {# this template is located in templates/layout.html.twig #}
    {% extends "layout.html.twig" %}

    {# this template is located in templates/user/profile.html.twig #}
    {{ include('user/profile.html.twig') }}

If the application defines lots of templates and stores them in deep nested
directories, you may consider using **Twig namespaces**, which create shortcuts
to template directories.

Registering your own Namespaces
-------------------------------

Suppose that you're using some third-party library that includes Twig templates
that live in ``vendor/acme/foo-bar/templates/``. This path is too long, so you
can define a ``foo_bar`` Twig namespace as a shortcut.

First, register a namespace for this directory:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths:
                '%kernel.project_dir%/vendor/acme/foo-bar/templates': foo_bar

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
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

        // config/packages/twig.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.project_dir%/vendor/acme/foo-bar/templates' => 'foo_bar',
            ),
        ));

The registered namespace is called ``foo_bar``, but you must prefix the ``@``
character when using it in templates (that's how Twig can differentiate
namespaces from regular paths). Assuming there's a file called ``sidebar.twig``
in the ``vendor/acme/foo-bar/templates/`` directory, you can refer to it as:

.. code-block:: twig

    {{ include('@foo_bar/sidebar.twig') }}

Multiple Paths per Namespace
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

A single Twig namespace can be associated with multiple paths. The order in
which paths are configured is very important, because Twig will always load
the first template that exists, starting from the first configured path. This
feature can be used as a fallback mechanism to load generic templates when the
specific template doesn't exist.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths:
                '%kernel.project_dir%/vendor/acme/themes/theme1': theme
                '%kernel.project_dir%/vendor/acme/themes/theme2': theme
                '%kernel.project_dir%/vendor/acme/themes/common': theme

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
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

        // config/packages/twig.php
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
