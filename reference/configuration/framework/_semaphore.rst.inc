semaphore
~~~~~~~~~

**type**: ``string`` | ``array``

The default semaphore adapter. Store DSNs are also allowed.

.. _reference-semaphore-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for semaphore or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-semaphore-resources:

resources
.........

**type**: ``array``

A map of semaphore stores to be created by the framework extension, with
the name as key and DSN or service id as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/semaphore.yaml
        framework:
            semaphore: '%env(SEMAPHORE_DSN)%'

    .. code-block:: xml

        <!-- config/packages/semaphore.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:semaphore>
                    <framework:resource name="default">%env(SEMAPHORE_DSN)%</framework:resource>
                </framework:semaphore>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/semaphore.php
        use Symfony\Config\FrameworkConfig;
        use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

        return static function (FrameworkConfig $framework): void {
            $framework->semaphore()
                ->resource('default', [env('SEMAPHORE_DSN')]);
        };

.. _reference-semaphore-resources-name:

name
""""

**type**: ``prototype``

Name of the semaphore you want to create.
