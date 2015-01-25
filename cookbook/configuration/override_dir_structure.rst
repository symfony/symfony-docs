.. index::
    single: Override Symfony

How to Override Symfony's default Directory Structure
=====================================================

Symfony automatically ships with a default directory structure. You can
easily override this directory structure to create your own. The default
directory structure is:

.. code-block:: text

    your-project/
    ├─ app/
    │  ├─ cache/
    │  ├─ config/
    │  ├─ logs/
    │  └─ ...
    ├─ src/
    │  └─ ...
    ├─ vendor/
    │  └─ ...
    └─ web/
       ├─ app.php
       └─ ...

.. _override-cache-dir:

Override the ``cache`` Directory
--------------------------------

You can override the cache directory by overriding the ``getCacheDir`` method
in the ``AppKernel`` class of you application::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function getCacheDir()
        {
            return $this->rootDir.'/'.$this->environment.'/cache';
        }
    }

``$this->rootDir`` is the absolute path to the ``app`` directory and ``$this->environment``
is the current environment (i.e. ``dev``). In this case you have changed
the location of the cache directory to ``app/{environment}/cache``.

.. caution::

    You should keep the ``cache`` directory different for each environment,
    otherwise some unexpected behavior may happen. Each environment generates
    its own cached config files, and so each needs its own directory to store
    those cache files.

.. _override-logs-dir:

Override the ``logs`` Directory
-------------------------------

Overriding the ``logs`` directory is the same as overriding the ``cache``
directory, the only difference is that you need to override the ``getLogDir``
method::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function getLogDir()
        {
            return $this->rootDir.'/'.$this->environment.'/logs';
        }
    }

Here you have changed the location of the directory to ``app/{environment}/logs``.

Override the ``web`` Directory
------------------------------

If you need to rename or move your ``web`` directory, the only thing you
need to guarantee is that the path to the ``app`` directory is still correct
in your ``app.php`` and ``app_dev.php`` front controllers. If you simply
renamed the directory, you're fine. But if you moved it in some way, you
may need to modify the paths inside these files::

    require_once __DIR__.'/../Symfony/app/bootstrap.php.cache';
    require_once __DIR__.'/../Symfony/app/AppKernel.php';

You also need to change the ``extra.symfony-web-dir`` option in the ``composer.json``
file:

.. code-block:: javascript

    {
        ...
        "extra": {
            ...
            "symfony-web-dir": "my_new_web_dir"
        }
    }

.. tip::

    Some shared hosts have a ``public_html`` web directory root. Renaming
    your web directory from ``web`` to ``public_html`` is one way to make
    your Symfony project work on your shared host. Another way is to deploy
    your application to a directory outside of your web root, delete your
    ``public_html`` directory, and then replace it with a symbolic link to
    the ``web`` in your project.

.. note::

    If you use the AsseticBundle you need to configure this, so it can use
    the correct ``web`` directory:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml

            # ...
            assetic:
                # ...
                read_from: "%kernel.root_dir%/../../public_html"

        .. code-block:: xml

            <!-- app/config/config.xml -->

            <!-- ... -->
            <assetic:config read-from="%kernel.root_dir%/../../public_html" />

        .. code-block:: php

            // app/config/config.php

            // ...
            $container->loadFromExtension('assetic', array(
                // ...
                'read_from' => '%kernel.root_dir%/../../public_html',
            ));

    Now you just need to clear the cache and dump the assets again and your application should
    work:

    .. code-block:: bash

        $ php app/console cache:clear --env=prod
        $ php app/console assetic:dump --env=prod --no-debug

Override the ``vendor`` Directory
---------------------------------

To override the ``vendor`` directory, you need to introduce changes in the
following files:

* ``app/autoload.php``
* ``composer.json``

The change in the ``composer.json`` will look like this:

.. code-block:: json

    {
        ...
        "config": {
            "bin-dir": "bin",
            "vendor-dir": "/some/dir/vendor"
        },
        ...
    }

In ``app/autoload.php``, you need to modify the path leading to the ``vendor/autoload.php``
file::

    // app/autoload.php
    // ...
    $loader = require '/some/dir/vendor/autoload.php';

.. tip::

    This modification can be of interest if you are working in a virtual environment
    and cannot use NFS - for example, if you're running a Symfony app using
    Vagrant/VirtualBox in a guest operating system.
