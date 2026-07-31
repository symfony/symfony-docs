php_errors
~~~~~~~~~~

log
...

**type**: ``boolean``, ``int`` or ``array<int, string>`` **default**: ``true``

Use the application logger instead of the PHP logger for logging PHP errors.
When an integer value is used, it defines a bitmask of PHP errors that will
be logged. Those integer values must be the same used in the
`error_reporting PHP option`_. The default log levels will be used for each
PHP error.
When a boolean value is used, ``true`` enables logging for all PHP errors
while ``false`` disables logging entirely.

This option also accepts a map of PHP errors to log levels:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            php_errors:
                log:
                    !php/const \E_DEPRECATED: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_DEPRECATED: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_NOTICE: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_NOTICE: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_STRICT: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_COMPILE_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_CORE_WARNING: !php/const Psr\Log\LogLevel::ERROR
                    !php/const \E_USER_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_RECOVERABLE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_COMPILE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_PARSE: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_ERROR: !php/const Psr\Log\LogLevel::CRITICAL
                    !php/const \E_CORE_ERROR: !php/const Psr\Log\LogLevel::CRITICAL

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <!-- in XML configuration you cannot use PHP constants as the value of
                     the 'type' attribute, which makes this format way less readable.
                     Consider using YAML or PHP for this configuration -->
                <framework:log type="8" logLevel="error"/>
                <framework:log type="2" logLevel="error"/>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Psr\Log\LogLevel;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->phpErrors()->log(\E_DEPRECATED, LogLevel::ERROR);
            $framework->phpErrors()->log(\E_USER_DEPRECATED, LogLevel::ERROR);
            // ...
        };

throw
.....

**type**: ``boolean`` **default**: ``%kernel.debug%``

Throw PHP errors as ``\ErrorException`` instances. The parameter
``debug.error_handler.throw_at`` controls the threshold.

.. _`error_reporting PHP option`: https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
