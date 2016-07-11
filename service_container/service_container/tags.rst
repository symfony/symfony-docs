.. index::
    single: DependencyInjection; Tags
    single: Service Container; Tags

How to Work with Tags
=====================

In the same way that a blog post on the web might be tagged with things such
as "Symfony" or "PHP", services configured in your container can also be
tagged. In the service container, a tag implies that the service is meant
to be used for a specific purpose. Take the following example:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            foo.twig.extension:
                class: AppBundle\Extension\FooExtension
                public: false
                tags:
                    -  { name: twig.extension }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service
                    id="foo.twig.extension"
                    class="AppBundle\Extension\FooExtension"
                    public="false">

                    <tag name="twig.extension" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('AppBundle\Extension\FooExtension');
        $definition->setPublic(false);
        $definition->addTag('twig.extension');
        $container->setDefinition('foo.twig.extension', $definition);

The ``twig.extension`` tag is a special tag that the TwigBundle uses
during configuration. By giving the service this ``twig.extension`` tag,
the bundle knows that the ``foo.twig.extension`` service should be registered
as a Twig extension with Twig. In other words, Twig finds all services tagged
with ``twig.extension`` and automatically registers them as extensions.

Tags, then, are a way to tell Symfony or other third-party bundles that
your service should be registered or used in some special way by the bundle.

For a list of all the tags available in the core Symfony Framework, check
out :doc:`/reference/dic_tags`. Each of these has a different effect on your
service and many tags require additional arguments (beyond just the ``name``
parameter).
