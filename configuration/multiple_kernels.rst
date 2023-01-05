.. index::
   single: kernel, performance

How To Create Symfony Applications with Multiple Kernels
========================================================

.. caution::

    Creating applications with multiple kernels is no longer recommended by
    Symfony. Consider creating multiple small applications instead.

In most Symfony applications, incoming requests are processed by the
``public/index.php`` front controller, which instantiates the ``src/Kernel.php``
class to create the application kernel that loads the bundles and handles the
request to generate the response.

This single kernel approach is a convenient default, but Symfony applications
can define any number of kernels. Whereas
:ref:`environments <configuration-environments>` run the same application with
different configurations, kernels can run different parts of the same
application.

These are some of the common use cases for creating multiple kernels:

* An application that defines an API could define two kernels for performance
  reasons. The first kernel would serve the regular application and the second
  one would only respond to the API requests, loading less bundles and enabling
  less features;
* A highly sensitive application could define two kernels. The first one would
  only load the routes that match the parts of the application exposed publicly.
  The second kernel would load the rest of the application and its access would
  be protected by the web server;
* A micro-services oriented application could define several kernels to
  enable/disable services selectively turning a traditional monolith application
  into several micro-applications.

Adding a new Kernel to the Application
--------------------------------------

Creating a new kernel in a Symfony application is a three-step process:

1. Create a new front controller to load the new kernel;
2. Create the new kernel class;
3. Define the configuration loaded by the new kernel.

The following example shows how to create a new kernel for the API of a given
Symfony application.

Step 1) Create a new Front Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Instead of creating the new front controller from scratch, it's easier to
duplicate the existing one. For example, create ``public/api.php`` from
``public/index.php``.

Then, update the code of the new front controller to instantiate the new kernel
class instead of the usual ``Kernel`` class::

    // public/api.php
    // ...
    $kernel = new ApiKernel(
        $_SERVER['APP_ENV'] ?? 'dev',
        $_SERVER['APP_DEBUG'] ?? ('prod' !== ($_SERVER['APP_ENV'] ?? 'dev'))
    );
    // ...

.. tip::

    Another approach is to keep the existing ``index.php`` front controller, but
    add an ``if`` statement to load the different kernel based on the URL (e.g.
    if the URL starts with ``/api``, use the ``ApiKernel``).

Step 2) Create the new Kernel Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you need to define the ``ApiKernel`` class used by the new front controller.
The easiest way to do this is by duplicating the existing  ``src/Kernel.php``
file and make the needed changes.

In this example, the ``ApiKernel`` will load fewer bundles than the default
Kernel. Be sure to also change the location of the cache, logs and configuration
files so they don't collide with the files from ``src/Kernel.php``::

    // src/ApiKernel.php
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

    class ApiKernel extends Kernel
    {
        use MicroKernelTrait;

        public function getProjectDir(): string
        {
            return \dirname(__DIR__);
        }

        public function getCacheDir(): string
        {
            return $this->getProjectDir().'/var/cache/api/'.$this->environment;
        }

        public function getLogDir(): string
        {
            return $this->getProjectDir().'/var/log/api';
        }

        protected function configureContainer(ContainerConfigurator $containerConfigurator): void
        {
            $containerConfigurator->import('../config/api/{packages}/*.yaml');
            $containerConfigurator->import('../config/api/{packages}/'.$this->environment.'/*.yaml');

            if (is_file(\dirname(__DIR__).'/config/api/services.yaml')) {
                $containerConfigurator->import('../config/api/services.yaml');
                $containerConfigurator->import('../config/api/{services}_'.$this->environment.'.yaml');
            } else {
                $containerConfigurator->import('../config/api/{services}.php');
            }
        }

        protected function configureRoutes(RoutingConfigurator $routes): void
        {
            $routes->import('../config/api/{routes}/'.$this->environment.'/*.yaml');
            $routes->import('../config/api/{routes}/*.yaml');
            // ... load only the config routes strictly needed for the API
        }

        // If you need to run some logic to decide which bundles to load,
        // you might prefer to use the registerBundles() method instead
        private function getBundlesPath(): string
        {
            // load only the bundles strictly needed for the API
            return $this->getProjectDir().'/config/api_bundles.php';
        }
    }

.. versionadded:: 5.4

    The ``getBundlesPath()`` method was introduced in Symfony 5.4.

Step 3) Define the Kernel Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Finally, define the configuration files that the new ``ApiKernel`` will load.
According to the above code, this config will live in one or multiple files
stored in ``config/api/`` and ``config/api/ENVIRONMENT_NAME/`` directories.

The new configuration files can be created from scratch when you load only a few
bundles, because it will be small. Otherwise, duplicate the existing
config files in ``config/packages/`` or better, import them and override the
needed options.

Executing Commands with a Different Kernel
------------------------------------------

The ``bin/console`` script used to run Symfony commands always uses the default
``Kernel`` class to build the application and load the commands. If you need
to run console commands using the new kernel, duplicate the ``bin/console``
script and rename it (e.g. ``bin/api``).

Then, replace the ``Kernel`` instance by your own kernel instance
(e.g. ``ApiKernel``). Now you can run commands using the new kernel
(e.g. ``php bin/api cache:clear``).

.. note::

    The commands available for each console script (e.g. ``bin/console`` and
    ``bin/api``) can differ because they depend on the bundles enabled for each
    kernel, which could be different.

Rendering Templates Defined in a Different Kernel
-------------------------------------------------

If you follow the Symfony Best Practices, the templates of the default kernel
will be stored in ``templates/``. Trying to render those templates in a
different kernel will result in a *There are no registered paths for namespace
"__main__"* error.

In order to solve this issue, add the following configuration to your kernel:

.. code-block:: yaml

    # config/api/twig.yaml
    twig:
        paths:
            # allows to use api/templates/ dir in the ApiKernel
            "%kernel.project_dir%/api/templates": ~

Running Tests Using a Different Kernel
--------------------------------------

In Symfony applications, functional tests extend by default from the
:class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase` class. Inside that
class, a method called ``getKernelClass()`` tries to find the class of the kernel
to use to run the application during tests. The logic of this method does not
support multiple kernel applications, so your tests won't use the right kernel.

The solution is to create a custom base class for functional tests extending
from ``WebTestCase`` class and overriding the ``getKernelClass()`` method to
return the fully qualified class name of the kernel to use::

    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

    // tests needing the ApiKernel to work, now must extend this
    // ApiTestCase class instead of the default WebTestCase class
    class ApiTestCase extends WebTestCase
    {
        protected static function getKernelClass()
        {
            return 'App\ApiKernel';
        }

        // this is needed because the KernelTestCase class keeps a reference to
        // the previously created kernel in its static $kernel property. Thus,
        // if your functional tests do not run in isolated processes, a later run
        // test for a different kernel will reuse the previously created instance,
        // which points to a different kernel
        protected function tearDown()
        {
            parent::tearDown();

            static::$class = null;
        }
    }

Adding more Kernels to the Application
--------------------------------------

If your application is very complex and you create several kernels, it's better
to store them in their own directories instead of messing with lots of files in
the default ``src/`` directory:

.. code-block:: text

    project/
    ├─ src/
    │  ├─ ...
    │  └─ Kernel.php
    ├─ api/
    │  ├─ ...
    │  └─ ApiKernel.php
    ├─ ...
    └─ public/
        ├─ ...
        ├─ api.php
        └─ index.php
