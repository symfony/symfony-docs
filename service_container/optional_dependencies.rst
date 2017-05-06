How to Make Service Arguments/References Optional
=================================================

Sometimes, one of your services may have an optional dependency, meaning
that the dependency is not required for your service to work properly. In
the example above, the ``app.mailer`` service *must* exist, otherwise an exception
will be thrown. By modifying the ``app.newsletter_manager`` service definition,
you can make this reference optional, there are two strategies for doing this.

Setting Missing Dependencies to null
------------------------------------

You can use the ``null`` strategy to explicitly set the argument to ``null``
if the service does not exist:

.. configuration-block::

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer">
                <!-- ... -->
                </service>

                <service id="app.newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <argument type="service" id="app.mailer" on-invalid="null" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Newsletter\NewsletterManager;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        $container->register('app.mailer', ...);

        $container->register('app.newsletter_manager', NewsletterManager::class)
            ->addArgument(new Reference(
                'app.mailer',
                ContainerInterface::NULL_ON_INVALID_REFERENCE
            ));

.. note::

    The "null" strategy is not currently supported by the YAML driver.

Ignoring Missing Dependencies
-----------------------------

The behavior of ignoring missing dependencies is the same as the "null" behavior
except when used within a method call, in which case the method call itself
will be removed.

In the following example the container will inject a service using a method
call if the service exists and remove the method call if it does not:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.newsletter_manager:
                class: AppBundle\Newsletter\NewsletterManager
                calls:
                    - [setMailer, ['@?app.mailer']]

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer">
                <!-- ... -->
                </service>

                <service id="app.newsletter_manager" class="AppBundle\Newsletter\NewsletterManager">
                    <call method="setMailer">
                        <argument type="service" id="my_mailer" on-invalid="ignore"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Newsletter\NewsletterManager;
        use Symfony\Component\DependencyInjection\Reference;
        use Symfony\Component\DependencyInjection\ContainerInterface;

        $container->register('app.mailer', ...);

        $container
            ->register('app.newsletter_manager', NewsletterManager::class)
            ->addMethodCall('setMailer', array(
                new Reference(
                    'my_mailer',
                    ContainerInterface::IGNORE_ON_INVALID_REFERENCE
                ),
            ))
        ;

In YAML, the special ``@?`` syntax tells the service container that the dependency
is optional. Of course, the ``NewsletterManager`` must also be rewritten by
adding a ``setMailer()`` method::

        public function setMailer(Mailer $mailer)
        {
            // ...
        }
