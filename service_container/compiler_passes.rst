Compiling and Extending the Container
=====================================

The :doc:`service container </service_container>` doesn't build the services
directly from the configuration files. First, it *compiles* the whole
configuration, loaded from your ``config/`` files and from the
:doc:`extensions </service_container/extensions>` of the enabled bundles: it
checks for potential issues such as circular references, resolves parameters,
removes unused services and optimizes the definitions to make the container
as fast as possible. Then, it caches the compiled container in ``var/cache/``
to avoid repeating all this work on every request.

The compilation runs as a series of steps called **compiler passes**. Symfony
includes many passes that perform all the checks and optimizations, and you
can create your own passes to modify the services in any way you need (add or
remove services, replace arguments, add :doc:`tags </service_container/tags>`
or :ref:`aliases <service-autowiring-alias>`, :doc:`decorate </service_container/decoration>`
services, etc.) before the application starts using them.

.. _components-dependency-injection-compiler-passes:
.. _components-di-separate-compiler-passes:
.. _creating-a-compiler-pass:

Creating a Compiler Pass
------------------------

A compiler pass is a class implementing
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`.
Its ``process()`` method receives the container builder so it can manipulate
services and parameters::

    // src/DependencyInjection/Compiler/CustomPass.php
    namespace App\DependencyInjection\Compiler;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;

    class CustomPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container): void
        {
            // ... do something during the compilation
        }
    }

.. _service-definitions:

Working with Service Definitions
--------------------------------

A **service definition** is an object that describes how the container must
create a service: its class, its constructor arguments, its tags, whether it's
lazy or shared, etc. No matter how you configure your services (automatic
discovery, PHP attributes or YAML, XML and PHP files), the container turns all
that configuration into
:class:`Symfony\\Component\\DependencyInjection\\Definition` objects. The
actual services are only instantiated later, when the application asks for them.

Compiler passes and :doc:`container extensions </service_container/extensions>`
work in that intermediate step: they inspect and modify the ``Definition``
objects with PHP code, so they can change anything about the services before
they are created. In the following examples, the ``$container`` variable is
the ``ContainerBuilder`` object that the ``process()`` method of the compiler
pass receives, as shown in the previous section.

Getting and Setting Service Definitions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There are some helpful methods for working with the service definitions::

    use Symfony\Component\DependencyInjection\Definition;

    // finds out if there is an "app.mailer" definition
    $container->hasDefinition('app.mailer');
    // finds out if there is an "app.mailer" definition or alias
    $container->has('app.mailer');

    // gets the "app.user_config_loader" definition
    $definition = $container->getDefinition('app.user_config_loader');
    // gets the definition with the "app.user_config_loader" ID or alias
    $definition = $container->findDefinition('app.user_config_loader');

    // adds a new "app.number_generator" definition
    $definition = new Definition(\App\NumberGenerator::class);
    $container->setDefinition('app.number_generator', $definition);

    // shortcut for the previous method
    $container->register('app.number_generator', \App\NumberGenerator::class);

.. note::

    As a rule, only work with service definitions in a compiler pass and do not
    create service instances. In practice, this means using the methods
    ``has()``, ``findDefinition()``, ``getDefinition()``, ``setDefinition()``,
    etc. instead of ``get()``, ``set()``, etc.

.. tip::

    Don't assume that the services manipulated by your compiler pass are always
    defined in the container: they might be registered only in some
    :ref:`config environments <configuration-environments>` or only when certain
    bundles are installed. Asking for a definition that doesn't exist throws
    an exception that breaks the compilation of the whole container. That's
    why, if some required service might not exist, check it first with the
    ``$container->has()`` method and end the ``process()`` method early with a
    ``return`` statement, as shown in
    :ref:`this example <service-container-compiler-pass-tags>`.

Setting the Definition Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The first optional argument of the ``Definition`` class is the fully qualified
class name of the object returned when the service is fetched from the container::

    use App\Config\CustomConfigLoader;
    use App\Config\UserConfigLoader;
    use Symfony\Component\DependencyInjection\Definition;

    $definition = new Definition(UserConfigLoader::class);

    // override the class
    $definition->setClass(CustomConfigLoader::class);

    // get the class configured for this definition
    $class = $definition->getClass();

Setting the Constructor Arguments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The second optional argument of the ``Definition`` class is an array with the
arguments passed to the constructor of the object returned when the service is
fetched from the container::

    use App\Config\DoctrineConfigLoader;
    use Symfony\Component\DependencyInjection\Definition;
    use Symfony\Component\DependencyInjection\Reference;

    $definition = new Definition(DoctrineConfigLoader::class, [
        new Reference('doctrine'), // a reference to another service
        '%app.config_table_name%',  // will be resolved to the value of a container parameter
    ]);

    // gets all arguments configured for this definition
    $constructorArguments = $definition->getArguments();

    // gets a specific argument
    $firstArgument = $definition->getArgument(0);

    // adds a new named argument
    // '$argumentName' = the name of the argument in the constructor, including the '$' symbol
    $definition = $definition->setArgument('$argumentName', $argumentValue);

    // adds a new argument
    $definition->addArgument($argumentValue);

    // replaces argument on a specific index (0 = first argument)
    $definition->replaceArgument($index, $argument);

    // replaces all previously configured arguments with the passed array
    $definition->setArguments($arguments);

.. warning::

    Don't use ``get()`` to get a service that you want to inject as constructor
    argument, the service is not yet available. Instead, use a
    ``Reference`` instance as shown above.

Setting the Method Calls
~~~~~~~~~~~~~~~~~~~~~~~~

If the service you are working with uses setter injection then you can manipulate
any method calls in the definitions as well::

    // gets all configured method calls
    $methodCalls = $definition->getMethodCalls();

    // configures a new method call
    $definition->addMethodCall('setLogger', [new Reference('logger')]);

    // configures an immutable-setter
    $definition->addMethodCall('withLogger', [new Reference('logger')], true);

    // replaces all previously configured method calls with the passed array
    $definition->setMethodCalls($methodCalls);

.. tip::

    There are more examples of specific ways of working with definitions
    in the PHP code blocks of the Service Container articles such as
    :doc:`/service_container/factories` and
    :ref:`parent services <parent-services>`.

.. note::

    The methods here that change service definitions can only be used before
    the container is compiled. Once the container is compiled you cannot
    manipulate service definitions further.

.. _service-container-compiler-pass-tags:

Processing Tagged Services in a Compiler Pass
---------------------------------------------

A common use case of compiler passes is to search for all service definitions
that have a certain :doc:`tag </service_container/tags>` in order to
dynamically plug each one into another service.

.. tip::

    If you only need to inject the collection of tagged services somewhere,
    use a :ref:`tagged iterator <tags_reference-tagged-services>` instead;
    it doesn't require writing a compiler pass.

Suppose your application generates reports in several formats and that you
want to register each report generator into a central manager class calling
its ``addGenerator()`` method::

    // src/Report/ReportGeneratorRegistry.php
    namespace App\Report;

    class ReportGeneratorRegistry
    {
        private array $generators = [];

        public function addGenerator(ReportGeneratorInterface $generator, string $format): void
        {
            $this->generators[$format] = $generator;
        }

        public function generate(string $format, array $data): string
        {
            // ...
        }
    }

Tag all the generator services with a custom tag (``app.report_generator``)
that defines a ``format`` attribute (the recommended way is the
``#[AutoconfigureTag]`` attribute on the common interface; see
:ref:`autoconfiguring tags <di-instanceof>` and
:ref:`tag attributes <tags_additional-attributes>`). Then, write a compiler
pass that asks the container for the tagged services with the
``findTaggedServiceIds()`` method::

    // src/DependencyInjection/Compiler/ReportGeneratorPass.php
    namespace App\DependencyInjection\Compiler;

    use App\Report\ReportGeneratorRegistry;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class ReportGeneratorPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container): void
        {
            // always first check if the primary service is defined
            if (!$container->has(ReportGeneratorRegistry::class)) {
                return;
            }

            $definition = $container->findDefinition(ReportGeneratorRegistry::class);

            // find all service IDs with the app.report_generator tag
            $taggedServices = $container->findTaggedServiceIds('app.report_generator');

            foreach ($taggedServices as $id => $tags) {
                // a service could have the same tag twice
                foreach ($tags as $attributes) {
                    $definition->addMethodCall('addGenerator', [
                        new Reference($id),
                        $attributes['format'],
                    ]);
                }
            }
        }
    }

The double loop may be confusing. This is because a service can have more
than one tag. You tag a service twice or more with the ``app.report_generator``
tag. The second ``foreach`` loop iterates over the ``app.report_generator``
tags set for the current service and gives you the attributes.

Creating Service Locators in a Compiler Pass
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sometimes, instead of injecting all the collected services into the main
service, you want to inject them via a
:doc:`service locator </service_container/subscribers_locators>` so they are
only instantiated when used. Create the locator with the
:method:`Symfony\\Component\\DependencyInjection\\Compiler\\ServiceLocatorTagPass::register`
method: it saves you some boilerplate and it shares identical locators among
all the services referencing them::

    use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    public function process(ContainerBuilder $container): void
    {
        // ...

        $locateableServices = [
            // ...
            'logger' => new Reference('logger'),
        ];

        $myService = $container->findDefinition(MyService::class);

        $myService->addArgument(ServiceLocatorTagPass::register($container, $locateableServices));
    }

Registering the Compiler Pass
-----------------------------

Once the compiler pass is created, register it in the container so it runs
during the compilation. The way to do it depends on the type of application.

Registering the Pass in a Symfony Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In Symfony applications, enable your compiler passes in the ``build()``
method of the application kernel::

    // src/Kernel.php
    namespace App;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        // ...

        protected function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass(new CustomPass());
        }
    }

.. _kernel-as-compiler-pass:

If your compiler pass is relatively small, you can define it inside the
application's ``Kernel`` class instead of creating a separate compiler pass
class. To do so, make your kernel implement
:class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
and add the compiler pass code inside the ``process()`` method::

    // src/Kernel.php
    namespace App;

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel implements CompilerPassInterface
    {
        use MicroKernelTrait;

        // ...

        public function process(ContainerBuilder $container): void
        {
            // in this method you can manipulate the service container:
            // for example, changing some container service:
            $container->getDefinition('app.some_private_service')->setPublic(true);

            // or processing tagged services:
            foreach ($container->findTaggedServiceIds('some_tag') as $id => $tags) {
                // ...
            }
        }
    }

Registering the Pass in a Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:doc:`Bundles </bundles>` can define compiler passes in the ``build()`` method of
the main bundle class::

    // src/MyBundle/MyBundle.php
    namespace App\MyBundle;

    use App\DependencyInjection\Compiler\CustomPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class MyBundle extends AbstractBundle
    {
        public function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass(new CustomPass());
        }
    }

If your compiler pass is relatively small, you can make the main bundle class
implement :class:`Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface`
so that it can add itself::

    // src/MyBundle/MyBundle.php
    namespace App\MyBundle;

    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class MyBundle extends AbstractBundle implements CompilerPassInterface
    {
        public function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass($this);
        }

        public function process(ContainerBuilder $container): void
        {
            // in this method you can manipulate the service container:
            // for example, changing some container service:
            $container->getDefinition('app.some_private_service')->setPublic(true);

            // or processing tagged services:
            foreach ($container->findTaggedServiceIds('some_tag') as $id => $tags) {
                // ...
            }
        }
    }

If you are using custom :doc:`service tags </service_container/tags>` in a
bundle, the convention is to format tag names by starting with the bundle's name
in lowercase (using underscores as separators), followed by a dot, and finally
the specific tag name. For example, to introduce a "transport" tag in your
AcmeMailerBundle, you would name it ``acme_mailer.transport``.

Registering the Pass in a Standalone Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the container in
:ref:`standalone PHP applications <service-container-standalone>`, register
the pass with the ``addCompilerPass()`` method before compiling the container::

    use Symfony\Component\DependencyInjection\ContainerBuilder;

    $container = new ContainerBuilder();
    $container->addCompilerPass(new CustomPass());

    // ...
    $container->compile();

Controlling the Pass Ordering
-----------------------------

The default compiler passes are grouped into optimization passes and removal
passes. The optimization passes run first and include tasks such as resolving
references within the definitions. The removal passes perform tasks such
as removing private aliases and unused services. When registering compiler
passes using ``addCompilerPass()``, you can configure when your compiler pass
is run. By default, they are run before the optimization passes.

You can use the following constants to determine when your pass is executed:

* ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
* ``PassConfig::TYPE_OPTIMIZE``
* ``PassConfig::TYPE_BEFORE_REMOVING``
* ``PassConfig::TYPE_REMOVE``
* ``PassConfig::TYPE_AFTER_REMOVING``

For example, to run your custom pass after the default removal passes have
been run, use::

    // ...
    $container->addCompilerPass(new CustomPass(), PassConfig::TYPE_AFTER_REMOVING);

You can also control the order in which compiler passes are run for each
compilation phase. Use the optional third argument of ``addCompilerPass()`` to
set the priority as an integer number. The default priority is ``0`` and the higher
its value, the earlier it's executed::

    // ...
    // FirstPass is executed after SecondPass because its priority is lower
    $container->addCompilerPass(
        new FirstPass(), PassConfig::TYPE_AFTER_REMOVING, 10
    );
    $container->addCompilerPass(
        new SecondPass(), PassConfig::TYPE_AFTER_REMOVING, 30
    );

.. _compiler-pass-autoconfiguration:

Registering Autoconfiguration Rules from PHP Code
-------------------------------------------------

Besides the :ref:`autoconfiguration options <di-instanceof>` available in
attributes and configuration files, you can define autoconfiguration rules
programmatically with the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerForAutoconfiguration`
method.

In a Symfony application, call this method in your kernel class::

    // src/Kernel.php
    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $container): void
        {
            $container->registerForAutoconfiguration(CustomInterface::class)
                ->addTag('app.custom_tag')
            ;
        }
    }

In bundles extending the :class:`Symfony\\Component\\HttpKernel\\Bundle\\AbstractBundle`
class, call this method in the ``loadExtension()`` method of the main bundle class::

    // ...
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

    class MyBundle extends AbstractBundle
    {
        public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
        {
            $builder
                ->registerForAutoconfiguration(CustomInterface::class)
                ->addTag('app.custom_tag')
            ;
        }
    }

.. note::

    For bundles not extending the ``AbstractBundle`` class, call this method in
    the ``load()`` method of the :doc:`bundle extension class </bundles/extension>`.

Autoconfiguration registering is not limited to interfaces. It is possible
to use PHP attributes to autoconfigure services by using the
:method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerAttributeForAutoconfiguration`
method::

    // src/Attribute/SensitiveElement.php
    namespace App\Attribute;

    #[\Attribute(\Attribute::TARGET_CLASS)]
    class SensitiveElement
    {
        public function __construct(
            private string $token,
        ) {
        }

        public function getToken(): string
        {
            return $this->token;
        }
    }

    // src/Kernel.php
    use App\Attribute\SensitiveElement;

    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $container): void
        {
            // ...

            $container->registerAttributeForAutoconfiguration(SensitiveElement::class, static function (ChildDefinition $definition, SensitiveElement $attribute, \ReflectionClass $reflector): void {
                // Apply the 'app.sensitive_element' tag to all classes with SensitiveElement
                // attribute, and attach the token value to the tag
                $definition->addTag('app.sensitive_element', ['token' => $attribute->getToken()]);
            });
        }
    }

You can also make attributes usable on methods. To do so, update the previous
example and add ``Attribute::TARGET_METHOD``::

    // src/Attribute/SensitiveElement.php
    namespace App\Attribute;

    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
    class SensitiveElement
    {
        // ...
    }

Then, update the :method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerAttributeForAutoconfiguration`
call to support ``ReflectionMethod``::

    // src/Kernel.php
    use App\Attribute\SensitiveElement;

    class Kernel extends BaseKernel
    {
        // ...

        protected function build(ContainerBuilder $container): void
        {
            // ...

            $container->registerAttributeForAutoconfiguration(SensitiveElement::class, static function (
                ChildDefinition $definition,
                SensitiveElement $attribute,
                // update the union type to support multiple types of reflection
                // you can also use the "\Reflector" interface
                \ReflectionClass|\ReflectionMethod $reflector): void {
                    if ($reflector instanceof \ReflectionMethod) {
                        // ...
                    }
                }
            );
        }
    }

.. tip::

    You can also define an attribute to be usable on properties and parameters with
    ``Attribute::TARGET_PROPERTY`` and ``Attribute::TARGET_PARAMETER``; then support
    ``ReflectionProperty`` and ``ReflectionParameter`` in your
    :method:`Symfony\\Component\\DependencyInjection\\ContainerBuilder::registerAttributeForAutoconfiguration`
    callable.

.. _resolving-env-vars-at-compile-time:

Resolving Environment Variables at Compile Time
-----------------------------------------------

.. warning::

    **This practice is discouraged**. Use it only if you fully understand the implications.

By default, environment variables are resolved at runtime. However, you can
force their resolution at compile time using the following code::

    $parameterValue = $container->resolveEnvPlaceholders(
        $container->getParameter('%env(ENV_VAR_NAME)%'),
        true // resolve to actual values
    );

However, a **major drawback** of this approach is that you must manually clear
the cache when changing the value of an environment variable. This goes
against the typical behavior of environment variables, which are designed
to be dynamic and not require cache invalidation.
