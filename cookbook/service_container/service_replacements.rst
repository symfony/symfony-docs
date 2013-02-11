
How to replace services and aliases provided by other Bundles
=============================================================

.. versionadded:: 2.3
   The ability to replace existing services and aliases
   is new to version 2.3

Sometimes you might want to replace service or alias that is provided by
different Bundle; for example you might want to completely re-invent the
wheel or just decorate implementation of particular feature.

Lets assume that some bundle provides service with ID ``bundle.service``
and we want to replace that service with our implementation:

.. configuration-block::

    .. code-block:: yaml

        # yoursbundle/Resources/config/services.yml
        service:
            yoursbundle.my.service:
                class: YoursBundle\YourClass
                tags:
                    - { name: framework.service_replacer, replaces: bundle.service }

    .. code-block:: xml

        <!-- yoursbundle/Resources/config/services.xml -->
        <service id="yoursbundle.my.service"
        class="YoursBundle\YourClass">
            <tag name="framework.service_replacer" replaces="bundle.service" />
        </service>

    .. code-block:: php

        $container->register('yoursbundle.my.service', 'YoursBundle\YourClass')
            ->addTag('framework.service_replacer', array('replaces' => 'bundle.service'));

Now when you'll request ``bundle.service`` from container, you'll get yours
implementation (``yoursbundle.my.service``) instead.

.. note::

    If old service was tagged, the tags will be removed, you have to properly
    tag yours definition to behave correctly, as replacement.
