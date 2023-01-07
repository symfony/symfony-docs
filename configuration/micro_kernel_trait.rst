Building your own Framework with the MicroKernelTrait
=====================================================

The default ``Kernel`` class included in Symfony applications uses a
:class:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait` to configure
the bundles, the routes and the service container in the same class.

This micro-kernel approach is flexible, allowing you to control your application
structure and features.

A Single-File Symfony Application
---------------------------------

Start with a completely empty directory and install these Symfony components
via Composer:

.. code-block:: terminal

    $ composer require symfony/config symfony/http-kernel \
      symfony/http-foundation symfony/routing \
      symfony/dependency-injection symfony/framework-bundle

Next, create an ``index.php`` file that defines the kernel class and runs it::

    // index.php
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

    require __DIR__.'/vendor/autoload.php';

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        public function registerBundles(): array
        {
            return [
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            ];
        }

        protected function configureContainer(ContainerConfigurator $containerConfigurator): void
        {
            // PHP equivalent of config/packages/framework.yaml
            $containerConfigurator->extension('framework', [
                'secret' => 'S0ME_SECRET'
            ]);
        }

        protected function configureRoutes(RoutingConfigurator $routes): void
        {
            $routes->add('random_number', '/random/{limit}')->controller([$this, 'randomNumber']);
        }

        public function randomNumber(int $limit): JsonResponse
        {
            return new JsonResponse([
                'number' => random_int(0, $limit),
            ]);
        }
    }

    $kernel = new Kernel('dev', true);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

That's it! To test it, start the :doc:`Symfony Local Web Server
</setup/symfony_server>`:

.. code-block:: terminal

    $ symfony server:start

Then see the JSON response in your browser: http://localhost:8000/random/10

The Methods of a "Micro" Kernel
-------------------------------

When you use the ``MicroKernelTrait``, your kernel needs to have exactly three methods
that define your bundles, your services and your routes:

**registerBundles()**
    This is the same ``registerBundles()`` that you see in a normal kernel.

**configureContainer(ContainerConfigurator $containerConfigurator)**
    This method builds and configures the container. In practice, you will use
    ``extension()`` to configure different bundles (this is the equivalent
    of what you see in a normal ``config/packages/*`` file). You can also register
    services directly in PHP or load external configuration files (shown below).

**configureRoutes(RoutingConfigurator $routes)**
    Your job in this method is to add routes to the application. The
    ``RoutingConfigurator`` has methods that make adding routes in PHP more
    fun. You can also load external routing files (shown below).

Adding Interfaces to "Micro" Kernel
-----------------------------------

When using the ``MicroKernelTrait``, you can also implement the
``CompilerPassInterface`` to automatically register the kernel itself as a
compiler pass as explained in the dedicated
:ref:`compiler pass section <kernel-as-compiler-pass>`.

It is also possible to implement the ``EventSubscriberInterface`` to handle
events directly from the kernel, again it will be registered automatically::

    // ...
    use App\Exception\Danger;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpKernel\Event\ExceptionEvent;
    use Symfony\Component\HttpKernel\KernelEvents;

    class Kernel extends BaseKernel implements EventSubscriberInterface
    {
        use MicroKernelTrait;

        // ...

        public function onKernelException(ExceptionEvent $event): void
        {
            if ($event->getException() instanceof Danger) {
                $event->setResponse(new Response('It\'s dangerous to go alone. Take this ⚔'));
            }
        }

        public static function getSubscribedEvents(): array
        {
            return [
                KernelEvents::EXCEPTION => 'onKernelException',
            ];
        }
    }

Advanced Example: Twig, Annotations and the Web Debug Toolbar
-------------------------------------------------------------

The purpose of the ``MicroKernelTrait`` is *not* to have a single-file application.
Instead, its goal is to give you the power to choose your bundles and structure.

First, you'll probably want to put your PHP classes in an ``src/`` directory. Configure
your ``composer.json`` file to load from there:

.. code-block:: json

    {
        "require": {
            "...": "..."
        },
        "autoload": {
            "psr-4": {
                "App\\": "src/"
            }
        }
    }

Then, run ``composer dump-autoload`` to dump your new autoload config.

Now, suppose you want to use Twig and load routes via annotations. Instead of
putting *everything* in ``index.php``, create a new ``src/Kernel.php`` to
hold the kernel. Now it looks like this::

    // src/Kernel.php
    namespace App;

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        public function registerBundles(): array
        {
            $bundles = [
                new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new \Symfony\Bundle\TwigBundle\TwigBundle(),
            ];

            if ($this->getEnvironment() == 'dev') {
                $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            }

            return $bundles;
        }

        protected function configureContainer(ContainerConfigurator $containerConfigurator): void
        {
            $containerConfigurator->import(__DIR__.'/../config/framework.yaml');

            // register all classes in /src/ as service
            $containerConfigurator->services()
                ->load('App\\', __DIR__.'/*')
                ->autowire()
                ->autoconfigure()
            ;

            // configure WebProfilerBundle only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $containerConfigurator->extension('web_profiler', [
                    'toolbar' => true,
                    'intercept_redirects' => false,
                ]);
            }
        }

        protected function configureRoutes(RoutingConfigurator $routes): void
        {
            // import the WebProfilerRoutes, only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')->prefix('/_wdt');
                $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')->prefix('/_profiler');
            }

            // load the annotation routes
            $routes->import(__DIR__.'/Controller/', 'annotation');
        }

        // optional, to use the standard Symfony cache directory
        public function getCacheDir(): string
        {
            return __DIR__.'/../var/cache/'.$this->getEnvironment();
        }

        // optional, to use the standard Symfony logs directory
        public function getLogDir(): string
        {
            return __DIR__.'/../var/log';
        }
    }

Before continuing, run this command to add support for the new dependencies:

.. code-block:: terminal

    $ composer require symfony/yaml symfony/twig-bundle symfony/web-profiler-bundle doctrine/annotations

Unlike the previous kernel, this loads an external ``config/framework.yaml`` file,
because the configuration started to get bigger:

.. configuration-block::

    .. code-block:: yaml

        # config/framework.yaml
        framework:
            secret: S0ME_SECRET
            profiler: { only_exceptions: false }

    .. code-block:: xml

        <!-- config/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config secret="S0ME_SECRET">
                <framework:profiler only-exceptions="false"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework
                ->secret('SOME_SECRET')
                ->profiler()
                    ->onlyExceptions(false)
            ;
        };

This also loads annotation routes from an ``src/Controller/`` directory, which
has one file in it::

    // src/Controller/MicroController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    class MicroController extends AbstractController
    {
        #[Route('/random/{limit}')]
        public function randomNumber(int $limit): Response
        {
            $number = random_int(0, $limit);

            return $this->render('micro/random.html.twig', [
                'number' => $number,
            ]);
        }
    }

Template files should live in the ``templates/`` directory at the root of your project.
This template lives at ``templates/micro/random.html.twig``:

.. code-block:: html+twig

    <!-- templates/micro/random.html.twig -->
    <!DOCTYPE html>
    <html>
        <head>
            <title>Random action</title>
        </head>
        <body>
            <p>{{ number }}</p>
        </body>
    </html>

Finally, you need a front controller to boot and run the application. Create a
``public/index.php``::

    // public/index.php
    use App\Kernel;
    use Doctrine\Common\Annotations\AnnotationRegistry;
    use Symfony\Component\HttpFoundation\Request;

    $loader = require __DIR__.'/../vendor/autoload.php';
    // auto-load annotations
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);

    $kernel = new Kernel('dev', true);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

That's it! This ``/random/10`` URL will work, Twig will render, and you'll even
get the web debug toolbar to show up at the bottom. The final structure looks like
this:

.. code-block:: text

    your-project/
    ├─ config/
    │  └─ framework.yaml
    ├─ public/
    |  └─ index.php
    ├─ src/
    |  ├─ Controller
    |  |  └─ MicroController.php
    |  └─ Kernel.php
    ├─ templates/
    |  └─ micro/
    |     └─ random.html.twig
    ├─ var/
    |  ├─ cache/
    │  └─ log/
    ├─ vendor/
    │  └─ ...
    ├─ composer.json
    └─ composer.lock

As before you can use the :doc:`Symfony Local Web Server
</setup/symfony_server>`:

.. code-block:: terminal

    $ symfony server:start

Then visit the page in your browser: http://localhost:8000/random/10
