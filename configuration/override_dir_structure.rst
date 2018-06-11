.. index::
    single: Override Symfony

How to Override Symfony's default Directory Structure
=====================================================

Symfony automatically ships with a default directory structure. You can
easily override this directory structure to create your own. The default
directory structure is:

.. code-block:: text

    your-project/
    ├─ assets/
    ├─ bin/
    │  └─ console
    ├─ config/
    ├─ public/
    │  └─ index.php
    ├─ src/
    │  └─ ...
    ├─ templates/
    ├─ tests/
    ├─ translations/
    ├─ var/
    │  ├─ cache/
    │  ├─ log/
    │  └─ ...
    └─ vendor/

.. _override-cache-dir:

Override the ``cache`` Directory
--------------------------------

You can change the default cache directory by overriding the ``getCacheDir()``
method in the ``Kernel`` class of your application::

    // src/Kernel.php

    // ...
    class Kernel extends BaseKernel
    {
        // ...

        public function getCacheDir()
        {
            return dirname(__DIR__).'/var/'.$this->environment.'/cache';
        }
    }

In this code, ``$this->environment`` is the current environment (i.e. ``dev``).
In this case you have changed the location of the cache directory to
``var/{environment}/cache``.

.. caution::

    You should keep the ``cache`` directory different for each environment,
    otherwise some unexpected behavior may happen. Each environment generates
    its own cached configuration files, and so each needs its own directory to
    store those cache files.

.. _override-logs-dir:

Override the ``logs`` Directory
-------------------------------

Overriding the ``logs`` directory is the same as overriding the ``cache``
directory. The only difference is that you need to override the ``getLogDir()``
method::

    // src/Kernel.php

    // ...
    class Kernel extends Kernel
    {
        // ...

        public function getLogDir()
        {
            return dirname(__DIR__).'/var/'.$this->environment.'/log';
        }
    }

Here you have changed the location of the directory to ``var/{environment}/log``.

.. _override-templates-dir:

Override the Templates Directory
--------------------------------

If your templates are not stored in the default ``templates/`` directory, use
the :ref:`twig.paths <config-twig-paths>` configuration option to define your
own templates directory (or directories):

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths: ["%kernel.project_dir%/resources/views"]

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:path>%kernel.project_dir%/resources/views</twig:path>
            </twig:config>

        </container>

    .. code-block:: php

        // config/packages/twig.php
        $container->loadFromExtension('twig', array(
            'paths' => array(
                '%kernel.project_dir%/resources/views',
            ),
        ));

.. _override-web-dir:
.. _override-the-web-directory:

Override the ``public`` Directory
---------------------------------

If you need to rename or move your ``public`` directory, the only thing you need
to guarantee is that the path to the ``var`` directory is still correct in your
``index.php`` front controller. If you simply renamed the directory, you're
fine. But if you moved it in some way, you may need to modify these paths inside
those files::

    require_once __DIR__.'/../path/to/vendor/autoload.php';

You also need to change the ``extra.public-dir`` option in the
``composer.json`` file:

.. code-block:: json

    {
        "...": "...",
        "extra": {
            "...": "...",
            "public-dir": "my_new_public_dir"
        }
    }

.. tip::

    Some shared hosts have a ``public_html`` web directory root. Renaming
    your web directory from ``public`` to ``public_html`` is one way to make
    your Symfony project work on your shared host. Another way is to deploy
    your application to a directory outside of your web root, delete your
    ``public_html`` directory, and then replace it with a symbolic link to
    the ``public`` dir in your project.

Override the ``vendor`` Directory
---------------------------------

To override the ``vendor`` directory, you need to define the ``vendor-dir``
option in your ``composer.json`` file like this:

.. code-block:: json

    {
        "config": {
            "bin-dir": "bin",
            "vendor-dir": "/some/dir/vendor"
        },
    }

.. tip::

    This modification can be of interest if you are working in a virtual environment
    and cannot use NFS - for example, if you're running a Symfony application using
    Vagrant/VirtualBox in a guest operating system.
