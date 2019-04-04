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

Next, create an ``index.php`` file that defines the kernel class and executes it::

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    use Symfony\Component\Routing\RouteCollectionBuilder;

    require __DIR__.'/vendor/autoload.php';

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        public function registerBundles()
        {
            return [
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle()
            ];
        }

        protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
        {
            // PHP equivalent of config/packages/framework.yaml
            $c->loadFromExtension('framework', [
                'secret' => 'S0ME_SECRET'
            ]);
        }

        protected function configureRoutes(RouteCollectionBuilder $routes)
        {
            // kernel is a service that points to this class
            // optional 3rd argument is the route name
            $routes->add('/random/{limit}', 'kernel::randomNumber');
        }

        public function randomNumber($limit)
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

That's it! To test it, you can start the built-in web server:

.. code-block:: terminal

    $ php -S localhost:8000

Then see the JSON response in your browser:

    http://localhost:8000/random/10

The Methods of a "Micro" Kernel
-------------------------------

When you use the ``MicroKernelTrait``, your kernel needs to have exactly three methods
that define your bundles, your services and your routes:

**registerBundles()**
    This is the same ``registerBundles()`` that you see in a normal kernel.

**configureContainer(ContainerBuilder $c, LoaderInterface $loader)**
    This method builds and configures the container. In practice, you will use
    ``loadFromExtension`` to configure different bundles (this is the equivalent
    of what you see in a normal ``config/packages/*`` file). You can also register
    services directly in PHP or load external configuration files (shown below).

**configureRoutes(RouteCollectionBuilder $routes)**
    Your job in this method is to add routes to the application. The
    ``RouteCollectionBuilder`` has methods that make adding routes in PHP more
    fun. You can also load external routing files (shown below).

Advanced Example: Twig, Annotations and the Web Debug Toolbar
-------------------------------------------------------------

The purpose of the ``MicroKernelTrait`` is *not* to have a single-file application.
Instead, its goal to give you the power to choose your bundles and structure.

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
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    use Symfony\Component\Routing\RouteCollectionBuilder;

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        public function registerBundles()
        {
            $bundles = [
                new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new \Symfony\Bundle\TwigBundle\TwigBundle(),
            ];

            if ($this->getEnvironment() == 'dev') {
                $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            }

            return $bundles;
        }

        protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/../config/framework.yaml');

            // configure WebProfilerBundle only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $c->loadFromExtension('web_profiler', [
                    'toolbar' => true,
                    'intercept_redirects' => false,
                ]);
            }
        }

        protected function configureRoutes(RouteCollectionBuilder $routes)
        {
            // import the WebProfilerRoutes, only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml', '/_wdt');
                $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml', '/_profiler');
            }

            // load the annotation routes
            $routes->import(__DIR__.'/../src/Controller/', '/', 'annotation');
        }

        // optional, to use the standard Symfony cache directory
        public function getCacheDir()
        {
            return __DIR__.'/../var/cache/'.$this->getEnvironment();
        }

        // optional, to use the standard Symfony logs directory
        public function getLogDir()
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
        $container->loadFromExtension('framework', [
            'secret' => 'S0ME_SECRET',
            'profiler' => [
                'only_exceptions' => false,
            ],
        ]);

This also loads annotation routes from an ``src/Controller/`` directory, which
has one file in it::

    // src/Controller/MicroController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;

    class MicroController extends AbstractController
    {
        /**
         * @Route("/random/{limit}")
         */
        public function randomNumber($limit)
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

As before you can use PHP built-in server:

.. code-block:: terminal

    cd public/
    $ php -S localhost:8000 -t public/

Then see webpage in browser:

    http://localhost:8000/random/10
