.. index::
    single: DependencyInjection
    single: Components; DependencyInjection

The DependencyInjection Component
=================================

    The DependencyInjection component allows you to standardize and centralize
    the way objects are constructed in your application.

For an introduction to Dependency Injection and service containers see
:doc:`/book/service_container`.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/dependency-injection`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/DependencyInjection).

Basic Usage
-----------

You might have a simple class like the following ``Mailer`` that
you want to make available as a service::

    class Mailer
    {
        private $transport;

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
        private $transport;

        public function __construct($transport)
        {
            $this->transport = $transport;
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

Which mail transport you have chosen may be something other services need to
know about. You can avoid having to change it in multiple places by making
it a parameter in the container and then referring to this parameter for the
``Mailer`` service's constructor argument::

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
        private $mailer;

        public function __construct(\Mailer $mailer)
        {
            $this->mailer = $mailer;
        }

        // ...
    }

Then you can register this as a service as well and pass the ``mailer`` service into it::

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
        private $mailer;

        public function setMailer(\Mailer $mailer)
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
        ->addMethodCall('setMailer', array(new Reference('mailer')));

You could then get your ``newsletter_manager`` service from the container
like this::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();

    // ...

    $newsletterManager = $container->get('newsletter_manager');

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
for the services rather than using PHP to define the services as in the above
examples. In anything but the smallest applications it makes sense to organize
the service definitions by moving them into one or more configuration files.
To do this you also need to install
:doc:`the Config component </components/config/introduction>`.

Loading an XML config file::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

    $container = new ContainerBuilder();
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.xml');

Loading a YAML config file::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

    $container = new ContainerBuilder();
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.yml');

.. note::

    If you want to load YAML config files then you will also need to install
    :doc:`The YAML component </components/yaml/introduction>`.

If you *do* want to use PHP to create the services then you can move this
into a separate config file and load it in a similar way::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\Config\FileLocator;
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
                arguments: ["%mailer.transport%"]
            newsletter_manager:
                class:     NewsletterManager
                calls:
                    - [setMailer, ["@mailer"]]

    .. code-block:: xml

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
                     <argument type="service" id="mailer" />
                </call>
            </service>
        </services>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('mailer.transport', 'sendmail');
        $container
            ->register('mailer', 'Mailer')
            ->addArgument('%mailer.transport%');

        $container
            ->register('newsletter_manager', 'NewsletterManager')
            ->addMethodCall('setMailer', array(new Reference('mailer')));

.. _Packagist: https://packagist.org/packages/symfony/dependency-injection
