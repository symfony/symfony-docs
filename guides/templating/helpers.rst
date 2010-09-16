Helpers
=======

.. _templating_renderer_tag:

Enabling Custom Template Renderers
----------------------------------

To enable a custom template renderer, add it as a regular service in one of
your configuration, tag it with ``templating.renderer`` and define an
``alias`` attribute (the renderer will be known by this alias in template
name):

.. configuration-block::

    .. code-block:: yaml

        services:
            templating.renderer.your_renderer_name:
                class: Fully\Qualified\Renderer\Class\Name
                tags:
                    - { name: templating.renderer, alias: alias_name }

    .. code-block:: xml

        <service id="templating.renderer.your_renderer_name" class="Fully\Qualified\Renderer\Class\Name">
            <tag name="templating.renderer" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('templating.renderer.your_renderer_name', 'Fully\Qualified\Renderer\Class\Name')
            ->addTag('templating.renderer', array('alias' => 'alias_name'))
        ;

.. _templating_helper_tag:

Enabling Custom Template Helpers
--------------------------------

To enable a custom template helper, add it as a regular service in one of your
configuration, tag it with ``templating.helper`` and define an ``alias``
attribute (the helper will be accessible via this name is the templates):

.. configuration-block::

    .. code-block:: yaml

        services:
            templating.helper.your_helper_name:
                class: Fully\Qualified\Helper\Class\Name
                tags:
                    - { name: templating.helper, alias: alias_name }

    .. code-block:: xml

        <service id="templating.helper.your_helper_name" class="Fully\Qualified\Helper\Class\Name">
            <tag name="templating.helper" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('templating.helper.your_helper_name', 'Fully\Qualified\Helper\Class\Name')
            ->addTag('templating.helper', array('alias' => 'alias_name'))
        ;
