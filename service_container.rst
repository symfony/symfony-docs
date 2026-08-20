Service Container
=================

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Symfony Fundamentals screencast series`_.

`Dependency injection`_ is one of the most important `design patterns`_ in
software development. Its idea is that classes don't create the objects they
need; they receive them from the outside. This keeps your code decoupled and
makes it more reusable and testable.

The tool that automates this pattern is called a **service container** (or
"dependency injection container"): an object that knows how to create and
connect all the objects of your application and provides them to you when you
need them. In Symfony, those objects are called **services** and almost
*everything* that your application does is actually done by one of them.
Symfony itself is built around the service container, so working with it well
is one of the keys to mastering Symfony.

The service container is provided by the DependencyInjection component. Symfony
applications create and configure the container for you. You can also use the
container in any PHP application, as explained in the
:ref:`standalone usage section <service-container-standalone>` of this article.
The next section explains the dependency injection pattern in detail; if you
are already familiar with it, you can skip to the rest of the article.

Understanding Dependency Injection
----------------------------------

To understand why dependency injection is useful and which problems it
solves, consider a class that generates status messages and formats them
before returning them::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\TextFormatter;

    class MessageGenerator
    {
        public function getHappyMessage(): string
        {
            $messages = [
                'You did it! You updated the system! Amazing!',
                'That was one of the coolest updates I\'ve seen all day!',
                'Great work! Keep going!',
            ];

            $formatter = new TextFormatter();

            return $formatter->format($messages[array_rand($messages)]);
        }
    }

This code works well, but it has several problems that are not acceptable in
real applications:

* **The class is limited**: it only supports one specific way of formatting
  messages. If you need HTML messages somewhere else, you have to modify the
  ``MessageGenerator`` class itself;
* **The class is not flexible**: the formatter is created inside the class, so
  there's no way to configure it. All the code using ``MessageGenerator`` gets
  the exact same behavior;
* **The class is hard to test**: you can't replace the formatter with a fake
  object, so any test of ``MessageGenerator`` also tests the real
  ``TextFormatter`` behavior.

The origin of these problems is the same: the class creates the objects it
needs (its *dependencies*) instead of receiving them. A good rule of thumb is
to avoid using the ``new`` keyword to create dependencies inside your classes.

The **first step** to fix this is to move the dependency to the **constructor**.
The class no longer creates the formatter; it declares that it needs one and
expects to receive it::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\TextFormatter;

    class MessageGenerator
    {
        public function __construct(
            private TextFormatter $formatter,
        ) {
        }

        public function getHappyMessage(): string
        {
            $messages = [
                // ...
            ];

            return $this->formatter->format($messages[array_rand($messages)]);
        }
    }

Now, whoever creates a ``MessageGenerator`` must also create its formatter and
pass (or "inject") it as a constructor argument::

    $generator = new MessageGenerator(new TextFormatter());
    $message = $generator->getHappyMessage();

This is **dependency injection**: instead of creating their own dependencies,
classes receive them via the constructor (or, less commonly, via setter methods
or properties; see :doc:`/service_container/injection_types`).

The **second step** is to depend on an **abstraction** instead of a concrete class.
Define an interface for the formatters and use it as the constructor type-hint::

    // src/Formatter/FormatterInterface.php
    namespace App\Formatter;

    interface FormatterInterface
    {
        public function format(string $message): string;
    }

.. code-block:: php

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\FormatterInterface;

    class MessageGenerator
    {
        public function __construct(
            private FormatterInterface $formatter,
        ) {
        }

        // ...
    }

The problems of the original class are now solved: you can pass a
``TextFormatter``, an ``HtmlFormatter`` or any other class implementing the
interface, configured in any way you need. In tests, you can pass a minimal
fake formatter to test the message generation logic in isolation::

    // the same class works with different formatters
    $generator = new MessageGenerator(new HtmlFormatter());

However, dependency injection creates a new problem: *someone* has to create
all these objects. In a real application, services depend on other services,
which depend on other services. You'd need to remember how to build each object,
create the dependencies in the right order and update all that code every time
some constructor changes.

Solving this problem is the job of the **service container**: it stores the
"recipe" of how to build each service and creates the objects for you, in the
right order, only when needed and only once (by default, you get the same
instance every time you ask for a service). In Symfony applications you don't
even have to write those recipes: thanks to :ref:`autowiring <services-autowire>`,
the container reads the constructor type-hints and figures out the dependencies
by itself.

Fetching and Using Services
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
    use Symfony\Component\Routing\Attribute\Route;

    class ProductController extends AbstractController
    {
        #[Route('/products')]
        public function list(LoggerInterface $logger): Response
        {
            $logger->info('Look, I just used a service!');

            // ...
        }
    }

.. _services-debug-container-types:

What other services are available? Find out by running:

.. code-block:: terminal

    $ php bin/console debug:autowiring

When you use these type-hints in your controller methods or inside your
:ref:`own services <service-container-creating-service>`, Symfony will automatically
pass you the service object matching that type.

Throughout the docs, you'll see how to use the many different services that live
in the container.

.. tip::

    There are actually *many* more services in the container, and each service has
    a unique id in the container, like ``request_stack`` or ``router.default``.
    For a full list, you can run ``php bin/console debug:container`` (see
    :ref:`how to debug the container <container-debug-container>`). But most of
    the time, you won't need to worry about this.
    See :ref:`how to choose a specific service <services-wire-specific-service>`.

.. _service-container-creating-service:

Creating/Configuring Services in the Container
----------------------------------------------

You can also organize your *own* code into services. Consider the
``MessageGenerator`` class created earlier to show your users a random, happy
message. If you put this code in your controller, it can't be reused. Defining
it as a class of its own makes it a service that you can use immediately inside
your controller::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use App\Service\MessageGenerator;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

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
``MessageGenerator`` object and returns it. It also constructs and injects its
formatter dependency, as explained in the next sections. But if you never ask
for the service, it's *never* constructed: saving memory and speed. As a bonus,
the ``MessageGenerator`` service is only created *once*: the same instance is
returned each time you ask for it.

.. _service-container-services-load-example:
.. _service-psr4-loader:

The Default Service Configuration
---------------------------------

The documentation assumes you're using the following service configuration,
which is the default config for a new project:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # default configuration for services in *this* file
            _defaults:
                autowire: true      # automatically injects dependencies in your services.
                autoconfigure: true # automatically registers your services as commands, event subscribers, etc.

            # makes classes in src/ available to be used as services
            # this creates a service per class whose id is the fully-qualified class name
            App\:
                resource: '../src/'

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
                <!-- default configuration for services in *this* file -->
                <defaults autowire="true" autoconfigure="true"/>

                <!-- makes classes in src/ available to be used as services -->
                <!-- this creates a service per class whose id is the fully-qualified class name -->
                <prototype namespace="App\" resource="../src/"/>

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
                    ->autowire()      // automatically injects dependencies in your services.
                    ->autoconfigure() // automatically registers your services as commands, event subscribers, etc.
            ;

            // makes classes in src/ available to be used as services
            // this creates a service per class whose id is the fully-qualified class name
            $services->load('App\\', '../src/');

            // order is important in this file because service definitions
            // always *replace* previous ones; add your own service configuration below
        };

.. tip::

    The value of the ``resource`` option can be any valid `glob pattern`_.

Thanks to this configuration, you can automatically use any classes from the
``src/`` directory as a service, without needing to manually configure it.
The ``id`` of each service is its fully-qualified class name. You can override
any service that's imported by using its id (class name) later in this file
(e.g. see :ref:`how to manually wire arguments <services-manually-wire-args>`).
If you override a service, none of the options (e.g. ``public``) are inherited
from the import (but the overridden service *does* still inherit from ``_defaults``).

.. note::

    Wait, does this mean that *every* class in ``src/`` is registered as a
    service? Even model classes? Actually, no. As long as you keep your imported
    services as :ref:`private <container-public>`, all classes in ``src/`` that
    are *not* explicitly used as services are automatically removed from the
    final container. In reality, the import means that all classes are
    "available to be *used* as services" without needing to be manually configured.

If you'd prefer to manually wire your service, you can
:ref:`use explicit configuration <services-explicitly-configure-wire-services>`.

.. _services-exclude:

Excluding Services
~~~~~~~~~~~~~~~~~~

If some files or directories in your project should not become services, you
can exclude them using the ``exclude`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            App\:
                resource: '../src/'
                exclude:
                    - '../src/SomeDirectory/'
                    - '../src/AnotherDirectory/'
                    - '../src/SomeFile.php'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <prototype namespace="App\" resource="../src/" exclude="../src/{SomeDirectory,AnotherDirectory,Kernel.php}"/>
                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            // ...

            $services->load('App\\', '../src/')
                ->exclude('../src/{SomeDirectory,AnotherDirectory,Kernel.php}');
        };

.. tip::

    The value of the ``exclude`` option can be any valid `glob pattern`_.

Excluding paths is optional, but it will slightly increase performance in the
``dev`` environment: excluded paths are not tracked and so modifying them will
not cause the container to be rebuilt.

.. _service-container-exclude-attribute:

If you want to exclude only a few services, you may use the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\Exclude`
attribute directly on your class to exclude it::

    // src/Service/SomeService.php
    namespace App\Service;

    use Symfony\Component\DependencyInjection\Attribute\Exclude;

    #[Exclude]
    class SomeService
    {
        // ...
    }

Multiple Service Definitions Using the Same Namespace
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In the examples above, the key of each entry (e.g. ``App\``) is the namespace
prefix of the classes to load. Sometimes you need to load classes of the *same*
namespace in several groups, each one with a different configuration (e.g. to
apply a different tag to each group). You can't do that with the previous
syntax, because YAML does not allow using the same key more than once.

To solve this, use any unique string as the key of each group and define the
real namespace prefix in the ``namespace`` option:

.. code-block:: yaml

    # config/services.yaml
    services:
        # 'command_handlers' and 'event_subscribers' are not service ids or
        # namespaces; they can be any unique string used as the entry key
        command_handlers:
            namespace: App\Domain\
            resource: '../src/Domain/*/CommandHandler'
            tags: [command_handler]

        event_subscribers:
            namespace: App\Domain\
            resource: '../src/Domain/*/EventSubscriber'
            tags: [event_subscriber]

.. note::

    This limitation only exists in the YAML format. In XML and PHP config
    files, the namespace is a regular attribute or argument, so you can repeat
    it in as many definitions as needed.

.. _services-autowire:

The ``autowire`` Option
~~~~~~~~~~~~~~~~~~~~~~~

The default configuration sets ``autowire: true`` in the ``_defaults`` section,
so it applies to all services defined in that file. With this setting, the
container looks at the type-hints of the arguments in the ``__construct()``
method of your services and automatically passes the correct services to them.
If it can't, you'll see a clear exception with a helpful suggestion. This
entire article has been written around autowiring.

**Autowiring** is how the "dependency injection problem" described in the
introduction disappears in Symfony applications: you declare the dependencies
with type-hints and the container does the rest. Most of the time you won't
write any service configuration.

For more details, check out the :doc:`service autowiring documentation </service_container/autowiring>`,
which covers how autowiring works, how to handle
:ref:`multiple implementations of the same type <autowiring-multiple-implementations-same-type>`,
the :ref:`#[Autowire] attribute <autowire-attribute>` for non-service
arguments, and :ref:`generating closures <autowiring_closures>` from services.

.. _services-autoconfigure:

The ``autoconfigure`` Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The default configuration also sets ``autoconfigure: true`` in the ``_defaults``
section, so it applies to all services defined in that file. With this setting,
the container looks at the interfaces implemented by your service classes (and
also at their base classes and PHP attributes) to detect the *purpose* of each
service and to integrate it automatically in the feature that uses it.

Symfony needs to know, for example, which of your services are
:doc:`console commands </console>` (to run them when you execute their
command), which ones are :ref:`event subscribers <events-subscriber>` (to call
them when their events happen), which ones are
:ref:`Twig extensions <templates-twig-extension>`, etc. Thanks to
``autoconfigure``, you don't have to declare any of that: create a class that
implements the right interface or extends the right base class, and Symfony
detects it and integrates it for you.

Internally, autoconfiguration uses **service tags**: labels added to the
service definitions to mark that a service must be processed in some special
way by Symfony or by third-party bundles. Most of the time you don't have to
work with tags yourself because autoconfiguration adds them for you. For
example, if your class implements ``Twig\Extension\ExtensionInterface``,
``autoconfigure`` adds the ``twig.extension`` tag to the service and Twig
loads it as one of its extensions. Read more about tags and how to use them
explicitly in the :doc:`service tags article </service_container/tags>`.

Autoconfiguration also works with attributes. Some attributes like
:class:`Symfony\\Component\\Messenger\\Attribute\\AsMessageHandler`,
:class:`Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener` and
:class:`Symfony\\Component\\Console\\Attribute\\AsCommand` are registered
for autoconfiguration. Any class using these attributes will have tags applied
to them.

.. versionadded:: 7.4

    In Symfony 7.4, autoconfiguration attributes on abstract classes are also
    parsed when using resource-based service loading. Previously, abstract
    classes were skipped entirely during this process.

.. _services-constructor-injection:

Injecting Services/Config into a Service
----------------------------------------

What if you need to access the ``logger`` service from within ``MessageGenerator``?
No problem! Create a ``__construct()`` method with a ``$logger`` argument that has
the ``LoggerInterface`` type-hint. Set this on a new ``$logger`` property
and use it later::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Formatter\FormatterInterface;
    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        public function __construct(
            private FormatterInterface $formatter,
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
find the matching service.

How should you know to use ``LoggerInterface`` for the type-hint? You can either
read the docs for whatever feature you're using, or use the ``debug:autowiring``
command :ref:`shown earlier <services-debug-container-types>` to get a list of
all autowireable type-hints in your application.

.. tip::

    If some dependency is optional (i.e. your service can work without it),
    declare the argument as nullable with a ``null`` default value (e.g.
    ``private ?LoggerInterface $logger = null``). The container injects the
    service if it exists and ``null`` otherwise. See
    :ref:`how to make dependencies optional <optional-dependencies>`.

.. _services-manually-wire-args:

Injecting Values That Cannot Be Autowired
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Autowiring works no matter how many arguments the constructor has, but it only
works when the arguments are services. The most common case of non-service
arguments is passing configuration values to services.

Suppose you create a new service to email a site administrator each time a
site update is made, and that the admin email must be configurable::

    // src/Service/SiteUpdater.php
    namespace App\Service;

    use Symfony\Component\Mailer\MailerInterface;
    use Symfony\Component\Mime\Email;

    class SiteUpdater
    {
        public function __construct(
            private MailerInterface $mailer,
            private string $adminEmail,
        ) {
        }

        public function notifyOfSiteUpdate(): void
        {
            // ...

            $email = (new Email())
                ->from('admin@example.com')
                ->to($this->adminEmail)
                ->subject('Site update just happened!')
                ->text('...');

            $this->mailer->send($email);
        }
    }

The ``MailerInterface`` argument is autowired as usual. But the container can't
guess the value to pass to the ``$adminEmail`` argument, so if you use this
service you'll see an error:

    Cannot autowire service "App\\Service\\SiteUpdater": argument "$adminEmail"
    of method "__construct()" must have a type-hint or be given a value explicitly.

The recommended way to solve this is the
:ref:`#[Autowire] attribute <autowire-attribute>`, which defines the value to
inject in the same place where the argument is declared::

    // src/Service/SiteUpdater.php
    namespace App\Service;

    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    // ...

    class SiteUpdater
    {
        public function __construct(
            private MailerInterface $mailer,
            #[Autowire('manager@example.com')]
            private string $adminEmail,
        ) {
        }

        // ...
    }

The ``#[Autowire]`` attribute can also inject :ref:`parameters <service-container-parameters>`,
:ref:`environment variables <config-env-vars>`, :ref:`expressions <services-expressions>`
and even other services. Read more about it in
:ref:`the autowiring article <autowire-attribute>`.

Alternatively, you can set the argument explicitly in your service configuration:

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
            App\Service\SiteUpdater:
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

                <!-- explicitly configure the service -->
                <service id="App\Service\SiteUpdater">
                    <argument key="$adminEmail">manager@example.com</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\SiteUpdater;

        return function(ContainerConfigurator $container): void {
            // ...

            // same as before
            $services->load('App\\', '../src/')
                ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');

            $services->set(SiteUpdater::class)
                ->arg('$adminEmail', 'manager@example.com')
            ;
        };

Thanks to this, the container will pass ``manager@example.com`` to the ``$adminEmail``
argument of ``__construct`` when creating the ``SiteUpdater`` service. The
other argument will still be autowired.

But, isn't this fragile? Fortunately, no! If you rename the ``$adminEmail`` argument
to something else (e.g. ``$mainEmail``) you will get a clear exception when you
reload the next page (even if that page doesn't use this service).

Injecting Scalar Values and Collections
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to injecting services, you can pass any type of value as an
argument of a service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\SomeService:
                arguments:
                    # string, numeric and boolean arguments can be passed "as is"
                    - 'Foo'
                    - true
                    - 7
                    - 3.14

                    # constants can be built-in, user-defined, or Enums
                    - !php/const E_ALL
                    - !php/const PDO::FETCH_NUM
                    - !php/const Symfony\Component\HttpKernel\Kernel::VERSION
                    - !php/const App\Config\SomeEnum::SomeCase

                    # when not using autowiring, you can pass service arguments explicitly
                    - '@some-service-id'  # the leading '@' tells this is a service ID, not a string
                    - '@?some-service-id' # using '?' means to pass null if service doesn't exist

                    # if the value of a string argument starts with '@', you need to escape
                    # it by adding another '@' so Symfony doesn't consider it a service;
                    # the following example would be parsed as the string '@securepassword'
                    - '@@securepassword'

                    # binary contents are passed encoded as base64 strings
                    - !!binary VGhpcyBpcyBhIEJlbGwgY2hhciAH

                    # collections (arrays) can include any type of argument
                    -
                        first: !php/const true
                        second: 'Foo'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <services>
                <service id="App\Service\SomeService">
                    <!-- arguments without a type can be strings or numbers -->
                    <argument>Foo</argument>
                    <argument>7</argument>
                    <argument>3.14</argument>
                    <!-- explicitly declare a string argument -->
                    <argument type="string">Foo</argument>
                    <!-- booleans are passed as constants -->
                    <argument type="constant">true</argument>

                    <!-- constants can be built-in, user-defined, or Enums -->
                    <argument type="constant">E_ALL</argument>
                    <argument type="constant">PDO::FETCH_NUM</argument>
                    <argument type="constant">Symfony\Component\HttpKernel\Kernel::VERSION</argument>
                    <argument type="constant">App\Config\SomeEnum::SomeCase</argument>

                    <!-- when not using autowiring, you can pass service arguments explicitly -->
                    <argument type="service" id="some-service-id"/>
                    <!-- use on-invalid="null" to pass null if the service doesn't exist -->
                    <argument type="service" id="some-service-id" on-invalid="null"/>

                    <!-- binary contents are passed encoded as base64 strings -->
                    <argument type="binary">VGhpcyBpcyBhIEJlbGwgY2hhciAH</argument>

                    <!-- collections (arrays) can include any type of argument -->
                    <argument type="collection">
                        <argument key="first" type="constant">true</argument>
                        <argument key="second" type="string">Foo</argument>
                    </argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\DependencyInjection\Reference;

        return static function (ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(App\Service\SomeService::class)
                // string, numeric and boolean arguments can be passed "as is"
                ->arg(0, 'Foo')
                ->arg(1, true)
                ->arg(2, 7)
                ->arg(3, 3.14)

                // constants: built-in, user-defined, or Enums
                ->arg(4, E_ALL)
                ->arg(5, \PDO::FETCH_NUM)
                ->arg(6, Symfony\Component\HttpKernel\Kernel::VERSION)
                ->arg(7, App\Config\SomeEnum::SomeCase)

                // when not using autowiring, you can pass service arguments explicitly;
                // this fails if the service doesn't exist
                ->arg(8, service('some-service-id'))
                // this passes null if the service doesn't exist
                ->arg(9, new Reference('some-service-id', Reference::NULL_ON_INVALID_REFERENCE))

                // collection with mixed argument types
                ->arg(10, [
                    'first' => true,
                    'second' => 'Foo',
                ]);

            // ...
        };

.. note::

    In XML service definitions, collection argument keys can use the ``key-type``
    attribute to specify how the key value is interpreted. The supported types
    are ``constant`` (resolves the key as a PHP constant) and ``binary``
    (decodes the key from a base64-encoded string):

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Service\SomeService">
            <argument type="collection">
                <!-- key resolved as the value of the constant (e.g. the 'published' string
                     if the class defines it as: const STATUS_PUBLISHED = 'published') -->
                <argument key-type="constant" key="App\Entity\BlogPost::STATUS_PUBLISHED">Value 1</argument>
                <!-- key used as the literal 'App\Entity\BlogPost::STATUS_PUBLISHED' string -->
                <argument key="App\Entity\BlogPost::STATUS_PUBLISHED">Value 2</argument>
                <!-- key decoded from base64 to binary string -->
                <argument key-type="binary" key="AQID">Value 3</argument>
            </argument>
        </service>

    .. versionadded:: 7.2

        The ``key-type`` attribute for XML service argument keys was introduced in Symfony 7.2.

.. _service-container-parameters:

Injecting Container Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to holding service objects, the container also holds configuration
values called **parameters**. The main article about Symfony configuration
explains :ref:`configuration parameters <configuration-parameters>` in detail
and shows all their types (string, boolean, array, binary and PHP constant
parameters).

To inject a parameter into a service, use its name surrounded by two ``%``
characters (or the dedicated ``#[Autowire]`` option):

.. configuration-block::

    .. code-block:: php-attributes

        // src/Service/SiteUpdater.php
        namespace App\Service;

        use Symfony\Component\DependencyInjection\Attribute\Autowire;
        // ...

        class SiteUpdater
        {
            public function __construct(
                #[Autowire(param: 'app.admin_email')]
                private string $adminEmail,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\SiteUpdater:
                arguments:
                    $adminEmail: '%app.admin_email%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\SiteUpdater">
                    <argument key="$adminEmail">%app.admin_email%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\SiteUpdater;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(SiteUpdater::class)
                ->arg('$adminEmail', param('app.admin_email'))
            ;
        };

    .. code-block:: php-standalone

        use App\Service\SiteUpdater;
        use Symfony\Component\DependencyInjection\ContainerBuilder;

        $container = new ContainerBuilder();
        $container->setParameter('app.admin_email', 'manager@example.com');

        $container->register(SiteUpdater::class)
            ->setAutowired(true)
            ->setArgument('$adminEmail', '%app.admin_email%');

.. warning::

    Using the ``.`` notation in parameter names is a
    :ref:`Symfony convention <service-naming-conventions>` to make parameters
    easier to read. Parameters are flat key-value elements; they can't
    be organized into a nested array.

The same syntax works for :ref:`environment variables <config-env-vars>`:
use ``%env(SOME_VARIABLE)%`` to inject the value of an environment variable
at runtime.

.. _services-expressions:

Injecting Values Based on Expressions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes the value to inject is not static but the result of some logic. For
these cases, the service container supports *expressions*, written with the
:doc:`ExpressionLanguage syntax </reference/formats/expression_language>`.

Suppose that the ``App\Mail\MailerConfiguration`` service has a
``getMailerMethod()`` method that returns a string like ``sendmail`` based on
some configuration and that you want to pass the result of this method as a
constructor argument to another service:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Mailer.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\Autowire;

        class Mailer
        {
            public function __construct(
                // because of the escaping applied by PHP, you must add 4 backslashes for each original backslash
                #[Autowire(expression: 'service("App\\\\Mail\\\\MailerConfiguration").getMailerMethod()')]
                private string $mailerMethod,
            ) {
            }

            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Mailer:
                # the '@=' prefix is required when using expressions for arguments in YAML files
                arguments: ['@=service("App\\Mail\\MailerConfiguration").getMailerMethod()']
                # when using double-quoted strings, the backslash needs to be escaped twice (see https://yaml.org/spec/1.2/spec.html#id2787109)
                # arguments: ["@=service('App\\\\Mail\\\\MailerConfiguration').getMailerMethod()"]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Mailer">
                    <argument type="expression">service('App\\Mail\\MailerConfiguration').getMailerMethod()</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mailer;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(Mailer::class)
                // because of the escaping applied by PHP, you must add 4 backslashes for each original backslash
                ->args([expr("service('App\\\\Mail\\\\MailerConfiguration').getMailerMethod()")]);
        };

In this context, you have access to 3 functions:

``service``
    Returns a given service (see the example above).
``parameter``
    Returns a specific parameter value (syntax is like ``service``).
``env``
    Returns the value of an env variable.

You also have access to the :class:`Symfony\\Component\\DependencyInjection\\Container`
via a ``container`` variable, which allows you to write more elaborated
expressions like ``container.hasParameter('some_param') ? parameter('some_param') : 'default_value'``.

Expressions can be used in ``arguments``, ``properties``, as arguments with
``configurator``, as arguments to ``calls`` (method calls) and in
``factories`` (:doc:`service factories </service_container/factories>`).

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
one of the services: ``logger`` in this case (read more about why in :ref:`service-autowiring-alias`).
But, you can control this and pass in a different logger:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Service/MessageGenerator.php
        namespace App\Service;

        use Psr\Log\LoggerInterface;
        use Symfony\Component\DependencyInjection\Attribute\Autowire;

        class MessageGenerator
        {
            public function __construct(
                #[Autowire(service: 'monolog.logger.request')]
                private LoggerInterface $logger,
            ) {
            }
            // ...
        }

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
the service whose id is ``monolog.logger.request``.

.. tip::

    If you need to choose between multiple implementations of the same type
    across your application, you can use
    :ref:`named autowiring aliases <autowiring-multiple-implementations-same-type>`
    instead of manually wiring each injection point.

For a list of possible logger services that can be used with autowiring, run:

.. code-block:: terminal

    $ php bin/console debug:autowiring logger

.. _services-explicitly-configure-wire-services:

Explicitly Configuring Services and Arguments
---------------------------------------------

:ref:`Loading services automatically <service-container-services-load-example>`
and :ref:`autowiring <services-autowire>` are optional. And even if you use them,
there may be some cases where you want to manually wire a service. For example,
suppose that you want to register two services for the ``SiteUpdater`` class,
each with a different admin email. In this case, each needs to have a unique
service id:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # this is the service's id
            site_updater.superadmin:
                class: App\Service\SiteUpdater
                # you CAN still use autowiring here, but this example shows what it looks like without
                autowire: false
                # manually wire all arguments
                arguments:
                    - '@mailer'
                    - 'superadmin@example.com'

            site_updater.normal_users:
                class: App\Service\SiteUpdater
                autowire: false
                arguments:
                    - '@mailer'
                    - 'contact@example.com'

            # Create an alias, so that, by default, if you type-hint SiteUpdater,
            # the site_updater.superadmin will be used
            App\Service\SiteUpdater: '@site_updater.superadmin'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="site_updater.superadmin" class="App\Service\SiteUpdater" autowire="false">
                    <argument type="service" id="mailer"/>
                    <argument>superadmin@example.com</argument>
                </service>

                <service id="site_updater.normal_users" class="App\Service\SiteUpdater" autowire="false">
                    <argument type="service" id="mailer"/>
                    <argument>contact@example.com</argument>
                </service>

                <!-- Create an alias, so that, by default, if you type-hint SiteUpdater,
                     the site_updater.superadmin will be used -->
                <service id="App\Service\SiteUpdater" alias="site_updater.superadmin"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\SiteUpdater;

        return function(ContainerConfigurator $container): void {
            // ...

            // site_updater.superadmin is the service's id
            $services->set('site_updater.superadmin', SiteUpdater::class)
                // you CAN still use autowiring here, but this example shows what it looks like without
                ->autowire(false)
                // manually wire all arguments
                ->args([
                   service('mailer'),
                   'superadmin@example.com',
                ]);

            $services->set('site_updater.normal_users', SiteUpdater::class)
                ->autowire(false)
                ->args([
                    service('mailer'),
                    'contact@example.com',
                ]);

            // Create an alias, so that, by default, if you type-hint SiteUpdater,
            // the site_updater.superadmin will be used
            $services->alias(SiteUpdater::class, 'site_updater.superadmin');
        };

In this case, *two* services are registered: ``site_updater.superadmin``
and ``site_updater.normal_users``. Thanks to the alias, if you type-hint
``SiteUpdater`` the first (``site_updater.superadmin``) will be passed.
Read more about aliases in :ref:`the autowiring article <service-autowiring-alias>`.

If you want to pass the second, you'll need to :ref:`manually wire the service <services-wire-specific-service>`
or to create a named :ref:`autowiring alias <autowiring-alias>`.

.. warning::

    If you do *not* create the alias and are :ref:`loading all services from src/ <service-container-services-load-example>`,
    then *three* services have been created (the automatic service + your two services)
    and the automatically loaded service will be passed, by default, when you type-hint
    ``SiteUpdater``. That's why creating the alias is a good idea.

.. _service-container-imports-directive:

Importing Configuration with ``imports``
----------------------------------------

By default, service configuration lives in ``config/services.yaml``. But if that
file becomes large, you're free to organize the configuration into multiple
files. Suppose you decided to move some configuration to a new file:

.. configuration-block::

    .. code-block:: yaml

        # config/services/mailer.yaml
        parameters:
            # ... some parameters

        services:
            # ... some services

    .. code-block:: xml

        <!-- config/services/mailer.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <!-- ... some parameters -->
            </parameters>

            <services>
                <!-- ... some services -->
            </services>
        </container>

    .. code-block:: php

        // config/services/mailer.php

        // ... some parameters
        // ... some services

To import this file, use the ``imports`` key from any other file and pass either
a relative or absolute path to the imported file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: services/mailer.yaml }
            # if you want to import a whole directory:
            - { resource: services/ }
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="services/mailer.xml"/>
                <!-- if you want to import a whole directory: -->
                <import resource="services/"/>
            </imports>

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/"/>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $container->import('services/mailer.php');
            // if you want to import a whole directory:
            $container->import('services/');

            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
            ;

            $services->load('App\\', '../src/');
        };

When loading a configuration file, Symfony first processes all imported files in
the order they are listed under the ``imports`` key. After all imports are processed,
it then processes the parameters and services defined directly in the current file.
In practice, this means that **later definitions override earlier ones**.

For example, if you use the :ref:`default services.yaml configuration <service-container-services-load-example>`
as in the above example, your main ``config/services.yaml`` file uses the ``App\``
namespace to auto-discover services and loads them after all imported files.
If an imported file (e.g. ``config/services/mailer.yaml``) defines a service that
is also auto-discovered, the definition from ``services.yaml`` will take precedence.

To make sure your specific service definitions are not overridden by auto-discovery,
consider one of the following strategies:

#. :ref:`Exclude services from auto-discovery <import-exclude-services-from-auto-discovery>`
#. :ref:`Override services in the same file <import-override-services-in-the-same-file>`
#. :ref:`Control import order <import-control-import-order>`

.. _import-exclude-services-from-auto-discovery:

Exclude Services from Auto-Discovery
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Adjust the ``App\`` definition to use :ref:`the exclude option <services-exclude>`.
This prevents Symfony from auto-registering classes that are defined manually elsewhere:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: services/mailer.yaml }
            # ... other imports

        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/'
                exclude:
                    - '../src/Mailer/'
                    - '../src/SpecificClass.php'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="services/mailer.xml"/>
                <!-- ... other imports -->
            </imports>

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/">
                    <exclude>../src/Mailer/</exclude>
                    <exclude>../src/SpecificClass.php</exclude>
                </prototype>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $container->import('services/mailer.php');
            // ... other imports

            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure()
            ;

            $services->load('App\\', '../src/')
                ->exclude([
                    '../src/Mailer/',
                    '../src/SpecificClass.php',
                ]);
        };

.. _import-override-services-in-the-same-file:

Override Services in the Same File
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can define specific services after the ``App\`` auto-discovery block in the
same file. These later definitions will override the auto-registered ones:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../src/'

            App\Mailer\MyMailer:
                arguments: ['%env(MAILER_DSN)%']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../src/"/>

                <service id="App\Mailer\MyMailer">
                    <argument>%env(MAILER_DSN)%</argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure();

            $services->load('App\\', '../src/');

            $services->set(App\Mailer\MyMailer::class)
                ->arg(0, '%env(MAILER_DSN)%');
        };

.. _import-control-import-order:

Control the Import Order
~~~~~~~~~~~~~~~~~~~~~~~~

Move the ``App\`` auto-discovery config to a separate file and import it
before more specific service files. This way, specific service definitions
can override the auto-discovered ones.

.. configuration-block::

    .. code-block:: yaml

        # config/services/autodiscovery.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true

            App\:
                resource: '../../src/'
                exclude:
                    - '../../src/Mailer/'

        # config/services/mailer.yaml
        services:
            App\Mailer\SpecificMailer:
                # ... custom configuration

        # config/services.yaml
        imports:
            - { resource: services/autodiscovery.yaml }
            - { resource: services/mailer.yaml }
            - { resource: services/ }

        services:
            # definitions here override anything from the imports above
            # consider keeping most definitions inside imported files

    .. code-block:: xml

        <!-- config/services/autodiscovery.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                       https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <defaults autowire="true" autoconfigure="true"/>

                <prototype namespace="App\" resource="../../src/">
                    <exclude>../../src/Mailer/</exclude>
                </prototype>
            </services>
        </container>

        <!-- config/services/mailer.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                       https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer\SpecificMailer">
                    <!-- ... custom configuration -->
                </service>
            </services>
        </container>

        <!-- config/services.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://symfony.com/schema/dic/services
                       https://symfony.com/schema/dic/services/services-1.0.xsd">

            <imports>
                <import resource="services/autodiscovery.xml"/>
                <import resource="services/mailer.xml"/>
                <import resource="services/"/>
            </imports>

            <services>
                <!-- definitions here override anything from the imports above -->
                <!-- consider keeping most definitions inside imported files -->
            </services>
        </container>

    .. code-block:: php

        // config/services/autodiscovery.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services()
                ->defaults()
                    ->autowire()
                    ->autoconfigure();

            $services->load('App\\', '../../src/')
                ->exclude([
                    '../../src/Mailer/',
                ]);
        };

        // config/services/mailer.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(App\Mailer\SpecificMailer::class);
            // add any custom configuration here if needed
        };

        // config/services.php
        use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

        return function (ContainerConfigurator $container): void {
            $container->import('services/autodiscovery.php');
            $container->import('services/mailer.php');
            $container->import('services/');

            $services = $container->services();

            // definitions here override anything from the imports above
            // consider keeping most definitions inside imported files
        };

.. include:: /service_container/_imports-parameters-note.rst.inc

.. _service-container-extension-configuration:

Importing Configuration via Container Extensions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Third-party bundle container configuration, including Symfony core services,
are usually loaded using another method: a :doc:`container extension </bundles/extension>`.

Internally, each bundle defines its services in files like you've seen so far.
However, these files aren't imported using the ``imports`` directive. Instead, bundles
use a *dependency injection extension* to load the files automatically. As soon
as you enable a bundle, its extension is called, which is able to load service
configuration files.

In fact, each configuration file in ``config/packages/`` is passed to the
extension of its related bundle (e.g. ``FrameworkBundle`` or ``TwigBundle``)
and used to configure those services further.

.. _service-container_limiting-to-env:

Conditional Service Registration
--------------------------------

Sometimes a service must be registered only in certain scenarios. Currently,
the only supported condition is the :ref:`configuration environment <configuration-environments>`;
for example, to register a service only in the ``dev`` environment:

.. configuration-block::

    .. code-block:: php-attributes

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

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\SomeClass: ~

        when@dev:
            services:
                App\Service\AnotherClass: ~

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="App\Service\SomeClass"/>

            <when env="dev">
                <services>
                    <service id="App\Service\AnotherClass"/>
                </services>
            </when>
        </services>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return static function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(App\Service\SomeClass::class);

            if ('dev' === $container->env()) {
                $services->set(App\Service\AnotherClass::class);
            }
        };

.. warning::

    The ``_defaults`` section applies only to services defined in the same
    ``services`` block. Each ``when@<env>`` block has its own scope and does
    not inherit ``_defaults`` from the main ``services`` section. Redefine
    ``_defaults`` in every ``when@<env>`` block where you need it:

    .. code-block:: yaml

        # config/services.yaml
        services:
            _defaults:
                autowire: true
                autoconfigure: true
            # ...

        when@prod:
            services:
                _defaults:
                    autowire: true
                    autoconfigure: true
                # ...

If you want to exclude a service from being registered in a specific
environment, you can use the ``#[WhenNot]`` attribute::

    use Symfony\Component\DependencyInjection\Attribute\WhenNot;

    // SomeClass is registered in all environments except "dev"

    #[WhenNot(env: 'dev')]
    class SomeClass
    {
        // ...
    }

    // you can apply more than one WhenNot attribute to the same class

    #[WhenNot(env: 'dev')]
    #[WhenNot(env: 'test')]
    class AnotherClass
    {
        // ...
    }

.. versionadded:: 7.2

    The ``#[WhenNot]`` attribute was introduced in Symfony 7.2.

.. _container-public:
.. _container-private-services:

Public Versus Private Services
------------------------------

In all the examples of this article, services are used via dependency
injection: they are passed as arguments to the services and controllers that
need them. But there is another way of getting services: the container is
itself a PHP object, so code that has access to it can ask for any service
directly using ``$container->get('service_id')``.

Fetching services like this is **strongly discouraged** in your own code: it hides
the real dependencies of your classes and it removes most of the benefits of
dependency injection explained at the beginning of this article. It only makes
sense at the *entry point* of an application (that's why the
:ref:`standalone usage section <service-container-standalone>` uses it) and in
some tests.

That's why every service defined is **private by default**. When a service is
private, you cannot access it directly from the container using
``$container->get()``; the only way to use it is dependency injection. As a
best practice, you should keep all your services private.

.. _services-why-private:

Private services are special because they allow the container to optimize whether
and how they are instantiated. This increases the container's performance. It also
gives you better errors: if you try to reference a non-existent service, you will
get a clear error when you refresh any page, even if the problematic code would
not have run on that page.

.. note::

    A private service can still be given an additional name (an *alias*) or be
    made accessible through a public alias. Read more about aliases in
    :ref:`the autowiring article <services-alias>`.

.. _inlined-private-services:

.. tip::

    A common reason to make services public is to fetch some of them by their
    id at runtime (e.g. to pick one service or another depending on some
    value). Instead of making those services public, use a
    :doc:`service locator </service_container/subscribers_locators>`: it provides
    direct access to a predefined set of services while keeping them private.

If none of this fits your use case and you still need to make a service
public, override the ``public`` setting:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Service/PublicService.php
        namespace App\Service;

        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

        #[Autoconfigure(public: true)]
        class PublicService
        {
            // ...
        }

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
                <service id="App\Service\PublicService" public="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\PublicService;

        return function(ContainerConfigurator $container): void {
            // ... same as code before

            // explicitly configure the service
            $services->set(PublicService::class)
                ->public()
            ;
        };

.. _service-container-request:

Fetching the Request in a Service
---------------------------------

Whenever you need to access the current :ref:`request <component-http-foundation-request>`
in a service, inject the ``RequestStack`` service and get the request from it::

    // src/Service/NewsletterSender.php
    namespace App\Service;

    use Symfony\Component\HttpFoundation\RequestStack;

    class NewsletterSender
    {
        public function __construct(
            private RequestStack $requestStack,
        ) {
        }

        public function anyMethod(): void
        {
            $request = $this->requestStack->getCurrentRequest();
            // ... do something with the request
        }

        // ...
    }

.. tip::

    In a controller you can get the ``Request`` object by having it passed in as an
    argument to your action method. See :ref:`controller-request-argument` for
    details.

.. _container-debug-container:

Debugging and Linting the Container
-----------------------------------

The following commands show all the information stored in the container,
which is useful to debug dependency injection issues:

.. code-block:: terminal

    # shows all services registered in the container (public and private)
    # and the PHP class associated to each of them
    $ php bin/console debug:container

    # add this option to also display "hidden services" (those whose ID starts with a dot)
    $ php bin/console debug:container --show-hidden

    # shows detailed information about a single service, including its
    # arguments, tags and the rest of its configuration
    $ php bin/console debug:container App\Service\Mailer

    # shows all services tagged with the given tag
    $ php bin/console debug:container --tag=kernel.event_listener

    # if you pass an incomplete tag name, the command shows an
    # interactive list of all the matching tags
    $ php bin/console debug:container --tag=kernel

    # shows all the types you can use as type-hints when autowiring
    $ php bin/console debug:autowiring

    # pass a search term to filter the results
    $ php bin/console debug:autowiring logger

.. deprecated:: 7.3

    Starting in Symfony 7.3, the ``debug:container`` command displays the
    service arguments by default. In earlier Symfony versions, you needed to
    use the ``--show-arguments`` option, which is now deprecated.

Linting Service Definitions
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``lint:container`` command performs additional checks to ensure the container
is properly configured. It is useful to run this command before deploying your
application to production (e.g. in your continuous integration server):

.. code-block:: terminal

    $ php bin/console lint:container

    # optionally, you can force the resolution of environment variables;
    # the command will fail if any of those environment variables are missing
    $ php bin/console lint:container --resolve-env-vars

.. versionadded:: 7.2

    The ``--resolve-env-vars`` option was introduced in Symfony 7.2.

Performing those checks whenever the container is compiled can hurt performance.
That's why they are implemented in :doc:`compiler passes </service_container/compiler_passes>`
called ``CheckTypeDeclarationsPass`` and ``CheckAliasValidityPass``, which are
disabled by default and enabled only when executing the ``lint:container`` command.
If you don't mind the performance loss, you can enable these compiler passes in
your application.

.. versionadded:: 7.1

    The ``CheckAliasValidityPass`` compiler pass was introduced in Symfony 7.1.

.. _service-container-standalone:

Using the Container in Standalone PHP Applications
--------------------------------------------------

The service container and all the features shown in this article are provided
by the DependencyInjection component, which implements a `PSR-11`_ compatible
container. In applications not based on the Symfony framework, first install
the component:

.. code-block:: terminal

    $ composer require symfony/dependency-injection

.. include:: /components/require_autoload.rst.inc

Registering and Fetching Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In standalone applications there is no ``config/services.yaml`` file and no
default configuration. Instead, you create the container and register the
services yourself using the ``ContainerBuilder`` class. Consider the
``MessageGenerator`` and ``FormatterInterface`` classes shown in the
introduction of this article::

    use App\Formatter\TextFormatter;
    use App\Service\MessageGenerator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    $container = new ContainerBuilder();

    // register the formatter service
    $container->register('app.text_formatter', TextFormatter::class);

    // register the message generator and tell the container to inject the
    // formatter service as the first constructor argument; the Reference class
    // makes the container inject a service instead of the given string
    $container->register('app.message_generator', MessageGenerator::class)
        ->addArgument(new Reference('app.text_formatter'));

    // ask the container for the service: it creates the formatter, then the
    // generator and finally injects the former into the latter
    $messageGenerator = $container->get('app.message_generator');
    $message = $messageGenerator->getHappyMessage();

The container also stores parameters that you can reuse in service definitions.
For example, if some services need to know the application locale::

    use App\Service\MessageGenerator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->setParameter('app.locale', 'es');

    $container->register('app.message_generator', MessageGenerator::class)
        // the %...% syntax tells the container to inject the parameter value
        ->addArgument('%app.locale%');

    // you can also work with parameters directly
    // (parameter names are case-sensitive)
    $container->hasParameter('app.locale');
    $container->getParameter('app.locale');

.. note::

    You can only set a parameter before the container is compiled, not at
    runtime. Compiling is explained later in this section.

Besides constructor arguments, you can configure other types of injection,
such as calling setter methods (see :doc:`/service_container/injection_types`
for the available types)::

    use App\Service\MessageGenerator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    $container = new ContainerBuilder();

    $container->register('app.message_generator', MessageGenerator::class)
        ->addMethodCall('setLogger', [new Reference('logger')]);

Getting Services That Don't Exist
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, when you try to get a service that doesn't exist, you see an exception.
You can override this behavior by passing a second argument to the ``get()``
method::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    $container = new ContainerBuilder();

    // ...

    $messageGenerator = $container->get('app.message_generator', ContainerInterface::NULL_ON_INVALID_REFERENCE);

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

.. _components-dependency-injection-loading-config:

Setting Up the Container with Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Besides defining the services in PHP as shown in the previous examples,
standalone applications can also use configuration files. To do this, you also
need to install :doc:`the Config component </components/config>`:

.. code-block:: terminal

    $ composer require symfony/config

Then, create a loader for the format of your configuration files and load them
into the container::

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

    $container = new ContainerBuilder();

    // loading an XML config file
    $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.xml');

    // loading a YAML config file (this requires to also install the Yaml
    // component: composer require symfony/yaml)
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.yaml');

    // loading a PHP config file
    $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
    $loader->load('services.php');

.. tip::

    If your application uses unconventional file extensions (for example, your
    XML files have a ``.config`` extension) you can pass the file type as the
    second optional parameter of the ``load()`` method::

        // ...
        $loader->load('services.config', 'xml');

The contents of these configuration files use the same format shown in the
rest of this article. For example, this is how the previous service
definitions look in each format:

.. configuration-block::

    .. code-block:: yaml

        # services.yaml
        parameters:
            app.locale: 'es'

        services:
            app.text_formatter:
                class: App\Formatter\TextFormatter

            app.message_generator:
                class: App\Service\MessageGenerator
                arguments: ['@app.text_formatter']

    .. code-block:: xml

        <!-- services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="app.locale">es</parameter>
            </parameters>

            <services>
                <service id="app.text_formatter" class="App\Formatter\TextFormatter"/>

                <service id="app.message_generator" class="App\Service\MessageGenerator">
                    <argument type="service" id="app.text_formatter"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Formatter\TextFormatter;
        use App\Service\MessageGenerator;

        return static function (ContainerConfigurator $container): void {
            $container->parameters()
                ->set('app.locale', 'es')
            ;

            $services = $container->services();

            $services->set('app.text_formatter', TextFormatter::class);

            $services->set('app.message_generator', MessageGenerator::class)
                ->args([service('app.text_formatter')])
            ;
        };

Compiling the Container and Best Practices
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before using the container, call the ``compile()`` method to resolve
parameters, optimize the service definitions and check their correctness::

    // ...
    $container->compile();

    $messageGenerator = $container->get('app.message_generator');

The compilation process is also the extension point of the container: you can
register *compiler passes* that modify the service definitions before the
container is frozen. Read :doc:`/service_container/compiler_passes` to learn
more about them.

Finally, while you can fetch services from the container directly, it is best
to minimize this. Fetch services from the container as few times as possible,
at the entry point of your application, and rely on constructor injection
everywhere else. Otherwise, your classes become coupled to the specific
container object, which makes them harder to reuse and to test.

.. _components-dependency-injection-dumping:

Dumping the Compiled Container for Performance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Compiling the container on every request is slow. That's why Symfony
applications cache the compiled container in ``var/cache/`` and only compile
it again when the configuration changes. In standalone applications, get the
same result by dumping the compiled container to a PHP file with the
``PhpDumper`` class and reusing that file in the following requests::

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

    $file = __DIR__.'/cache/container.php';

    if (file_exists($file)) {
        require_once $file;
        $container = new ProjectServiceContainer();
    } else {
        $container = new ContainerBuilder();
        // ...
        $container->compile();

        $dumper = new PhpDumper($container);
        file_put_contents($file, $dumper->dump());
    }

``ProjectServiceContainer`` is the default name of the dumped container class;
you can change it with the ``class`` option of the ``dump()`` method.

.. tip::

    The ``file_put_contents()`` function is not atomic, which can cause issues
    in production environments with multiple concurrent requests. Use the
    :ref:`dumpFile() method <filesystem-dumpfile>` from the
    :doc:`Filesystem component </components/filesystem>` or the ``ConfigCache``
    class shown in the following example.

The previous example never rebuilds the container, so you must delete the
cached file after any configuration change. Instead, use the ``ConfigCache``
class from the :doc:`Config component </components/config>` to rebuild the
cached container automatically. The container builder keeps track of all the
resources used to configure it (config files, extension classes, compiler
passes, etc.) and ``ConfigCache`` uses them to consider the cache stale
whenever any of those files change::

    use Symfony\Component\Config\ConfigCache;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

    // in debug mode, the cache is rebuilt whenever any config resource changes;
    // when not in debug mode, the cached container is always used if it exists
    $isDebug = true;

    $file = __DIR__.'/cache/container.php';
    $containerConfigCache = new ConfigCache($file, $isDebug);

    if (!$containerConfigCache->isFresh()) {
        $container = new ContainerBuilder();
        // ...
        $container->compile();

        $dumper = new PhpDumper($container);
        $containerConfigCache->write(
            $dumper->dump(['class' => 'MyCachedContainer']),
            $container->getResources()
        );
    }

    require_once $file;
    $container = new MyCachedContainer();

Learn more
----------

.. toctree::
    :maxdepth: 1

    /service_container/autowiring
    /service_container/injection_types
    /service_container/tags
    /service_container/subscribers_locators
    /service_container/decoration
    /service_container/factories
    /service_container/lazy_services
    /service_container/compiler_passes
    /service_container/extensions
    /service_container/advanced_definitions

.. _`Dependency injection`: https://en.wikipedia.org/wiki/Dependency_injection
.. _`design patterns`: https://en.wikipedia.org/wiki/Software_design_pattern
.. _`glob pattern`: https://en.wikipedia.org/wiki/Glob_(programming)
.. _`PSR-11`: https://www.php-fig.org/psr/psr-11/
.. _`Symfony Fundamentals screencast series`: https://symfonycasts.com/screencast/symfony-fundamentals
