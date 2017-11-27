.. index::
    single: Configuration

How to Organize Configuration Files
===================================

The Symfony skeleton defines three :doc:`execution environments </configuration/environments>`
called ``dev``, ``prod`` and ``test``. An environment simply represents a way
to execute the same codebase with different configurations.

In order to select the configuration file to load for each environment, Symfony
executes the ``configureContainer()`` method of the ``Kernel`` class::

    // src/Kernel.php
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;

    class Kernel extends BaseKernel
    {
        const CONFIG_EXTS = '.{php,xml,yaml,yml}';

        // ...

        public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
        {
            $confDir = $this->getProjectDir().'/config';
            $loader->load($confDir.'/packages/*'.self::CONFIG_EXTS, 'glob');
            if (is_dir($confDir.'/packages/'.$this->environment)) {
                $loader->load($confDir.'/packages/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
            }
            $loader->load($confDir.'/services'.self::CONFIG_EXTS, 'glob');
            $loader->load($confDir.'/services_'.$this->environment.self::CONFIG_EXTS, 'glob');
        }
    }

For the ``dev`` environment, Symfony loads the following config files and
directories and in this order:

#. ``config/packages/*``
#. ``config/packages/dev/*``
#.  ``config/services.yaml``
#. ``config/services_dev.yaml``

Therefore, the configuration files of the default Symfony applications follow
this structure:

.. code-block:: text

    your-project/
    ├─ config/
    │  └─ packages/
    │     ├─ dev/
    |     │  ├─ framework.yaml
    │     │  └─ ...
    │     ├─ prod/
    │     │  └─ ...
    │     ├─ test/
    │     │  └─ ...
    |     ├─ framework.yaml
    │     └─ ...
    │     ├─ services.yaml
    │     └─ services_dev.yaml
    ├─ ...

This default structure was chosen for its simplicity — one file per package and
environment. But as any other Symfony feature, you can customize it to better
suit your needs.

Advanced Techniques
-------------------

Symfony loads configuration files using the
:doc:`Config component </components/config>`, which provides some
advanced features.

Mix and Match Configuration Formats
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Configuration files can import files defined with any other built-in configuration
format (``.yml``, ``.xml``, ``.php``, ``.ini``):

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: 'my_config_file.xml' }
            - { resource: 'legacy.php' }

        # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="my_config_file.yaml" />
                <import resource="legacy.php" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        $loader->import('my_config_file.yaml');
        $loader->import('legacy.xml');

        // ...

If you use any other configuration format, you have to define your own loader
class extending it from :class:`Symfony\\Component\\DependencyInjection\\Loader\\FileLoader`.
When the configuration values are dynamic, you can use the PHP configuration
file to execute your own logic. In addition, you can define your own services
to load configurations from databases or web services.

Global Configuration Files
~~~~~~~~~~~~~~~~~~~~~~~~~~

Some system administrators may prefer to store sensitive parameters in files
outside the project directory. Imagine that the database credentials for your
website are stored in the ``/etc/sites/mysite.com/parameters.yaml`` file. Loading
this file is as simple as indicating the full file path when importing it from
any other configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        imports:
            - { resource: '/etc/sites/mysite.com/parameters.yaml', ignore_errors: true }

        # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <imports>
                <import resource="/etc/sites/mysite.com/parameters.yaml" ignore-errors="true" />
            </imports>

            <!-- ... -->
        </container>

    .. code-block:: php

        // config/services.php
        $loader->import('/etc/sites/mysite.com/parameters.yaml', null, true);

        // ...

.. tip::

    The ``ignore_errors`` option (which is the third optional argument in the
    loader's ``import()`` method) silently discards errors when the loaded file
    doesn't exist. This is needed in this case because most of the time, local
    developers won't have the same files that exist on the production servers.

As you've seen, there are lots of ways to organize your configuration files. You
can choose one of these or even create your own custom way of organizing the
files. For even more customization, see ":doc:`/configuration/override_dir_structure`".
