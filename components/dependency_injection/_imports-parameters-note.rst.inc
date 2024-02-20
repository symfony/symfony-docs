.. note::

    Due to the way in which parameters are resolved, you cannot use them
    to build paths in imports dynamically. This means that something like
    **the following does not work:**

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            imports:
                - { resource: '%kernel.project_dir%/somefile.yaml' }

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <imports>
                    <import resource="%kernel.project_dir%/somefile.yaml"/>
                </imports>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return static function (ContainerConfigurator $container): void {
                $container->import('%kernel.project_dir%/somefile.yaml');
            };
