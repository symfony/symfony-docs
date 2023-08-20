How to Create Multiple Symfony Applications with a Single Kernel
================================================================

In Symfony applications, incoming requests are usually processed by the front
controller at ``public/index.php``, which instantiates the ``src/Kernel.php``
class to create the application kernel. This kernel loads the bundles, the
configuration, and handles the request to generate the response.

The current implementation of the Kernel class serves as a convenient default
for a single application. However, it can also manage multiple applications.
While the Kernel typically runs the same application with different
configurations based on various :ref:`environments <configuration-environments>`,
it can be adapted to run different applications with specific bundles and configuration.

These are some of the common use cases for creating multiple applications with a
single Kernel:

* An application that defines an API can be divided into two segments to improve
  performance. The first segment serves the regular web application, while the
  second segment exclusively responds to API requests. This approach requires
  loading fewer bundles and enabling fewer features for the second part, thus
  optimizing performance;
* A highly sensitive application could be divided into two parts for enhanced
  security. The first part would only load routes corresponding to the publicly
  exposed sections of the application. The second part would load the remainder
  of the application, with its access safeguarded by the web server;
* A monolithic application could be gradually transformed into a more
  distributed architecture, such as micro-services. This approach allows for a
  seamless migration of a large application while still sharing common
  configurations and components.

Turning a Single Application into Multiple Applications
-------------------------------------------------------

These are the steps required to convert a single application into a new one that
supports multiple applications:

1. Create a new application;
2. Update the Kernel class to support multiple applications;
3. Add a new ``APP_ID`` environment variable;
4. Update the front controllers.

The following example shows how to create a new application for the API of a new
Symfony project.

Step 1) Create a new Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This example follows the `Shared Kernel`_ pattern: all applications maintain an
isolated context, but they can share common bundles, configuration, and code if
desired. The optimal approach will depend on your specific needs and
requirements, so it's up to you to decide which best suits your project.

First, create a new ``apps`` directory at the root of your project, which will
hold all the necessary applications. Each application will follow a simplified
directory structure like the one described in :ref:`Symfony Best Practice </best_practices>`:

.. code-block:: text

    your-project/
    ├─ apps/
    │  └─ api/
    │     ├─ config/
    │     │  ├─ bundles.php
    │     │  ├─ routes.yaml
    │     │  └─ services.yaml
    │     └─ src/
    ├─ bin/
    │  └─ console
    ├─ config/
    ├─ public/
    │  └─ index.php
    ├─ src/
    │  └─ Kernel.php

.. note::

    Note that the ``config/`` and ``src/`` directories at the root of the
    project will represent the shared context among all applications within the
    ``apps/`` directory. Therefore, you should carefully consider what is
    common and what should be placed in the specific application.

.. tip::

    You might also consider renaming the namespace for the shared context, from
    ``App`` to ``Shared``, as it will make it easier to distinguish and provide
    clearer meaning to this context.

Since the new ``apps/api/src/`` directory will host the PHP code related to the
API, you have to update the ``composer.json`` file to include it in the autoload
section:

.. code-block:: json

    {
        "autoload": {
            "psr-4": {
                "Shared\\": "src/",
                "Api\\": "apps/api/src/"
            }
        }
    }

Additionally, don't forget to run ``composer dump-autoload`` to generate the
autoload files.

Step 2) Update the Kernel class to support Multiple Applications
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since there will be multiple applications, it's better to add a new property
``string $id`` to the Kernel to identify the application being loaded. This
property will also allow you to split the cache, logs, and configuration files
in order to avoid collisions with other applications. Moreover, it contributes
to performance optimization, as each application will load only the required
resources::

    // src/Kernel.php
    namespace Shared;

    // ...

    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;

        public function __construct(string $environment, bool $debug, private string $id)
        {
            parent::__construct($environment, $debug);
        }

        public function getSharedConfigDir(): string
        {
            return $this->getProjectDir().'/config';
        }

        public function getAppConfigDir(): string
        {
            return $this->getProjectDir().'/apps/'.$this->id.'/config';
        }

        public function registerBundles(): iterable
        {
            $sharedBundles = require $this->getSharedConfigDir().'/bundles.php';
            $appBundles = require $this->getAppConfigDir().'/bundles.php';

            // load common bundles, such as the FrameworkBundle, as well as
            // specific bundles required exclusively for the app itself
            foreach (array_merge($sharedBundles, $appBundles) as $class => $envs) {
                if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                    yield new $class();
                }
            }
        }

        public function getCacheDir(): string
        {
            // divide cache for each application
            return ($_SERVER['APP_CACHE_DIR'] ?? $this->getProjectDir().'/var/cache').'/'.$this->id.'/'.$this->environment;
        }

        public function getLogDir(): string
        {
            // divide logs for each application
            return ($_SERVER['APP_LOG_DIR'] ?? $this->getProjectDir().'/var/log').'/'.$this->id;
        }

        protected function configureContainer(ContainerConfigurator $container): void
        {
            // load common config files, such as the framework.yaml, as well as
            // specific configs required exclusively for the app itself
            $this->doConfigureContainer($container, $this->getSharedConfigDir());
            $this->doConfigureContainer($container, $this->getAppConfigDir());
        }

        protected function configureRoutes(RoutingConfigurator $routes): void
        {
            // load common routes files, such as the routes/framework.yaml, as well as
            // specific routes required exclusively for the app itself
            $this->doConfigureRoutes($routes, $this->getSharedConfigDir());
            $this->doConfigureRoutes($routes, $this->getAppConfigDir());
        }

        private function doConfigureContainer(ContainerConfigurator $container, string $configDir): void
        {
            $container->import($configDir.'/{packages}/*.{php,yaml}');
            $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

            if (is_file($configDir.'/services.yaml')) {
                $container->import($configDir.'/services.yaml');
                $container->import($configDir.'/{services}_'.$this->environment.'.yaml');
            } else {
                $container->import($configDir.'/{services}.php');
            }
        }

        private function doConfigureRoutes(RoutingConfigurator $routes, string $configDir): void
        {
            $routes->import($configDir.'/{routes}/'.$this->environment.'/*.{php,yaml}');
            $routes->import($configDir.'/{routes}/*.{php,yaml}');

            if (is_file($configDir.'/routes.yaml')) {
                $routes->import($configDir.'/routes.yaml');
            } else {
                $routes->import($configDir.'/{routes}.php');
            }

            if (false !== ($fileName = (new \ReflectionObject($this))->getFileName())) {
                $routes->import($fileName, 'annotation');
            }
        }
    }

This example reuses the default implementation to import the configuration and
routes based on a given configuration directory. As shown earlier, this
approach will import both the shared and the app-specific resources.

Step 3) Add a new APP_ID environment variable
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Next, define a new environment variable that identifies the current application.
This new variable can be added to the ``.env`` file to provide a default value,
but it should typically be added to your web server configuration.

.. code-block:: bash

    # .env
    APP_ID=api

.. caution::

    The value of this variable must match the application directory within
    ``apps/`` as it is used in the Kernel to load the specific application
    configuration.

Step 4) Update the Front Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In this final step, update the front controllers ``public/index.php`` and
``bin/console`` to pass the value of the ``APP_ID`` variable to the Kernel
instance. This will allow the Kernel to load and run the specified
application::

    // public/index.php
    use Shared\Kernel;
    // ...

    return function (array $context): Kernel {
        return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG'], $context['APP_ID']);
    };

Similar to configuring the required ``APP_ENV`` and ``APP_DEBUG`` values, the
third argument of the Kernel constructor is now also necessary to set the
application ID, which is derived from an external configuration.

For the second front controller, define a new console option to allow passing
the application ID to run under CLI context::

    // bin/console
    use Shared\Kernel;
    // ...

    return function (InputInterface $input, array $context): Application {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG'], $input->getParameterOption(['--id', '-i'], $context['APP_ID']));

        $application = new Application($kernel);
        $application->getDefinition()
            ->addOption(new InputOption('--id', '-i', InputOption::VALUE_REQUIRED, 'The App ID'))
        ;

        return $application;
    };

That's it!

Executing Commands
------------------

The ``bin/console`` script, which is used to run Symfony commands, always uses
the ``Kernel`` class to build the application and load the commands. If you
need to run console commands for a specific application, you can provide the
``--id`` option along with the appropriate identity value:

.. code-block:: terminal

    php bin/console cache:clear --id=api
    // or
    php bin/console cache:clear -iapi

    // alternatively
    export APP_ID=api
    php bin/console cache:clear

You might want to update the composer auto-scripts section to run multiple
commands simultaneously. This example shows the commands of two different
applications called ``api`` and ``admin``:

.. code-block:: json

    {
        "scripts": {
            "auto-scripts": {
                "cache:clear -iapi": "symfony-cmd",
                "cache:clear -iadmin": "symfony-cmd",
                "assets:install %PUBLIC_DIR% -iapi": "symfony-cmd",
                "assets:install %PUBLIC_DIR% -iadmin --no-cleanup": "symfony-cmd"
            }
        }
    }

Then, run ``composer auto-scripts`` to test it!

.. note::

    The commands available for each console script (e.g. ``bin/console -iapi``
    and ``bin/console -iadmin``) can differ because they depend on the bundles
    enabled for each application, which could be different.

Rendering Templates
-------------------

Let's consider that you need to create another app called ``admin``. If you
follow the :ref:`Symfony Best Practices </best_practices>`, the shared Kernel
templates will be located in the ``templates/`` directory at the project's root.
For admin-specific templates, you can create a new directory
``apps/admin/templates/`` which you will need to manually configure under the
Admin application:

.. code-block:: yaml

    # apps/admin/config/packages/twig.yaml
    twig:
        paths:
            '%kernel.project_dir%/apps/admin/templates': Admin

Then, use this Twig namespace to reference any template within the Admin
application only, for example ``@Admin/form/fields.html.twig``.

Running Tests
-------------

In Symfony applications, functional tests typically extend from
the :class:`Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase` class by
default. Within its parent class, ``KernelTestCase``, there is a method called
``createKernel()`` that attempts to create the kernel responsible for running
the application during tests. However, the current logic of this method doesn't
include the new application ID argument, so you need to update it::

    // apps/api/tests/ApiTestCase.php
    namespace Api\Tests;

    use Shared\Kernel;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\HttpKernel\KernelInterface;

    class ApiTestCase extends WebTestCase
    {
        protected static function createKernel(array $options = []): KernelInterface
        {
            $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
            $debug = $options['debug'] ?? (bool) ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true);

            return new Kernel($env, $debug, 'api');
        }
    }

.. note::

    This examples uses a hardcoded application ID value because the tests
    extending this ``ApiTestCase`` class will focus solely on the ``api`` tests.

Now, create a ``tests/`` directory inside the ``apps/api/`` application. Then,
update both the ``composer.json`` file and ``phpunit.xml`` configuration about
its existence:

.. code-block:: json

    {
        "autoload-dev": {
            "psr-4": {
                "Shared\\Tests\\": "tests/",
                "Api\\Tests\\": "apps/api/tests/"
            }
        }
    }

Remember to run ``composer dump-autoload`` to generate the autoload files.

And, here is the update needed for the ``phpunit.xml`` file:

.. code-block:: xml

    <testsuites>
        <testsuite name="shared">
            <directory>tests</directory>
        </testsuite>
        <testsuite name="api">
            <directory>apps/api/tests</directory>
        </testsuite>
    </testsuites>

Adding more Applications
------------------------

Now you can begin adding more applications as needed, such as an ``admin``
application to manage the project's configuration and permissions. To do that,
you will have to repeat the step 1 only:

.. code-block:: text

    your-project/
    ├─ apps/
    │  ├─ admin/
    │  │  ├─ config/
    │  │  │  ├─ bundles.php
    │  │  │  ├─ routes.yaml
    │  │  │  └─ services.yaml
    │  │  └─ src/
    │  └─ api/
    │     └─ ...

Additionally, you might need to update your web server configuration to set the
``APP_ID=admin`` under a different domain.

.. _`Shared Kernel`: http://ddd.fed.wiki.org/view/shared-kernel
