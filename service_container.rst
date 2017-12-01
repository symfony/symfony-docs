.. index::
   single: Service Container
   single: DependencyInjection; Container

Service Container
=================

Your application is *full* of useful objects: a "Mailer" object might help you
send emails while another object might help you save things to the database.
Almost *everything* that your app "does" is actually done by one of these objects.
And each time you install a new bundle, you get access to even more!

In Symfony, these useful objects are called **services** and each service lives
inside a very special object called the **service container**. The container
allows you to centralize the way objects are constructed. It makes your life
easier, promotes a strong architecture and is super fast!

Fetching and using Services
---------------------------

The moment you start a Symfony app, your container *already* contains many services.
These are like *tools*: waiting for you to take advantage of them. In your controller,
you can "ask" for a service from the container by type-hinting an argument with the
service's class or interface name. Want to :doc:`log </logging>` something? No problem::

    // src/Controller/ProductController.php
    // ...

    use Psr\Log\LoggerInterface;

    /**
     * @Route("/products")
     */
    public function list(LoggerInterface $logger)
    {
        $logger->info('Look! I just used a service');

        // ...
    }

What other services are available? Find out by running:

.. code-block:: terminal

     $ php bin/console debug:autowiring

=============================================================== =====================================
Class/Interface Type                                            Alias Service ID
=============================================================== =====================================
``Psr\Cache\CacheItemPoolInterface``                            alias for "cache.app.recorder"
``Psr\Log\LoggerInterface``                                     alias for "monolog.logger"
``Symfony\Component\EventDispatcher\EventDispatcherInterface``  alias for "debug.event_dispatcher"
``Symfony\Component\HttpFoundation\RequestStack``               alias for "request_stack"
``Symfony\Component\HttpFoundation\Session\SessionInterface``   alias for "session"
``Symfony\Component\Routing\RouterInterface``                   alias for "router.default"
=============================================================== =====================================

When you use these type-hints in your controller methods or inside your
:ref:`own services <service-container-creating-service>`, Symfony will automatically
pass you the service object matching that type.

Throughout the docs, you'll see how to use the many different services that live
in the container.

.. tip::

    There are actually *many* more services in the container, and each service has
    a unique id in the container, like ``session`` or ``router.default``. For a full
    list, you can run ``php bin/console debug:container``. But most of the time,
    you won't need to worry about this. See :ref:`services-wire-specific-service`.
    See :doc:`/service_container/debug`.

.. index::
   single: Service Container; Configuring services

.. _service-container-creating-service:

Creating/Configuring Services in the Container
----------------------------------------------

You can also organize your *own* code into services. For example, suppose you need
to show your users a random, happy message. If you put this code in your controller,
it can't be re-used. Instead, you decide to create a new class::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    class MessageGenerator
    {
        public function getHappyMessage()
        {
            $messages = [
                'You did it! You updated the system! Amazing!',
                'That was one of the coolest updates I\'ve seen all day!',
                'Great work! Keep going!',
            ];

            $index = array_rand($messages);

            return $messages[$index];
        }
    }

Congratulations! You've just created your first service class! You can use it immediately
inside your controller::

    use App\Service\MessageGenerator;

    public function new(MessageGenerator $messageGenerator)
    {
        // thanks to the type-hint, the container will instantiate a
        // new MessageGenerator and pass it to you!
        // ...

        $message = $messageGenerator->getHappyMessage();
        $this->addFlash('success', $message);
        // ...
    }

When you ask for the ``MessageGenerator`` service, the container constructs a new
``MessageGenerator`` object and returns it (see sidebar below). But if you never ask
for the service, it's *never* constructed: saving memory and speed. As a bonus, the
``MessageGenerator`` service is only created *once*: the same instance is returned
each time you ask for it.

.. _service-container-services-load-example:

.. sidebar:: Automatic Service Loading in services.yaml

    The documentation assumes you're using the following service configuration,
    which is the default config for a new project:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                # default configuration for services in *this* file
                _defaults:
                    autowire: true      # Automatically injects dependencies in your services.
                    autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.
                    public: false       # Allows optimizing the container by removing unused services; this also means
                                        # fetching services directly from the container via $container->get() won't work.
                                        # The best practice is to be explicit about your dependencies anyway.

                # makes classes in src/ available to be used as services
                # this creates a service per class whose id is the fully-qualified class name
                App\:
                    resource: '../src/*'
                    exclude: '../src/{Entity,Migrations,Tests}'

                # ...

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- Default configuration for services in *this* file -->
                    <defaults autowire="true" autoconfigure="true" public="false" />

                    <prototype namespace="App\" resource="../src/*" exclude="../src/{Entity,Migrations,Tests}" />
                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use Symfony\Component\DependencyInjection\Definition;

            // To use as default template
            $definition = new Definition();

            $definition
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(false)
            ;

            // $this is a reference to the current loader
            $this->registerClasses($definition, 'App\\', '../src/*', '../src/{Entity,Migrations,Tests}');

    .. tip::

        The value of the ``resource`` and ``exclude`` options can be any valid
        `glob pattern`_.

    Thanks to this configuration, you can automatically use any classes from the
    ``src/`` directory as a service, without needing to manually configure
    it. Later, you'll learn more about this in :ref:`service-psr4-loader`.

    If you'd prefer to manually wire your service, that's totally possible: see
    :ref:`services-explicitly-configure-wire-services`.

.. _services-constructor-injection:

Injecting Services/Config into a Service
----------------------------------------

What if you need to access the ``logger`` service from within ``MessageGenerator``?
No problem! Create a ``__construct()`` method with a ``$logger`` argument that has
the ``LoggerInterface`` type-hint. Set this on a new ``$logger`` property
and use it later::

    // src/Service/MessageGenerator.php
    // ...

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function getHappyMessage()
        {
            $this->logger->info('About to find a happy message!');
            // ...
        }
    }

That's it! The container will *automatically* know to pass the ``logger`` service
when instantiating the ``MessageGenerator``. How does it know to do this?
:ref:`Autowiring <services-autowire>`. The key is the ``LoggerInterface``
type-hint in your ``__construct()`` method and the ``autowire: true`` config in
``services.yaml``. When you type-hint an argument, the container will automatically
find the matching service. If it can't, you'll see a clear exception with a helpful
suggestion.

By the way, this method of adding dependencies to your ``__construct()`` method is
called *dependency injection*. It's a scary term for a simple concept.

.. _services-debug-container-types:

How should you know to use ``LoggerInterface`` for the type-hint? You can either
read the docs for whatever feature you're using, or get a list of autowireable
type-hints by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

This command is your best friend.  This is a small subset of the output:

=============================================================== =====================================
Class/Interface Type                                            Alias Service ID
=============================================================== =====================================
``Psr\Cache\CacheItemPoolInterface``                            alias for "cache.app.recorder"
``Psr\Log\LoggerInterface``                                     alias for "monolog.logger"
``Symfony\Component\EventDispatcher\EventDispatcherInterface``  alias for "debug.event_dispatcher"
``Symfony\Component\HttpFoundation\RequestStack``               alias for "request_stack"
``Symfony\Component\HttpFoundation\Session\SessionInterface``   alias for "session"
``Symfony\Component\Routing\RouterInterface``                   alias for "router.default"
=============================================================== =====================================

Handling Multiple Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you also want to email a site administrator each time a site update is
made. To do that, you create a new class::

    // src/Updates/SiteUpdateManager.php
    namespace App\Updates;

    use App\Service\MessageGenerator;

    class SiteUpdateManager
    {
        private $messageGenerator;
        private $mailer;

        public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer)
        {
            $this->messageGenerator = $messageGenerator;
            $this->mailer = $mailer;
        }

        public function notifyOfSiteUpdate()
        {
            $happyMessage = $this->messageGenerator->getHappyMessage();

            $message = \Swift_Message::newInstance()
                ->setSubject('Site update just happened!')
                ->setFrom('admin@example.com')
                ->setTo('manager@example.com')
                ->addPart(
                    'Someone just updated the site. We told them: '.$happyMessage
                );
            $this->mailer->send($message);

            return $happyMessage;
        }
    }

This needs the ``MessageGenerator`` *and* the ``Swift_Mailer`` service. That's no
problem! In fact, this new service is ready to be used. In a controller, for example,
you can type-hint the new ``SiteUpdateManager`` class and use it::

    // src/Controller/SiteController.php

    // ...
    use App\Updates\SiteUpdateManager;

    public function new(SiteUpdateManager $siteUpdateManager)
    {
        // ...

        $message = $siteUpdateManager->notifyOfSiteUpdate();
        $this->addFlash('success', $message);
        // ...
    }

Thanks to autowiring and your type-hints in ``__construct()``, the container creates
the ``SiteUpdateManager`` object and passes it the correct argument. In most cases,
this works perfectly.

.. _services-manually-wire-args:

Manually Wiring Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~

But there are a few cases when an argument to a service cannot be autowired. For
example, suppose you want to make the admin email configurable:

.. code-block:: diff

    // src/Updates/SiteUpdateManager.php
    // ...

    class SiteUpdateManager
    {
        // ...
    +    private $adminEmail;

    -    public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer)
    +    public function __construct(MessageGenerator $messageGenerator, \Swift_Mailer $mailer, $adminEmail)
        {
            // ...
    +        $this->adminEmail = $adminEmail;
        }

        public function notifyOfSiteUpdate()
        {
            // ...

            $message = \Swift_Message::newInstance()
                // ...
    -            ->setTo('manager@example.com')
    +            ->setTo($this->adminEmail)
                // ...
            ;
            // ...
        }
    }

If you make this change and refresh, you'll see an error:

    Cannot autowire service "App\Updates\SiteUpdateManager": argument "$adminEmail"
    of method "__construct()" must have a type-hint or be given a value explicitly.

That makes sense! There is no way that the container knows what value you want to
pass here. No problem! In your configuration, you can explicitly set this argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # same as before
            App\:
                resource: '../src/*'
                exclude: '../src/{Entity,Migrations,Tests}'

            # explicitly configure the service
            App\Updates\SiteUpdateManager:
                arguments:
                    $adminEmail: 'manager@example.com'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- Same as before -->
                <prototype namespace="App\" resource="../src/*" exclude="../src/{Entity,Migrations,Tests}" />

                <!-- Explicitly configure the service -->
                <service id="App\Updates\SiteUpdateManager">
                    <argument key="$adminEmail">manager@example.com</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Updates\SiteUpdateManager;
        use Symfony\Component\DependencyInjection\Definition;

        // Same as before
        $definition = new Definition();

        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;

        $this->registerClasses($definition, 'App\\', '../src/*', '../src/{Entity,Migrations,Tests}');

        // Explicitly configure the service
        $container->getDefinition(SiteUpdateManager::class)
            ->setArgument('$adminEmail', 'manager@example.com');

Thanks to this, the container will pass ``manager@example.com`` to the ``$adminEmail``
argument of ``__construct`` when creating the ``SiteUpdateManager`` service. The
other arguments will still be autowired.

But, isn't this fragile? Fortunately, no! If you rename the ``$adminEmail`` argument
to something else - e.g. ``$mainEmail`` - you will get a clear exception when you
reload the next page (even if that page doesn't use this service).

.. _service-container-parameters:

Service Parameters
------------------

In addition to holding service objects, the container also holds configuration,
called ``parameters``. To create a parameter, add it under the ``parameters`` key
and reference it with the ``%parameter_name%`` syntax:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            admin_email: manager@example.com

        services:
            # ...

            App\Updates\SiteUpdateManager:
                arguments:
                    $adminEmail: '%admin_email%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="admin_email">manager@example.com</parameter>
            </parameters>

            <services>
                <!-- ... -->

                <service id="App\Updates\SiteUpdateManager">
                    <argument key="$adminEmail">%admin_email%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Updates\SiteUpdateManager;
        $container->setParameter('admin_email', 'manager@example.com');

        $container->autowire(SiteUpdateManager::class)
            // ...
            ->setArgument('$adminEmail', '%admin_email%');

Actually, once you define a parameter, it can be referenced via the
``%parameter_name%`` syntax in *any* other configuration file. Many parameters
are defined in the ``config/services.yaml`` file.

You can then fetch the parameter in the service::

    class SiteUpdateManager
    {
        // ...

        private $adminEmail;

        public function __construct($adminEmail)
        {
            $this->adminEmail = $adminEmail;
        }
    }

You can also fetch parameters directly from the container::

    public function new()
    {
        // ...

        // this ONLY works if you extend the base Controller
        $adminEmail = $this->container->getParameter('admin_email');

        // or a shorter way!
        // $adminEmail = $this->getParameter('admin_email');
    }

For more info about parameters, see :doc:`/service_container/parameters`.

.. _services-wire-specific-service:

Choose a Specific Service
-------------------------

The ``MessageGenerator`` service created earlier requires a ``LoggerInterface`` argument::

    // src/Service/MessageGenerator.php
    // ...

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }
        // ...
    }

However, there are *multiple* services in the container that implement ``LoggerInterface``,
such as ``logger``, ``monolog.logger.request``, ``monolog.logger.php``, etc. How
does the container know which one to use?

In these situations, the container is usually configured to automatically choose
one of the services - ``logger`` in this case (read more about why in :ref:`service-autowiring-alias`).
But, you can control this and pass in a different logger:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same code as before

            # explicitly configure the service
            App\Service\MessageGenerator:
                arguments:
                    $logger: '@monolog.logger.request'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="App\Service\MessageGenerator">
                    <argument key="$logger" type="service" id="monolog.logger.request" />
                </service>
            </services>
        </container>


    .. code-block:: php

        // config/services.php
        use App\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->autowire(MessageGenerator::class)
            ->setAutoconfigured(true)
            ->setPublic(false)
            ->setArgument('$logger', new Reference('monolog.logger.request'));

This tells the container that the ``$logger`` argument to ``__construct`` should use
service whose id is ``monolog.logger.request``.

.. _container-debug-container:

For a full list of *all* possible services in the container, run:

.. code-block:: terminal

    php bin/console debug:container --show-private

.. tip::

    The ``@`` symbol is important: that's what tells the container you want to pass
    the *service* whose id is ``monolog.logger.request``, and not just the *string*
    ``monolog.logger.request``.

.. _services-binding:

Binding Arguments by Name or Type
---------------------------------

You can also use the ``bind`` keyword to bind specific arguments by name or type:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                bind:
                    # pass this value to any $adminEmail argument for any service
                    # that's defined in this file (including controller arguments)
                    $adminEmail: 'manager@example.com'

                    # pass this service for any LoggerInterface type-hint for any
                    # service that's defined in this file
                    Psr\Log\LoggerInterface: '@monolog.logger.request'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false">
                    <bind key="$adminEmail">manager@example.com</bind>
                    <bind key="$logger"
                        type="service"
                        id="monolog.logger.request"
                    />
                </defaults>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Controller\LuckyController;
        use Symfony\Component\DependencyInjection\Reference;
        use Psr\Log\LoggerInterface;

        $container->register(LuckyController::class)
            ->setPublic(true)
            ->setBindings(array(
                '$adminEmail' => 'manager@example.com',
                LoggerInterface::class => new Reference('monolog.logger.request'),
            ))
        ;

By putting the ``bind`` key under ``_defaults``, you can specify the value of *any*
argument for *any* service defined in this file! You can bind arguments by name
(e.g. ``$adminEmail``) or by type (e.g. ``Psr\Log\LoggerInterface``).

The ``bind`` config can be also be applied to specific services or when loading many
services at once (i.e. :ref:`service-psr4-loader`).

.. _services-autowire:

The autowire Option
-------------------

Above, the ``services.yaml`` file has ``autowire: true`` in the ``_defaults`` section
so that it applies to all services defined in that file. With this setting, you're
able to type-hint arguments in the ``__construct()`` method of your services and
the container will automatically pass you the correct arguments. This entire entry
has been written around autowiring.

For more details about autowiring, check out :doc:`/service_container/autowiring`.

.. _services-autoconfigure:

The autoconfigure Option
------------------------

Above, the ``services.yaml`` file has ``autoconfigure: true`` in the ``_defaults``
section so that it applies to all services defined in that file. With this setting,
the container will automatically apply certain configuration to your services, based
on your service's *class*. This is mostly used to *auto-tag* your services.

For example, to create a Twig extension, you need to create a class, register it
as a service, and :doc:`tag </service_container/tags>` it with ``twig.extension``.

But, with ``autoconfigure: true``, you don't need the tag. In fact, if you're using
the :ref:`default services.yaml config <service-container-services-load-example>`,
you don't need to do *anything*: the service will be automatically loaded. Then,
``autoconfigure`` will add the ``twig.extension`` tag *for* you, because your class
implements ``Twig_ExtensionInterface``. And thanks to ``autowire``, you can even add
constructor arguments without any configuration.

.. _container-public:

Public Versus Private Services
------------------------------

Thanks to the ``_defaults`` section in ``services.yaml``, every service defined in
this file is ``public: false`` by default.

What does this mean? When a service **is** public, you can access it directly
from the container object, which is accessible from any controller that extends
``Controller``::

    use App\Service\MessageGenerator;

    // ...
    public function new()
    {
        // there IS a public "logger" service in the container
        $logger = $this->container->get('logger');

        // this will NOT work: MessageGenerator is a private service
        $generator = $this->container->get(MessageGenerator::class);
    }

As a best practice, you should only create *private* services, which will happen
automatically. And also, you should *not* use the ``$container->get()`` method to
fetch public services.

But, if you *do* need to make a service public, just override the ``public`` setting:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same code as before

            # explicitly configure the service
            App\Service\MessageGenerator:
                public: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="App\Service\MessageGenerator" public="true"></service>
            </services>
        </container>

.. _service-psr4-loader:

Importing Many Services at once with resource
---------------------------------------------

You've already seen that you can import many services at once by using the ``resource``
key. For example, the default Symfony configuration contains this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # makes classes in src/ available to be used as services
            # this creates a service per class whose id is the fully-qualified class name
            App\:
                resource: '../src/*'
                exclude: '../src/{Entity,Migrations,Tests}'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <prototype namespace="App\" resource="../src/*" exclude="../src/{Entity,Migrations,Tests}" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        // To use as default template
        $definition = new Definition();

        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;

        $this->registerClasses($definition, 'App\\', '../src/*', '../src/{Entity,Migrations,Tests}');

.. tip::

    The value of the ``resource`` and ``exclude`` options can be any valid
    `glob pattern`_.

This can be used to quickly make many classes available as services and apply some
default configuration. The ``id`` of each service is its fully-qualified class name.
You can override any service that's imported by using its id (class name) below
(e.g. see :ref:`services-manually-wire-args`). If you override a service, none of
the options (e.g. ``public``) are inherited from the import (but the overridden
service *does* still inherit from ``_defaults``).

You can also ``exclude`` certain paths. This is optional, but will slightly increase
performance in the ``dev`` environment: excluded paths are not tracked and so modifying
them will not cause the container to be rebuilt.

.. note::

    Wait, does this mean that *every* class in ``src/`` is registered as
    a service? Even model classes? Actually, no. As long as you have
    ``public: false`` under your ``_defaults`` key (or you can add it under the
    specific import), all the imported services are *private*. Thanks to this, all
    classes in ``src/`` that are *not* explicitly used as services are
    automatically removed from the final container. In reality, the import
    means that all classes are "available to be *used* as services" without needing
    to be manually configured.

.. _services-explicitly-configure-wire-services:

Explicitly Configuring Services and Arguments
---------------------------------------------

Prior to Symfony 3.3, all services and (typically) arguments were explicitly configured:
it was not possible to :ref:`load services automatically <service-container-services-load-example>`
and :ref:`autowiring <services-autowire>` was much less common.

Both of these features are optional. And even if you use them, there may be some
cases where you want to manually wire a service. For example, suppose that you want
to register *2* services for the ``SiteUpdateManager`` class - each with a different
admin email. In this case, each needs to have a unique service id:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # this is the service's id
            site_update_manager.superadmin:
                class: App\Updates\SiteUpdateManager
                # you CAN still use autowiring: we just want to show what it looks like without
                autowire: false
                # manually wire all arguments
                arguments:
                    - '@App\Service\MessageGenerator'
                    - '@mailer'
                    - 'superadmin@example.com'

            site_update_manager.normal_users:
                class: App\Updates\SiteUpdateManager
                autowire: false
                arguments:
                    - '@App\Service\MessageGenerator'
                    - '@mailer'
                    - 'contact@example.com'

            # Create an alias, so that - by default - if you type-hint SiteUpdateManager,
            # the site_update_manager.superadmin will be used
            App\Updates\SiteUpdateManager: '@site_update_manager.superadmin'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="site_update_manager.superadmin" class="App\Updates\SiteUpdateManager" autowire="false">
                    <argument type="service" id="App\Service\MessageGenerator" />
                    <argument type="service" id="mailer" />
                    <argument>superadmin@example.com</argument>
                </service>

                <service id="site_update_manager.normal_users" class="App\Updates\SiteUpdateManager" autowire="false">
                    <argument type="service" id="App\Service\MessageGenerator" />
                    <argument type="service" id="mailer" />
                    <argument>contact@example.com</argument>
                </service>

                <alias id="App\Updates\SiteUpdateManager" service="site_update_manager.superadmin" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Updates\SiteUpdateManager;
        use App\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('site_update_manager.superadmin', SiteUpdateManager::class)
            ->setAutowired(false)
            ->setArguments(array(
                new Reference(MessageGenerator::class),
                new Reference('mailer'),
                'superadmin@example.com'
            ));

        $container->register('site_update_manager.normal_users', SiteUpdateManager::class)
            ->setAutowired(false)
            ->setArguments(array(
                new Reference(MessageGenerator::class),
                new Reference('mailer'),
                'contact@example.com'
            ));

        $container->setAlias(SiteUpdateManager::class, 'site_update_manager.superadmin')

In this case, *two* services are registered: ``site_update_manager.superadmin``
and ``site_update_manager.normal_users``. Thanks to the alias, if you type-hint
``SiteUpdateManager`` the first (``site_update_manager.superadmin``) will be passed.
If you want to pass the second, you'll need to :ref:`manually wire the service <services-wire-specific-service>`.

.. caution::

    If you do *not* create the alias and are :ref:`loading all services from src/ <service-container-services-load-example>`,
    then *three* services have been created (the automatic service + your two services)
    and the automatically loaded service will be passed - by default - when you type-hint
    ``SiteUpdateManager``. That's why creating the alias is a good idea.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

.. _`service-oriented architecture`: https://en.wikipedia.org/wiki/Service-oriented_architecture
.. _`Symfony Standard Edition (version 3.3) services.yaml`: https://github.com/symfony/symfony-standard/blob/3.3/app/config/services.yml
.. _`glob pattern`: https://en.wikipedia.org/wiki/Glob_(programming)
