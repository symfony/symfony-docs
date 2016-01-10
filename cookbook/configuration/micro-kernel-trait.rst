Building your own Framework with the MicroKernelTrait
=====================================================

A :ref:`traditional Symfony app <installation-creating-the-app>` contains a sensible
directory structure, various configuration files and an ``AppKernel`` with several
bundles already-registered. This is a fully-featured app that's ready to go.

But did you know, you can create a fully-functional Symfony application in as little
as one file? This is possible thanks to the new
:class:`Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait`. This allows
you to start with a tiny application, and then add features and structure as you
need to.

A Single-File Symfony Application
---------------------------------

Start with a completely empty directory. Get ``symfony/symfony`` as a dependency
via Composer:

.. code-block:: bash

    $ composer require symfony/symfony

Next, create an ``index.php`` file that creates a kernel class and executes it::

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Routing\RouteCollectionBuilder;

    // require Composer's autoloader
    require __DIR__.'/vendor/autoload.php';

    class AppKernel extends Kernel
    {
        use MicroKernelTrait;

        public function registerBundles()
        {
            return array(
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle()
            );
        }

        protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
        {
            // PHP equivalent of config.yml
            $c->loadFromExtension('framework', array(
                'secret' => 'S0ME_SECRET'
            ));
        }

        protected function configureRoutes(RouteCollectionBuilder $routes)
        {
            // kernel is a service that points to this class
            // optional 3rd argument is the route name
            $routes->add('/random/{limit}', 'kernel:randomAction');
        }

        public function randomAction($limit)
        {
            return new JsonResponse(array(
                'number' => rand(0, $limit)
            ));
        }
    }

    $kernel = new AppKernel('dev', true);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

That's it! To test it, you can start the built-in web server:

.. code-block:: bash

    $ php -S localhost:8000

Then see the JSON response in your browser:

> http://localhost:8000/random/10

The Methods of a "Micro" Kernel
-------------------------------

When you use the ``MicroKernelTrait``, your kernel needs to have exactly three methods
that define your bundles, your services and your routes:

**registerBundles()**
    This is the same ``registerBundles()`` that you see in a normal kernel.

**configureContainer(ContainerBuilder $c, LoaderInterface $loader)**
    This methods builds and configures the container. In practice, you will use
    ``loadFromExtension`` to configure different bundles (this is the equivalent
    of what you see in a normal ``config.yml`` file). You can also register services
    directly in PHP or load external configuration files (shown below).

**configureRoutes(RouteCollectionBuilder $routes)**
    Your job in this method is to add routes to the application. The ``RouteCollectionBuilder``
    is new in Symfony 2.8 and has methods that make adding routes in PHP more fun.
    You can also load external routing files (shown below).


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
                "": "src/"
            }
        }
    }

Now, suppose you want to use Twig and load routes via annotations. For annotation
routing, you need SensioFrameworkExtraBundle. This comes with a normal Symfony project.
But in this case, you need to download it:

.. code-block:: bash

    $ composer require sensio/framework-extra-bundle

Instead of putting *everything* in ``index.php``, create a new ``app/AppKernel.php``
to hold the kernel. Now it looks like this::

    // app/AppKernel.php

    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Routing\RouteCollectionBuilder;
    use Doctrine\Common\Annotations\AnnotationRegistry;

    // require Composer's autoloader
    $loader = require __DIR__.'/../vendor/autoload.php';
    // auto-load annotations
    AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

    class AppKernel extends Kernel
    {
        use MicroKernelTrait;

        public function registerBundles()
        {
            $bundles = array(
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new Symfony\Bundle\TwigBundle\TwigBundle(),
                new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle()
            );

            if ($this->getEnvironment() == 'dev') {
                $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            }

            return $bundles;
        }

        protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config/config.yml');

            // configure WebProfilerBundle only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $c->loadFromExtension('web_profiler', array(
                    'toolbar' => true,
                    'intercept_redirects' => false,
                ));
            }
        }

        protected function configureRoutes(RouteCollectionBuilder $routes)
        {
            // import the WebProfilerRoutes, only if the bundle is enabled
            if (isset($this->bundles['WebProfilerBundle'])) {
                $routes->mount('/_wdt', $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml'));
                $routes->mount('/_profiler', $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml'));
            }

            // load the annotation routes
            $routes->mount(
                '/',
                $routes->import(__DIR__.'/../src/App/Controller/', 'annotation')
            );
        }
    }

Unlike the previous kernel, this loads an external ``app/config/config.yml`` file,
because the configuration started to get bigger:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            secret: S0ME_SECRET
            templating:
                engines: ['twig']
            profiler: { only_exceptions: false }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config secret="S0ME_SECRET">
                <framework:templating>
                    <framework:engine>twig</framework:engine>
                </framework:templating>
                <framework:profiler only-exceptions="false" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'secret' => 'S0ME_SECRET',
            'templating' => array(
                'engines' => array('twig'),
            ),
            'profiler' => array(
                'only_exceptions' => false,
            ),
        ));

This also loads annotation routes from an ``src/App/Controller/`` directory, which
has one file in it::

    // src/App/Controller/MicroController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class MicroController extends Controller
    {
        /**
         * @Route("/random/{limit}")
         */
        public function randomAction($limit)
        {
            $number = rand(0, $limit);

            return $this->render('micro/random.html.twig', array(
                'number' => $number
            ));
        }
    }

Template files should live in the ``Resources/views`` directory of whatever directory
your *kernel* lives in. Since ``AppKernel`` lives in ``app/``, this template lives
at ``app/Resources/views/micro/random.html.twig``.

Finally, you need a front controller to boot and run the application. Create a
``web/index.php``::

    // web/index.php

    use Symfony\Component\HttpFoundation\Request;

    require __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

That's it! This ``/random/10`` URL will work, Twig will render, and you'll even
get the web debug toolbar to show up at the bottom. The final structure looks like
this:

.. code-block:: text

    your-project/
    ├─ app/
    |  ├─ AppKernel.php
    │  ├─ cache/
    │  ├─ config/
    │  ├─ logs/
    │  └─ Resources
    |     └─ views
    |        ├─ base.html.twig
    |        └─ micro
    |           └─ random.html.twig
    ├─ src/
    │  └─ App
    |     └─ Controller
    |        └─ MicroController.php
    ├─ vendor/
    │  └─ ...
    ├─ web/
    |  └─ index.php
    ├─ composer.json
    └─ composer.lock

Hey, that looks a lot like a *traditional* Symfony application! You're right: the
``MicroKernelTrait`` *is* still Symfony: but you can control your structure and
features quite easily.
