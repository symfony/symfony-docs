.. index::
   single: kernel, performance

How To Create Symfony Applications with Multiple Kernels
========================================================

In most Symfony applications, incoming requests are processed by the
``web/app.php`` front controller, which instantiates the ``app/AppKernel.php``
class to create the application kernel that loads the bundles and handles the
request to generate the response.

This single kernel approach is a convenient default provided by the Symfony
Standard edition, but Symfony applications can define any number of kernels.
Whereas :doc:`environments </configuration/environments>` execute the same
application with different configurations, kernels can execute different parts
of the same application.

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
duplicate the existing ones. For example, create ``web/api_dev.php`` from
``web/app_dev.php`` and ``web/api.php`` from ``web/app.php``.

Then, update the code of the new front controllers to instantiate the new kernel
class instead of the usual ``AppKernel`` class::

    // web/api.php
    // ...
    $kernel = new ApiKernel('prod', false);
    // ...

    // web/api_dev.php
    // ...
    $kernel = new ApiKernel('dev', true);
    // ...

.. tip::

    Another approach is to keep the existing front controller (e.g. ``app.php`` and
    ``app_dev.php``), but add an ``if`` statement to load the different kernel based
    on the URL (e.g. if the URL starts with ``/api``, use the ``ApiKernel``).

Step 2) Create the new Kernel Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you need to define the ``ApiKernel`` class used by the new front controller.
The easiest way to do this is by duplicating the existing  ``app/AppKernel.php``
file and make the needed changes.

In this example, the ``ApiKernel`` will load less bundles than AppKernel. Be
sure to also change the location of the cache, logs and configuration files so
they don't collide with the files from ``AppKernel``::

    // app/ApiKernel.php
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class ApiKernel extends Kernel
    {
        public function registerBundles()
        {
            // load only the bundles strictly needed for the API...
        }

        public function getCacheDir()
        {
            return dirname(__DIR__).'/var/cache/api/'.$this->getEnvironment();
        }

        public function getLogDir()
        {
            return dirname(__DIR__).'/var/log/api';
        }

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getProjectDir().'/app/config/api/config_'.$this->getEnvironment().'.yml');
        }
    }

In order for the autoloader to find your new ``ApiKernel``, make sure you add it
to your ``composer.json`` autoload section:


.. code-block:: json

    {
        "...": "..."

        "autoload": {
            "psr-4": { "": "src/" },
            "classmap": [ "app/AppKernel.php", "app/AppCache.php", "app/ApiKernel.php" ]
        }
    }

Then, run ``composer install`` to dump your new autoload config.

Step 3) Define the Kernel Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Finally, define the configuration files that the new ``ApiKernel`` will load.
According to the above code, this config will live in the ``app/config/api/``
directory.

The new configuration can be created from scratch when you load just a few
bundles, because it will be very simple. Otherwise, duplicate the existing
config files or better, import them and override the needed options:

.. code-block:: yaml

    # app/config/api/config_dev.yml
    imports:
        - { resource: ../config_dev.yml }

    # override option values ...

Executing Commands with a Different Kernel
------------------------------------------

The ``bin/console`` script used to run Symfony commands always uses the default
``AppKernel`` class to build the application and load the commands. If you need
to execute console commands using the new kernel, duplicate the ``bin/console``
script and rename it (e.g. ``bin/api``).

Then, replace the ``AppKernel`` instantiation by your own kernel instantiation
(e.g. ``ApiKernel``) and now you can execute commands using the new kernel
(e.g. ``php bin/api cache:clear``) Now you can use execute commands using the
new kernel.

.. note::

    The commands available for each console script (e.g. ``bin/console`` and
    ``bin/api``) can differ because they depend on the bundles enabled for each
    kernel, which could be different.

Rendering Templates Defined in a Different Kernel
-------------------------------------------------

If you follow the Symfony Best Practices, the templates of the default kernel
will be stored in ``app/Resources/views/``. Trying to render those templates in
a different kernel will result in a *There are no registered paths for
namespace "__main__"* error.

In order to solve this issue, add the following configuration to your kernel:

.. code-block:: yaml

    # api/config/config.yml
    twig:
        paths:
            # allows to use app/Resources/views/ templates in the ApiKernel
            "%kernel.project_dir%/app/Resources/views": ~

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
            return 'ApiKernel';
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
the default ``app/`` directory:

.. code-block:: text

    project/
    ├─ app/
    │  ├─ ...
    │  ├─ config/
    │  └─ AppKernel.php
    ├─ api/
    │  ├─ ...
    │  ├─ config/
    │  └─ ApiKernel.php
    ├─ ...
    └─ web/
        ├─ ...
        ├─ app.php
        ├─ app_dev.php
        ├─ api.php
        └─ api_dev.php
