.. index::
    single: Configuration

How to Organize Configuration Files
===================================

The default Symfony Standard Edition defines three
:doc:`execution environments </cookbook/configuration/environments>` called
``dev``, ``prod`` and ``test``. An environment simply represents a way to
execute the same codebase with different configurations.

In order to select the configuration file to load for each environment, Symfony
executes the ``registerContainerConfiguration()`` method of the ``AppKernel``
class::

    // app/AppKernel.php
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
        }
    }

This method loads the ``app/config/config_dev.yml`` file for the ``dev``
environment and so on. In turn, this file loads the common configuration file
located at ``app/config/config.yml``. Therefore, the configuration files of the
default Symfony Standard Edition follow this structure:

.. code-block:: text

    your-project/
    ├─ app/
    │  ├─ ...
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
    ├─ ...

This default structure was chosen for its simplicity — one file per environment.
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

    your-project/
    ├─ app/
    │  ├─ ...
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
    ├─ ...

To make this work, change the code of the
:method:`Symfony\\Component\\HttpKernel\\KernelInterface::registerContainerConfiguration`
method::

    // app/AppKernel.php
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getRootDir().'/config/'.$this->getEnvironment().'/config.yml');
        }
    }

Then, make sure that each ``config.yml`` file loads the rest of the configuration
files, including the common files. For instance, this would be the imports
needed for the ``app/config/dev/config.yml`` file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/dev/config.yml
        imports:
            - { resource: '../common/config.yml' }
            - { resource: 'parameters.yml' }
            - { resource: 'security.yml' }

        # ...

    .. code-block:: xml

        <!-- app/config/dev/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="../common/config.xml" />
                <import resource="parameters.xml" />
                <import resource="security.xml" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/dev/config.php
        $loader->import('../common/config.php');
        $loader->import('parameters.php');
        $loader->import('security.php');

        // ...

.. include:: /components/dependency_injection/_imports-parameters-note.rst.inc

Semantic Configuration Files
----------------------------

A different organization strategy may be needed for complex applications with
large configuration files. For instance, you could create one file per bundle
and several files to define all application services:

.. code-block:: text

    your-project/
    ├─ app/
    │  ├─ ...
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
    ├─ ...

Again, change the code of the ``registerContainerConfiguration()`` method to
make Symfony aware of the new file organization::

    // app/AppKernel.php
    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;

    class AppKernel extends Kernel
    {
        // ...

        public function registerContainerConfiguration(LoaderInterface $loader)
        {
            $loader->load($this->getRootDir().'/config/environments/'.$this->getEnvironment().'.yml');
        }
    }

Following the same technique explained in the previous section, make sure to
import the appropriate configuration files from each main file (``common.yml``,
``dev.yml`` and ``prod.yml``).

Advanced Techniques
-------------------

Symfony loads configuration files using the
:doc:`Config component </components/config/introduction>`, which provides some
advanced features.

Mix and Match Configuration Formats
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration files can import files defined with any other built-in configuration
format (``.yml``, ``.xml``, ``.php``, ``.ini``):

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: 'parameters.yml' }
            - { resource: 'services.xml' }
            - { resource: 'security.yml' }
            - { resource: 'legacy.php' }

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="services.xml" />
                <import resource="security.yml" />
                <import resource="legacy.php" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $loader->import('parameters.yml');
        $loader->import('services.xml');
        $loader->import('security.yml');
        $loader->import('legacy.php');

        // ...

.. caution::

    The ``IniFileLoader`` parses the file contents using the
    :phpfunction:`parse_ini_file` function. Therefore, you can only set
    parameters to string values. Use one of the other loaders if you want
    to use other data types (e.g. boolean, integer, etc.).

If you use any other configuration format, you have to define your own loader
class extending it from :class:`Symfony\\Component\\DependencyInjection\\Loader\\FileLoader`.
When the configuration values are dynamic, you can use the PHP configuration
file to execute your own logic. In addition, you can define your own services
to load configurations from databases or web services.

Global Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~

Some system administrators may prefer to store sensitive parameters in files
outside the project directory. Imagine that the database credentials for your
website are stored in the ``/etc/sites/mysite.com/parameters.yml`` file. Loading
this file is as simple as indicating the full file path when importing it from
any other configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: 'parameters.yml' }
            - { resource: '/etc/sites/mysite.com/parameters.yml' }

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="/etc/sites/mysite.com/parameters.yml" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $loader->import('parameters.yml');
        $loader->import('/etc/sites/mysite.com/parameters.yml');

        // ...

Most of the time, local developers won't have the same files that exist on the
production servers. For that reason, the Config component provides the
``ignore_errors`` option to silently discard errors when the loaded file
doesn't exist:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        imports:
            - { resource: 'parameters.yml' }
            - { resource: '/etc/sites/mysite.com/parameters.yml', ignore_errors: true }

        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="parameters.yml" />
                <import resource="/etc/sites/mysite.com/parameters.yml" ignore-errors="true" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // app/config/config.php
        $loader->import('parameters.yml');
        $loader->import('/etc/sites/mysite.com/parameters.yml', null, true);

        // ...

As you've seen, there are lots of ways to organize your configuration files. You
can choose one of these or even create your own custom way of organizing the
files. Don't feel limited by the Standard Edition that comes with Symfony. For even
more customization, see ":doc:`/cookbook/configuration/override_dir_structure`".
