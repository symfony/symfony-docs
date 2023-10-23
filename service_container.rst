Service Container
=================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Symfony Fundamentals screencast series`_.

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
    namespace App\Controller;

    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class ProductController extends AbstractController
    {
        #[Route('/products')]
        public function list(LoggerInterface $logger): Response
        {
            $logger->info('Look, I just used a service!');

            // ...
        }
    }


What other services are available? Find out by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

      # this is just a *small* sample of the output...

      Autowirable Types
      =================

       The following classes & interfaces can be used as type-hints when autowiring:

       Describes a logger instance.
       Psr\Log\LoggerInterface - alias:logger

       Request stack that controls the lifecycle of requests.
       Symfony\Component\HttpFoundation\RequestStack - alias:request_stack

       RouterInterface is the interface that all Router classes must implement.
       Symfony\Component\Routing\RouterInterface - alias:router.default

       [...]

When you use these type-hints in your controller methods or inside your
:ref:`own services <service-container-creating-service>`, Symfony will automatically
pass you the service object matching that type.

Throughout the docs, you'll see how to use the many different services that live
in the container.

.. tip::

    There are actually *many* more services in the container, and each service has
    a unique id in the container, like ``request_stack`` or ``router.default``. For a full
    list, you can run ``php bin/console debug:container``. But most of the time,
    you won't need to worry about this. See :ref:`services-wire-specific-service`.
    See :doc:`/service_container/debug`.

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
        public function getHappyMessage(): string
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

Congratulations! You've created your first service class! You can use it immediately
inside your controller::

    // src/Controller/ProductController.php
    use App\Service\MessageGenerator;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class ProductController extends AbstractController
    {
        #[Route('/products/new')]
        public function new(MessageGenerator $messageGenerator): Response
        {
            // thanks to the type-hint, the container will instantiate a
            // new MessageGenerator and pass it to you!
            // ...

            $message = $messageGenerator->getHappyMessage();
            $this->addFlash('success', $message);
            // ...
        }
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

                # makes classes in src/ available to be used as services
                # this creates a service per class whose id is the fully-qualified class name
                App\:
                    resource: '../src/'
                    exclude:
                        - '../src/DependencyInjection/'
                        - '../src/Entity/'
                        - '../src/Kernel.php'

                # order is important in this file because service definitions
                # always *replace* previous ones; add your own service configuration below

                # ...

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- Default configuration for services in *this* file -->
                    <defaults autowire="true" autoconfigure="true"/>

                    <!-- makes classes in src/ available to be used as services -->
                    <!-- this creates a service per class whose id is the fully-qualified class name -->
                    <prototype namespace="App\" resource="../src/" exclude="../src/{DependencyInjection,Entity,Kernel.php}"/>

                    <!-- order is important in this file because service definitions
                         always *replace* previous ones; add your own service configuration below -->

                    <!-- ... -->

                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return function(ContainerConfigurator $container): void {
                // default configuration for services in *this* file
                $services = $container->services()
                    ->defaults()
                        ->autowire()      // Automatically injects dependencies in your services.
                        ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
                ;

                // makes classes in src/ available to be used as services
                // this creates a service per class whose id is the fully-qualified class name
                $services->load('App\\', '../src/')
                    ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');

                // order is important in this file because service definitions
                // always *replace* previous ones; add your own service configuration below
            };

    .. tip::

        The value of the ``resource`` and ``exclude`` options can be any valid
        `glob pattern`_. The value of the ``exclude`` option can also be an
        array of glob patterns.

    Thanks to this configuration, you can automatically use any classes from the
    ``src/`` directory as a service, without needing to manually configure
    it. Later, you'll learn more about this in :ref:`service-psr4-loader`.

    If you'd prefer to manually wire your service, that's totally possible: see
    :ref:`services-explicitly-configure-wire-services`.

.. _service-container_limiting-to-env:

Limiting Services to a specific Symfony Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the ``#[When]`` attribute to only register the class
as a service in some environments::

    use Symfony\Component\DependencyInjection\Attribute\When;

    // SomeClass is only registered in the "dev" environment

    #[When(env: 'dev')]
    class SomeClass
    {
        // ...
    }

    // you can also apply more than one When attribute to the same class

    #[When(env: 'dev')]
    #[When(env: 'test')]
    class AnotherClass
    {
        // ...
    }

.. _services-constructor-injection:

Injecting Services/Config into a Service
----------------------------------------

What if you need to access the ``logger`` service from within ``MessageGenerator``?
No problem! Create a ``__construct()`` method with a ``$logger`` argument that has
the ``LoggerInterface`` type-hint. Set this on a new ``$logger`` property
and use it later::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
        }

        public function getHappyMessage(): string
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
called *dependency injection*.

.. _services-debug-container-types:

How should you know to use ``LoggerInterface`` for the type-hint? You can either
read the docs for whatever feature you're using, or get a list of autowireable
type-hints by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

      # this is just a *small* sample of the output...

      Describes a logger instance.
      Psr\Log\LoggerInterface - alias:monolog.logger

      Request stack that controls the lifecycle of requests.
      Symfony\Component\HttpFoundation\RequestStack - alias:request_stack

      RouterInterface is the interface that all Router classes must implement.
      Symfony\Component\Routing\RouterInterface - alias:router.default

      [...]

Handling Multiple Services
~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you also want to email a site administrator each time a site update is
made. To do that, you create a new class::

    // src/Service/SiteUpdateManager.php
    namespace App\Service;

    use App\Service\MessageGenerator;
    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;

    class SiteUpdateManager
    {
        public function __construct(
            private MessageGenerator $messageGenerator,
            private MailerInterface $mailer,
        ) {
        }

        public function notifyOfSiteUpdate(): bool
        {
            $happyMessage = $this->messageGenerator->getHappyMessage();

            $email = (new Email())
                ->from('admin@example.com')
                ->to('manager@example.com')
                ->subject('Site update just happened!')
                ->text('Someone just updated the site. We told them: '.$happyMessage);

            $this->mailer->send($email);

            // ...

            return true;
        }
    }

This needs the ``MessageGenerator`` *and* the ``Mailer`` service. That's no
problem, we ask them by type hinting their class and interface names!
Now, this new service is ready to be used. In a controller, for example,
you can type-hint the new ``SiteUpdateManager`` class and use it::

    // src/Controller/SiteController.php
    namespace App\Controller;

    use App\Service\SiteUpdateManager;
    // ...

    class SiteController extends AbstractController
    {
        public function new(SiteUpdateManager $siteUpdateManager): Response
        {
            // ...

            if ($siteUpdateManager->notifyOfSiteUpdate()) {
                $this->addFlash('success', 'Notification mail was sent successfully.');
            }

            // ...
        }
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

      // src/Service/SiteUpdateManager.php
      // ...

      class SiteUpdateManager
      {
          // ...
    +    private string $adminEmail;

          public function __construct(
              private MessageGenerator $messageGenerator,
              private MailerInterface $mailer,
    +        private string $adminEmail
          ) {
          }

          public function notifyOfSiteUpdate(): bool
          {
              // ...

              $email = (new Email())
                  // ...
    -            ->to('manager@example.com')
    +            ->to($this->adminEmail)
                  // ...
              ;
              // ...
          }
      }

If you make this change and refresh, you'll see an error:

    Cannot autowire service "App\\Service\\SiteUpdateManager": argument "$adminEmail"
    of method "__construct()" must have a type-hint or be given a value explicitly.

That makes sense! There is no way that the container knows what value you want to
pass here. No problem! In your configuration, you can explicitly set this argument:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same as before

            # same as before
            App\:
                resource: '../src/'
                exclude: '../src/{DependencyInjection,Entity,Kernel.php}'

            # explicitly configure the service
            App\Service\SiteUpdateManager:
                arguments:
                    $adminEmail: 'manager@example.com'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ...  same as before -->

                <!-- Same as before -->

                <prototype namespace="App\"
                    resource="../src/"
                    exclude="../src/{DependencyInjection,Entity,Kernel.php}"
                />

                <!-- Explicitly configure the service -->
                <service id="App\Service\SiteUpdateManager">
                    <argument key="$adminEmail">manager@example.com</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\SiteUpdateManager;

        return function(ContainerConfigurator $container): void {
            // ...

            // same as before
            $services->load('App\\', '../src/')
                ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');

            $services->set(SiteUpdateManager::class)
                ->arg('$adminEmail', 'manager@example.com')
            ;
        };


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
called **parameters**. The main article about Symfony configuration explains the
:ref:`configuration parameters <configuration-parameters>` in detail and shows
all their types (string, boolean, array, binary and PHP constant parameters).

However, there is another type of parameter related to services. In YAML config,
any string which starts with ``@`` is considered as the ID of a service, instead
of a regular string. In XML config, use the ``type="service"`` type for the
parameter and in PHP config use the ``service()`` function:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MessageGenerator:
                arguments:
                    # this is not a string, but a reference to a service called 'logger'
                    - '@logger'

                    # if the value of a string argument starts with '@', you need to escape
                    # it by adding another '@' so Symfony doesn't consider it a service
                    # the following example would be parsed as the string '@securepassword'
                    # - '@@securepassword'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MessageGenerator">
                    <argument type="service" id="logger"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(MessageGenerator::class)
                ->args([service('logger')])
            ;
        };

Working with container parameters is straightforward using the container's
accessor methods for parameters::

    // checks if a parameter is defined (parameter names are case-sensitive)
    $container->hasParameter('mailer.transport');

    // gets value of a parameter
    $container->getParameter('mailer.transport');

    // adds a new parameter
    $container->setParameter('mailer.transport', 'sendmail');

.. caution::

    The used ``.`` notation is a
    :ref:`Symfony convention <service-naming-conventions>` to make parameters
    easier to read. Parameters are flat key-value elements, they can't
    be organized into a nested array

.. note::

    You can only set a parameter before the container is compiled, not at run-time.
    To learn more about compiling the container see
    :doc:`/components/dependency_injection/compilation`.

.. _services-wire-specific-service:

Choose a Specific Service
-------------------------

The ``MessageGenerator`` service created earlier requires a ``LoggerInterface`` argument::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        public function __construct(
            private LoggerInterface $logger,
        ) {
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
                    # the '@' symbol is important: that's what tells the container
                    # you want to pass the *service* whose id is 'monolog.logger.request',
                    # and not just the *string* 'monolog.logger.request'
                    $logger: '@monolog.logger.request'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="App\Service\MessageGenerator">
                    <argument key="$logger" type="service" id="monolog.logger.request"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $container): void {
            // ... same code as before

            // explicitly configure the service
            $services->set(MessageGenerator::class)
                ->arg('$logger', service('monolog.logger.request'))
            ;
        };

This tells the container that the ``$logger`` argument to ``__construct`` should use
service whose id is ``monolog.logger.request``.

For a list of possible logger services that can be used with autowiring, run:

.. code-block:: terminal

    $ php bin/console debug:autowiring logger

.. _container-debug-container:

For a full list of *all* possible services in the container, run:

.. code-block:: terminal

    $ php bin/console debug:container

Remove Services
---------------

A service can be removed from the service container if needed. This is useful
for example to make a service unavailable in some :ref:`configuration environment <configuration-environments>`
(e.g. in the ``test`` environment):

.. configuration-block::

    .. code-block:: php

        // config/services_test.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\RemovedService;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->remove(RemovedService::class);
        };

Now, the container will not contain the ``App\RemovedService`` in the ``test``
environment.

.. _container_closure-as-argument:

Injecting a Closure as an Argument
----------------------------------

It is possible to inject a callable as an argument of a service.
Let's add an argument to our ``MessageGenerator`` constructor::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private string $messageHash;

        public function __construct(
            private LoggerInterface $logger,
            callable $generateMessageHash,
        ) {
            $this->messageHash = $generateMessageHash();
        }
        // ...
    }

Now, we would add a new invokable service to generate the message hash::

    // src/Hash/MessageHashGenerator.php
    namespace App\Hash;

    class MessageHashGenerator
    {
        public function __invoke(): string
        {
            // Compute and return a message hash
        }
    }

Our configuration looks like this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same code as before

            # explicitly configure the service
            App\Service\MessageGenerator:
                arguments:
                    $logger: '@monolog.logger.request'
                    $generateMessageHash: !closure '@App\Hash\MessageHashGenerator'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="App\Service\MessageGenerator">
                    <argument key="$logger" type="service" id="monolog.logger.request"/>
                    <argument key="$generateMessageHash" type="closure" id="App\Hash\MessageHashGenerator"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $containerConfigurator): void {
            // ... same code as before

            // explicitly configure the service
            $services->set(MessageGenerator::class)
                ->arg('$logger', service('monolog.logger.request'))
                ->arg('$generateMessageHash', closure('App\Hash\MessageHashGenerator'))
            ;
        };

.. seealso::

    Closures can be injected :ref:`by using autowiring <autowiring_closures>`
    and its dedicated attributes.

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

                    # pass this service to any $requestLogger argument for any
                    # service that's defined in this file
                    $requestLogger: '@monolog.logger.request'

                    # pass this service for any LoggerInterface type-hint for any
                    # service that's defined in this file
                    Psr\Log\LoggerInterface: '@monolog.logger.request'

                    # optionally you can define both the name and type of the argument to match
                    string $adminEmail: 'manager@example.com'
                    Psr\Log\LoggerInterface $requestLogger: '@monolog.logger.request'
                    iterable $rules: !tagged_iterator app.foo.rule

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true" public="false">
                    <bind key="$adminEmail">manager@example.com</bind>
                    <bind key="$requestLogger"
                        type="service"
                        id="monolog.logger.request"
                    />
                    <bind key="Psr\Log\LoggerInterface"
                        type="service"
                        id="monolog.logger.request"
                    />

                    <!-- optionally you can define both the name and type of the argument to match -->
                    <bind key="string $adminEmail">manager@example.com</bind>
                    <bind key="Psr\Log\LoggerInterface $requestLogger"
                        type="service"
                        id="monolog.logger.request"
                    />
                    <bind key="iterable $rules"
                        type="tagged_iterator"
                        tag="app.foo.rule"
                    />
                </defaults>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Psr\Log\LoggerInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    // pass this value to any $adminEmail argument for any service
                    // that's defined in this file (including controller arguments)
                    ->bind('$adminEmail', 'manager@example.com')

                    // pass this service to any $requestLogger argument for any
                    // service that's defined in this file
                    ->bind('$requestLogger', service('monolog.logger.request'))

                    // pass this service for any LoggerInterface type-hint for any
                    // service that's defined in this file
                    ->bind(LoggerInterface::class, service('monolog.logger.request'))

                    // optionally you can define both the name and type of the argument to match
                    ->bind('string $adminEmail', 'manager@example.com')
                    ->bind(LoggerInterface::class.' $requestLogger', service('monolog.logger.request'))
                    ->bind('iterable $rules', tagged_iterator('app.foo.rule'))
            ;

            // ...
        };

By putting the ``bind`` key under ``_defaults``, you can specify the value of *any*
argument for *any* service defined in this file! You can bind arguments by name
(e.g. ``$adminEmail``), by type (e.g. ``Psr\Log\LoggerInterface``) or both
(e.g. ``Psr\Log\LoggerInterface $requestLogger``).

The ``bind`` config can also be applied to specific services or when loading many
services at once (i.e. :ref:`service-psr4-loader`).

Abstract Service Arguments
--------------------------

Sometimes, the values of some service arguments can't be defined in the
configuration files because they are calculated at runtime using a
:doc:`compiler pass </service_container/compiler_passes>`
or :doc:`bundle extension </bundles/extension>`.

In those cases, you can use the ``abstract`` argument type to define at least
the name of the argument and some short description about its purpose:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Service\MyService:
                arguments:
                    $rootNamespace: !abstract 'should be defined by Pass'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MyService" class="App\Service\MyService">
                    <argument key="$rootNamespace" type="abstract">should be defined by Pass</argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MyService;
        use Psr\Log\LoggerInterface;
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        return function(ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(MyService::class)
                ->arg('$rootNamespace', abstract_arg('should be defined by Pass'))
            ;

            // ...
        };

If you don't replace the value of an abstract argument during runtime, a
``RuntimeException`` will be thrown with a message like
``Argument "$rootNamespace" of service "App\Service\MyService" is abstract: should be defined by Pass.``

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
implements ``Twig\Extension\ExtensionInterface``. And thanks to ``autowire``, you can even add
constructor arguments without any configuration.

Linting Service Definitions
---------------------------

The ``lint:container`` command checks that the arguments injected into services
match their type declarations. It's useful to run it before deploying your
application to production (e.g. in your continuous integration server):

.. code-block:: terminal

    $ php bin/console lint:container

Checking the types of all service arguments whenever the container is compiled
can hurt performance. That's why this type checking is implemented in a
:doc:`compiler pass </service_container/compiler_passes>` called
``CheckTypeDeclarationsPass`` which is disabled by default and enabled only when
executing the ``lint:container`` command. If you don't mind the performance
loss, enable the compiler pass in your application.

.. _container-public:

Public Versus Private Services
------------------------------

Every service defined is private by default. When a service is private, you
cannot access it directly from the container using ``$container->get()``. As a
best practice, you should only create *private* services and you should fetch
services using dependency injection instead of using ``$container->get()``.

If you need to fetch services lazily, instead of using public services you
should consider using a :ref:`service locator <service-locators>`.

But, if you *do* need to make a service public, override the ``public``
setting:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same code as before

            # explicitly configure the service
            App\Service\PublicService:
                public: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same code as before -->

                <!-- Explicitly configure the service -->
                <service id="App\Service\PublicService" public="true"></service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\PublicService;

        return function(ContainerConfigurator $container): void {
            // ... same as code before

            // explicitly configure the service
            $services->set(Service\PublicService::class)
                ->public()
            ;
        };

.. _service-psr4-loader:

Importing Many Services at once with resource
---------------------------------------------

You've already seen that you can import many services at once by using the ``resource``
key. For example, the default Symfony configuration contains this:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ... same as before

            # makes classes in src/ available to be used as services
            # this creates a service per class whose id is the fully-qualified class name
            App\:
                resource: '../src/'
                exclude: '../src/{DependencyInjection,Entity,Kernel.php}'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... same as before -->

                <prototype namespace="App\" resource="../src/" exclude="../src/{DependencyInjection,Entity,Kernel.php}"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            // ...

            // makes classes in src/ available to be used as services
            // this creates a service per class whose id is the fully-qualified class name
            $services->load('App\\', '../src/')
                ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
        };

.. tip::

    The value of the ``resource`` and ``exclude`` options can be any valid
    `glob pattern`_. If you want to exclude only a few services, you
    may use the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Exclude`
    attribute directly on your class to exclude it.

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
    a service? Even model classes? Actually, no. As long as you keep your imported services as :ref:`private <container-public>`, all
    classes in ``src/`` that are *not* explicitly used as services are
    automatically removed from the final container. In reality, the import
    means that all classes are "available to be *used* as services" without needing
    to be manually configured.

Multiple Service Definitions Using the Same Namespace
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you define services using the YAML config format, the PHP namespace is used
as the key of each configuration, so you can't define different service configs
for classes under the same namespace:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Domain\:
                resource: '../src/Domain/*'
                # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <prototype namespace="App\Domain"
                    resource="../src/App/Domain/*"/>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $defaults = new Definition();

        // $this is a reference to the current loader
        $this->registerClasses(
            $defaults,
            'App\\Domain\\',
            '../src/App/Domain/*'
        );

        // ...

In order to have multiple definitions, add the ``namespace`` option and use any
unique string as the key of each service config:

.. code-block:: yaml

    # config/services.yaml
    services:
        command_handlers:
            namespace: App\Domain\
            resource: '../src/Domain/*/CommandHandler'
            tags: [command_handler]

        event_subscribers:
            namespace: App\Domain\
            resource: '../src/Domain/*/EventSubscriber'
            tags: [event_subscriber]

.. _services-explicitly-configure-wire-services:

Explicitly Configuring Services and Arguments
---------------------------------------------

:ref:`Loading services automatically <service-container-services-load-example>`
and :ref:`autowiring <services-autowire>` are optional. And even if you use them, there may be some
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
                class: App\Service\SiteUpdateManager
                # you CAN still use autowiring: we just want to show what it looks like without
                autowire: false
                # manually wire all arguments
                arguments:
                    - '@App\Service\MessageGenerator'
                    - '@mailer'
                    - 'superadmin@example.com'

            site_update_manager.normal_users:
                class: App\Service\SiteUpdateManager
                autowire: false
                arguments:
                    - '@App\Service\MessageGenerator'
                    - '@mailer'
                    - 'contact@example.com'

            # Create an alias, so that - by default - if you type-hint SiteUpdateManager,
            # the site_update_manager.superadmin will be used
            App\Service\SiteUpdateManager: '@site_update_manager.superadmin'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="site_update_manager.superadmin" class="App\Service\SiteUpdateManager" autowire="false">
                    <argument type="service" id="App\Service\MessageGenerator"/>
                    <argument type="service" id="mailer"/>
                    <argument>superadmin@example.com</argument>
                </service>

                <service id="site_update_manager.normal_users" class="App\Service\SiteUpdateManager" autowire="false">
                    <argument type="service" id="App\Service\MessageGenerator"/>
                    <argument type="service" id="mailer"/>
                    <argument>contact@example.com</argument>
                </service>

                <service id="App\Service\SiteUpdateManager" alias="site_update_manager.superadmin"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;
        use App\Service\SiteUpdateManager;

        return function(ContainerConfigurator $container): void {
            // ...

            // site_update_manager.superadmin is the service's id
            $services->set('site_update_manager.superadmin', SiteUpdateManager::class)
                // you CAN still use autowiring: we just want to show what it looks like without
                ->autowire(false)
                // manually wire all arguments
                ->args([
                   service(MessageGenerator::class),
                   service('mailer'),
                   'superadmin@example.com',
                ]);

            $services->set('site_update_manager.normal_users', SiteUpdateManager::class)
                ->autowire(false)
                ->args([
                    service(MessageGenerator::class),
                    service('mailer'),
                    'contact@example.com',
                ]);

            // Create an alias, so that - by default - if you type-hint SiteUpdateManager,
            // the site_update_manager.superadmin will be used
            $services->alias(SiteUpdateManager::class, 'site_update_manager.superadmin');
        };

In this case, *two* services are registered: ``site_update_manager.superadmin``
and ``site_update_manager.normal_users``. Thanks to the alias, if you type-hint
``SiteUpdateManager`` the first (``site_update_manager.superadmin``) will be passed.

If you want to pass the second, you'll need to :ref:`manually wire the service <services-wire-specific-service>`
or to create a named ref:`autowiring alias <autowiring-alias>`.

.. caution::

    If you do *not* create the alias and are :ref:`loading all services from src/ <service-container-services-load-example>`,
    then *three* services have been created (the automatic service + your two services)
    and the automatically loaded service will be passed - by default - when you type-hint
    ``SiteUpdateManager``. That's why creating the alias is a good idea.

When using PHP closures to configure your services, it is possible to automatically
inject the current environment value by adding a string argument named ``$env`` to
the closure::

    // config/packages/my_config.php
    namespace Symfony\Component\DependencyInjection\Loader\Configurator;

    return function(ContainerConfigurator $containerConfigurator, string $env): void {
        // `$env` is automatically filled in, so you can configure your
        // services depending on which environment you're on
    };

Generating Adapters for Functional Interfaces
---------------------------------------------

Functional interfaces are interfaces with a single method.
They are conceptually very similar to a closure except that their only method
has a name. Moreover, they can be used as type-hints across your code.

The :class:`Symfony\\Component\\DependencyInjection\\Attribute\\AutowireCallable`
attribute can be used to generate an adapter for a functional interface.
Let's say you have the following functional interface::

    // src/Service/MessageFormatterInterface.php
    namespace App\Service;

    interface MessageFormatterInterface
    {
        public function format(string $message, array $parameters): string;
    }

You also have a service that defines many methods and one of them is the same
``format()`` method of the previous interface::

    // src/Service/MessageFormatterInterface.php
    namespace App\Service;

    class MessageUtils
    {
        // other methods...

        public function format(string $message, array $parameters): string
        {
            // ...
        }
    }

Thanks to the ``#[AutowireCallable]`` attribute, you can now inject this
``MessageUtils`` service as a functional interface implementation::

    namespace App\Service\Mail;

    use App\Service\MessageFormatterInterface;
    use App\Service\MessageUtils;
    use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;

    class Mailer
    {
        public function __construct(
            #[AutowireCallable(service: MessageUtils::class, method: 'format')]
            private MessageFormatterInterface $formatter
        ) {
        }

        public function sendMail(string $message, array $parameters): string
        {
            $formattedMessage = $this->formatter->format($message, $parameters);

            // ...
        }
    }

Instead of using the ``#[AutowireCallable]`` attribute, you can also generate
an adapter for a functional interface through configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:

            # ...

            app.message_formatter:
                class: App\Service\MessageFormatterInterface
                from_callable: [!service {class: 'App\Service\MessageUtils'}, 'format']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="app.message_formatter" class="App\Service\MessageFormatterInterface">
                    <from-callable method="format">
                        <service class="App\Service\MessageUtils"/>
                    </from-callable>
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageFormatterInterface;
        use App\Service\MessageUtils;

        return function(ContainerConfigurator $container) {
            // ...

            $container
                ->set('app.message_formatter', MessageFormatterInterface::class)
                ->fromCallable([inline_service(MessageUtils::class), 'format'])
                ->alias(MessageFormatterInterface::class, 'app.message_formatter')
            ;
        };

By doing so, Symfony will generate a class (also called an *adapter*)
implementing ``MessageFormatterInterface`` that will forward calls of
``MessageFormatterInterface::format()`` to your underlying service's method
``MessageUtils::format()``, with all its arguments.

Learn more
----------

.. toctree::
    :maxdepth: 1
    :glob:

    /service_container/*

.. _`glob pattern`: https://en.wikipedia.org/wiki/Glob_(programming)
.. _`Symfony Fundamentals screencast series`: https://symfonycasts.com/screencast/symfony-fundamentals
