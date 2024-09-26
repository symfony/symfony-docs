The DependencyInjection Component
=================================

    The DependencyInjection component implements a `PSR-11`_ compatible service
    container that allows you to standardize and centralize the way objects are
    constructed in your application.

For an introduction to Dependency Injection and service containers see
:doc:`/service_container`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/dependency-injection

.. include:: /components/require_autoload.rst.inc

Basic Usage
-----------

.. seealso::

    This article explains how to use the DependencyInjection features as an
    independent component in any PHP application. Read the :doc:`/service_container`
    article to learn about how to use it in Symfony applications.

You might have a class like the following ``Mailer`` that
you want to make available as a service::

    class Mailer
    {
        private string $transport;

        public function __construct()
        {
            $this->transport = 'sendmail';
        }

        // ...
    }

You can register this in the container as a service::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->register('mailer', 'Mailer');

An improvement to the class to make it more flexible would be to allow
the container to set the ``transport`` used. If you change the class
so this is passed into the constructor::

    class Mailer
    {
        public function __construct(
            private string $transport,
        ) {
        }

        // ...
    }

Then you can set the choice of transport in the container::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container
        ->register('mailer', 'Mailer')
        ->addArgument('sendmail');

This class is now much more flexible as you have separated the choice of
transport out of the implementation and into the container.

Which mail transport you have chosen may be something other services need
to know about. You can avoid having to change it in multiple places by making
it a parameter in the container and then referring to this parameter for
the ``Mailer`` service's constructor argument::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->setParameter('mailer.transport', 'sendmail');
    $container
        ->register('mailer', 'Mailer')
        ->addArgument('%mailer.transport%');

Now that the ``mailer`` service is in the container you can inject it as
a dependency of other classes. If you have a ``NewsletterManager`` class
like this::

    class NewsletterManager
    {
        public function __construct(
            private \Mailer $mailer,
        ) {
        }

        // ...
    }

When defining the ``newsletter_manager`` service, the ``mailer`` service does
not exist yet. Use the ``Reference`` class to tell the container to inject the
``mailer`` service when it initializes the newsletter manager::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    $container = new ContainerBuilder();

    $container->setParameter('mailer.transport', 'sendmail');
    $container
        ->register('mailer', 'Mailer')
        ->addArgument('%mailer.transport%');

    $container
        ->register('newsletter_manager', 'NewsletterManager')
        ->addArgument(new Reference('mailer'));

If the ``NewsletterManager`` did not require the ``Mailer`` and injecting
it was only optional then you could use setter injection instead::

    class NewsletterManager
    {
        private \Mailer $mailer;

        public function setMailer(\Mailer $mailer): void
        {
            $this->mailer = $mailer;
        }

        // ...
    }

You can now choose not to inject a ``Mailer`` into the ``NewsletterManager``.
If you do want to though then the container can call the setter method::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    $container = new ContainerBuilder();

    $container->setParameter('mailer.transport', 'sendmail');
    $container
        ->register('mailer', 'Mailer')
        ->addArgument('%mailer.transport%');

    $container
        ->register('newsletter_manager', 'NewsletterManager')
        ->addMethodCall('setMailer', [new Reference('mailer')]);

You could then get your ``newsletter_manager`` service from the container
like this::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();

    // ...

    $newsletterManager = $container->get('newsletter_manager');

Getting Services That Don't Exist
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, when you try to get a service that doesn't exist, you see an exception.
You can override this behavior as follows::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    $containerBuilder = new ContainerBuilder();

    // ...

    // the second argument is optional and defines what to do when the service doesn't exist
    $newsletterManager = $containerBuilder->get('newsletter_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);

These are all the possible behaviors:

 * ``ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE``: throws an exception
   at compile time (this is the **default** behavior);
 * ``ContainerInterface::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE``: throws an
   exception at runtime, when trying to access the missing service;
 * ``ContainerInterface::NULL_ON_INVALID_REFERENCE``: returns ``null``;
 * ``ContainerInterface::IGNORE_ON_INVALID_REFERENCE``: ignores the wrapping
   command asking for the reference (for instance, ignore a setter if the service
   does not exist);
 * ``ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE``: ignores/returns
   ``null`` for uninitialized services or invalid references.

Avoiding your Code Becoming Dependent on the Container
------------------------------------------------------

Whilst you can retrieve services from the container directly it is best
to minimize this. For example, in the ``NewsletterManager`` you injected
the ``mailer`` service in rather than asking for it from the container.
You could have injected the container in and retrieved the ``mailer`` service
from it but it would then be tied to this particular container making it
difficult to reuse the class elsewhere.

You will need to get a service from the container at some point but this
should be as few times as possible at the entry point to your application.

.. _components-dependency-injection-loading-config:

Setting up the Container with Configuration Files
-------------------------------------------------

As well as setting up the services using PHP as above you can also use
configuration files. This allows you to use XML or YAML to write the definitions
for the services rather than using PHP to define the services as in the
above examples. In anything but the smallest applications it makes sense
to organize the service definitions by moving them into one or more configuration
files. To do this you also need to install
:doc:`the Config component </components/config>`.

Loading an XML config file::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

    $container = new ContainerBuilder();
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.xml');

Loading a YAML config file::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

    $container = new ContainerBuilder();
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.yaml');

.. note::

    If you want to load YAML config files then you will also need to install
    :doc:`the Yaml component </components/yaml>`.

.. tip::

    If your application uses unconventional file extensions (for example, your
    XML files have a ``.config`` extension) you can pass the file type as the
    second optional parameter of the ``load()`` method::

        // ...
        $loader->load('services.config', 'xml');

If you *do* want to use PHP to create the services then you can move this
into a separate config file and load it in a similar way::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

    $container = new ContainerBuilder();
    $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.php');

You can now set up the ``newsletter_manager`` and ``mailer`` services using
config files:

.. configuration-block::

    .. code-block:: yaml

        parameters:
            # ...
            mailer.transport: sendmail

        services:
            mailer:
                class:     Mailer
                arguments: ['%mailer.transport%']
            newsletter_manager:
                class:     NewsletterManager
                calls:
                    - [setMailer, ['@mailer']]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <parameters>
                <!-- ... -->
                <parameter key="mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="mailer" class="Mailer">
                    <argument>%mailer.transport%</argument>
                </service>

                <service id="newsletter_manager" class="NewsletterManager">
                    <call method="setMailer">
                        <argument type="service" id="mailer"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container): void {
            $container->parameters()
                // ...
                ->set('mailer.transport', 'sendmail')
            ;

            $services = $container->services();
            $services->set('mailer', 'Mailer')
                ->args(['%mailer.transport%'])
            ;

            $services->set('mailer', 'Mailer')
                ->args([param('mailer.transport')])
            ;

            $services->set('newsletter_manager', 'NewsletterManager')
                ->call('setMailer', [service('mailer')])
            ;
        };

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /components/dependency_injection/*
    /service_container/*

.. _`PSR-11`: https://www.php-fig.org/psr/psr-11/
