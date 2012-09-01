.. index::
   single: Override Symfony

How to override Symfony's Default Directory Structure
=====================================================

Symfony automatically ships with a default directory structure. You can easily
override this directory structure to create your own. The default directory
structure is:

.. code-block:: text

    Symfony/
        app/
            cache/
            config/
            logs/
            ...
        src/
            ...
        vendor/
            ...
        web/
            app.php
            ...

Override the ``cache`` directory
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
            return $this->rootDir.'/'.$this->environment.'/cache/';
        }
    }

``$this->rootDir`` is the absolute path to the ``app`` directory and ``$this->environment``
is the current environment (i.e. ``dev``). In this case we have changed the location 
of the cache directory to ``app/{environment}/cache``.

Override the ``logs`` directory
-------------------------------

Overriding the ``logs`` directory is the same as overriding the ``cache`` directory,
the only difference is that you need to override the ``getLogDir`` method::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function getLogDir()
        {
            return $this->rootDir.'/'.$this->environment.'/logs/';
        }
    }

We have changed location of the directory to ``app/{environment}/logs``.

Override the ``web`` directory
------------------------------

Some shared hosting require to rename the ``web`` directory to ``public_html``
and move this directory to the apache root. You can simply rename the directory: 
The only thing you need to check is if the path to the ``app`` directory is 
still right in ``app.php`` or ``app_dev.php``. For instance: If we move the 
``web`` directory one map up we need to add ``Symfony`` (the name of your 
Symfony installation directory) to the path::

    require_once __DIR__.'/../Symfony/app/bootstrap.php.cache';
    require_once __DIR__.'/../Symfony/app/AppKernel.php';
    
.. note::
    
    If you use the AsseticBundle you need to configure this, so it can use the
    correct ``web`` directory:

    .. code-block:: yaml

        # app/config/config.yml

        # ...
        assetic:
            # ...
            read_from: %kernel.root_dir%/../../public_html

    Now you just need to dump the assets again and your application should work:

    .. code-block:: bash

        $ php app/console assetic:dump --env=prod --no-debug
