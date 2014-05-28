.. index::
    single: Configuration, Environments

How to Organize Configuration Files
===================================

The default Symfony2 Standard Edition defines three
:doc:`execution environments </cookbook/configuration/environments>` called
``dev``, ``prod``, and ``test``. An environment simply represents a way to
execute the same codebase with different configuration.

In order to select the configuration file to load for each environment, Symfony
executes the ``registerContainerConfiguration()`` method of the ``AppKernel``
class::

    // app/AppKernel.php
    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        }
    }

This method loads the ``app/config/config_dev.yml`` file for the ``dev``
environment and so on. In turn, this file loads the common configuration file
located at ``app/config/config.yml``. Therefore, the configuration files of the
default Symfony Standard Edition follow this structure:

.. code-block:: text

    <your-project>/
    ├─ app/
    │  └─ config/
    │     ├─ config.yml
    │     ├─ config_dev.yml
    │     ├─ config_prod.yml
    │     ├─ config_test.yml
    │     ├─ parameters.yml
    │     ├─ parameters.yml.dist
    │     ├─ routing.yml
    │     ├─ routing_dev.yml
    │     └─ security.yml
    ├─ src/
    ├─ vendor/
    └─ web/

This default structure was choosen for its simplicity — one file per environment.
But as any other Symfony feature, you can customize it to better suit your needs.
The following sections explain different ways to organize your configuration
files. In order to simplify the examples, only the ``dev`` and ``prod``
environments are taken into account.

Different Directories per Environment
-------------------------------------

Instead of suffixing the files with ``_dev`` and ``_prod``, this technique
groups all the related configuration files under a directory with the same
name as the environment:

.. code-block:: text

    <your-project>/
    ├─ app/
    │  └─ config/
    │     ├─ common/
    │     │  ├─ config.yml
    │     │  ├─ parameters.yml
    │     │  ├─ routing.yml
    │     │  └─ security.yml
    │     ├─ dev/
    │     │  ├─ config.yml
    │     │  ├─ parameters.yml
    │     │  ├─ routing.yml
    │     │  └─ security.yml
    │     └─ prod/
    │        ├─ config.yml
    │        ├─ parameters.yml
    │        ├─ routing.yml
    │        └─ security.yml
    ├─ src/
    ├─ vendor/
    └─ web/

To make it work, change the code of the ``registerContainerConfiguration()``
method::

    // app/AppKernel.php
    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config/'.$this->getEnvironment().'/config.yml');
        }
    }

Then, make sure that each ``config.yml`` file loads the rest of the configuration
files, including the common files:

.. code-block:: yaml

    # app/config/dev/config.yml
    imports:
        - { resource: '../config.yml'  }
        - { resource: 'parameters.yml' }
        - { resource: 'security.yml'   }

    # ...

    # app/config/prod/config.yml
    imports:
        - { resource: '../config.yml'  }
        - { resource: 'parameters.yml' }
        - { resource: 'security.yml'   }

    # ...


    # app/config/common/config.yml
    imports:
        - { resource: 'parameters.yml' }
        - { resource: 'security.yml'   }

    # ...


Semantic Configuration Files
----------------------------

A different organization strategy may be needed for complex applications with
large configuration files. You could for instance create one file per bundle
and several files to define all the application services:

.. code-block:: text

    <your-project>/
    ├─ app/
    │  └─ config/
    │     ├─ bundles/
    │     │  ├─ bundle1.yml
    │     │  ├─ bundle2.yml
    │     │  ├─ ...
    │     │  └─ bundleN.yml
    │     ├─ environments/
    │     │  ├─ common.yml
    │     │  ├─ dev.yml
    │     │  └─ prod.yml
    │     ├─ routing/
    │     │  ├─ common.yml
    │     │  ├─ dev.yml
    │     │  └─ prod.yml
    │     └─ services/
    │        ├─ frontend.yml
    │        ├─ backend.yml
    │        ├─ ...
    │        └─ security.yml
    ├─ src/
    ├─ vendor/
    └─ web/

Again, change the code of the ``registerContainerConfiguration()`` method to
make Symfony aware of the new file organization::

    // app/AppKernel.php
    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load(__DIR__.'/config/environments/'.$this->getEnvironment().'.yml');
        }
    }

Advanced Tecniques
------------------

Symfony loads configuration files using the ``Config component </components/config>``,
which provides some advanced features.

Mix and Match Configuration Formats
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration files can import files defined with any other built-in configuration
format (``.yml``, ``.xml``, ``.php``, ``.ini``):

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: 'parameters.yml' }
        - { resource: 'services.xml'   }
        - { resource: 'security.yml'   }
        - { resource: 'legacy.php'     }

    # ...

If you use any other configuration format, you have to define your own loader
class extending it from ``Symfony\Component\DependencyInjection\Loader\FileLoader``.
When the configuration values are dynamic, you can use the PHP configuration
file to execute your own logic. In addition, you can define your own services
to load configuration from databases and web services.

Directory Loading
~~~~~~~~~~~~~~~~~

Splitting configuration into lots of smaller files can rapidly become cumbersome
when importing those files from the main configuration file. Avoid these problems
by loading an entire directory:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: 'bundles/'   }
        - { resource: 'services/'  }

    # ...

The Config component will look for recursively in the ``bundles/`` and ``services/``
directories and it will load any supported file format (``.yml``, ``.xml``,
``.php``, ``.ini``).

Global Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~

Some system administrators may prefer to store sensitive parameteres in global
configuration files under the ``/etc`` directory. Imagine that the database
credentials for your website are stored in the ``/etc/sites/mysite.com/parameters.yml``.
Loading this file is as simple as indicating the full file path when importing
it from any other configuration file:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: 'parameters.yml'   }
        - { resource: '/etc/sites/mysite.com/parameters.yml'  }

    # ...

Most of the time, local developers won't have the same files that exist in the
production servers. For that reason, the Config component provides the
``ignore_errors`` option to silently discard errors when the loaded file
doesn't exist:

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: 'parameters.yml'   }
        - { resource: '/etc/sites/mysite.com/parameters.yml', ignore_errors: true  }

    # ...
