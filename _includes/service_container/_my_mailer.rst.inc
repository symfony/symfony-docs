.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.mailer:
                class:        App\Mailer
                arguments:    [sendmail]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer" class="App\Mailer">
                    <argument>sendmail</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Mailer;

        $container->register('app.mailer', Mailer::class)
            ->addArgument('sendmail');
