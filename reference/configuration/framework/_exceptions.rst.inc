.. _framework_exceptions:

exceptions
~~~~~~~~~~

**type**: ``array``

Defines the :ref:`log level </logging>`, :doc:`log channel </logging/channels_handlers>`
and HTTP status code applied to the exceptions that match the given exception class:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Symfony\Component\HttpKernel\Exception\BadRequestHttpException:
                    log_level: 'debug'
                    status_code: 422
                    log_channel: 'custom_channel'

    .. code-block:: xml

        <!-- config/packages/exceptions.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:exception
                    class="Symfony\Component\HttpKernel\Exception\BadRequestHttpException"
                    log-level="debug"
                    status-code="422"
                    log-channel="custom_channel"
                />
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/exceptions.php
        use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->exception(BadRequestHttpException::class)
                ->logLevel('debug')
                ->statusCode(422)
                ->logChannel('custom_channel')
            ;
        };

.. versionadded:: 7.3

    The ``log_channel`` option was introduced in Symfony 7.3.

The order in which you configure exceptions is important because Symfony will
use the configuration of the first exception that matches ``instanceof``:

.. code-block:: yaml

        # config/packages/exceptions.yaml
        framework:
            exceptions:
                Exception:
                    log_level: 'debug'
                    status_code: 404
                # The following configuration will never be used because \RuntimeException extends \Exception
                RuntimeException:
                    log_level: 'debug'
                    status_code: 422

You can map a status code and a set of headers to an exception thanks
to the ``#[WithHttpStatus]`` attribute on the exception class::

    namespace App\Exception;

    use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

    #[WithHttpStatus(422, [
       'Retry-After' => 10,
       'X-Custom-Header' => 'header-value',
    ])]
    class CustomException extends \Exception
    {
    }

It is also possible to map a log level on a custom exception class using
the ``#[WithLogLevel]`` attribute::

    namespace App\Exception;

    use Psr\Log\LogLevel;
    use Symfony\Component\HttpKernel\Attribute\WithLogLevel;

    #[WithLogLevel(LogLevel::WARNING)]
    class CustomException extends \Exception
    {
    }

The attributes can also be added to interfaces directly::

    namespace App\Exception;

    use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

    #[WithHttpStatus(422)]
    interface CustomExceptionInterface
    {
    }

    class CustomException extends \Exception implements CustomExceptionInterface
    {
    }

.. versionadded:: 7.1

    Support to use ``#[WithHttpStatus]`` and ``#[WithLogLevel]`` attributes
    on interfaces was introduced in Symfony 7.1.
