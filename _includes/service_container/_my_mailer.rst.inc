.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.mailer:
                class:        AppBundle\Mailer
                arguments:    [sendmail]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer" class="AppBundle\Mailer">
                    <argument>sendmail</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Mailer;

        $container->register('app.mailer', Mailer::class)
            ->addArgument('sendmail');
