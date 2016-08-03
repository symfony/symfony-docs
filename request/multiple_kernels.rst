.. index::
   single: kernel, performance

How To Create Symfony Applications with Multiple Kernels
========================================================

In most Symfony applications, incoming requests are processed by the
``web/app.php`` front controller, which instantiates the ``app/AppKernel.php``
class to create the application kernel that loads the bundles and generates the
response.

This single kernel approach is a convenient default provided by the Symfony
Standard edition, but Symfony applications can define any number of kernels.
This is useful to execute the same application using a different configuration
and a different set of bundles for each kernel:

* An application that defines an API could create an ``ApiKernel`` to not have
  to load all the bundles enabled in the regular web application. This will
  improve the API performance;
* A bundle that doesn't allow multiple instances can define multiple
  configurations in different files loaded by each kernel.

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

Instead of creating the new front controller from scratch, it's recommended to
duplicate the existing ``web/app_dev.php`` and ``web/app.php`` files. For
example, you can create ``web/api_dev.php`` and ``web/api.php`` (or
``web/api/app_dev.php`` and ``web/api/app.php`` depending on your server
configuration).

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

Step 2) Create the new Kernel Class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now you need to define the ``ApiKernel`` class used by the new front controller.
The recommendation again is to duplicate the existing ``app/AppKernel.php`` file
and make the needed changes.

In this example, the changes of the new ``ApiKernel`` would be to load less
bundles than ``AppKernel`` and to change the location of the cache, logs and
config files to not mess with the regular application::

    // app/ApiKernel.php
    <?php

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
            return dirname(__DIR__).'/var/logs/api';
        }

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getRootDir().'/config/api/config_'.$this->getEnvironment().'.yml');
        }
    }

Step 3) Define the Kernel Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Finally, define the configuration used by the application when it executes the
new API kernel. According to the previous code, this config must be defined in
the ``app/config/api/`` directory.

The new configuration can be created from scratch when you load just a few
bundles, because it it will be very simple. Otherwise, duplicate the existing
config files or better, import them and override the needed options:

.. code-block:: yaml

    # app/config/api/config_dev.yml
    imports:
        - { resource: ../config_dev.yml }

    # override option values ...

Adding more Kernels to the Application
--------------------------------------

If your application is very complex and you create several kernels, it's better
to store them on their own directories instead of messing with lots of files in
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
