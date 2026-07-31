.. _reference-lock:

lock
~~~~

**type**: ``string`` | ``array``

The default lock adapter. If not defined, the value is set to ``semaphore`` when
available, or to ``flock`` otherwise. Store DSNs are also allowed.

.. _reference-lock-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true``

Whether to enable the support for lock or not. This setting is
automatically set to ``true`` when one of the child settings is configured.

.. _reference-lock-resources:

resources
.........

**type**: ``array``

A map of lock stores to be created by the framework extension, with
the name as key and DSN or service id as value:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/lock.yaml
        framework:
            lock: '%env(LOCK_DSN)%'

    .. code-block:: xml

        <!-- config/packages/lock.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:lock>
                    <framework:resource name="default">%env(LOCK_DSN)%</framework:resource>
                </framework:lock>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/lock.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->lock()
                ->resource('default', [env('LOCK_DSN')]);
        };

.. seealso::

    For more details, see :doc:`/lock`.

.. _reference-lock-resources-name:

name
""""

**type**: ``prototype``

Name of the lock you want to create.
