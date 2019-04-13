Using Encore in a Virtual Machine
=================================

You may encounter some issues when using Encore in a virtual machine, like VirtualBox or VMWare.

Fix watching issues
-------------------

When using a virtual machine, your project root directory is shared with the virtual machine with `NFS`_.
This is really useful, but it introduces some issues with files watching.

You must enable `polling`_ option to make it work:

.. code-block:: javascript

    // webpack.config.js

    // ...

    // will be applied for `encore dev --watch` and `encore dev-server` commands
    Encore.configureWatchOptions(watchOptions => {
        watchOptions.poll = 250; // check for changes every 250 ms
    });

Fix development server
----------------------

Configure public path
~~~~~~~~~~~~~~~~~~~~~

.. note::

    You can skip this sub-section if your app is running on ``http://localhost``
    and not a custom local domain-name like ``http://app.vm``.

When running the development server, you will probably face the following errors in the web console:

.. code-block:: text

    GET http://localhost:8080/build/vendors~app.css net::ERR_CONNECTION_REFUSED
    GET http://localhost:8080/build/runtime.js net::ERR_CONNECTION_REFUSED
    ...

If your Symfony application is running on ``http://app.vm``, you must configure the public path explicitly
in your ``package.json``:

.. code-block:: diff

    {
        ...
        "scripts": {
    -        "dev-server": "encore dev-server",
    +        "dev-server": "encore dev-server --public http://app.vm:8080",
            ...
        }
    }

After restarting Encore and reloading your web page, you will probably face different issues:

.. code-block:: text

    GET http://app.vm:8080/build/vendors~app.css net::ERR_CONNECTION_REFUSED
    GET http://app.vm:8080/build/runtime.js net::ERR_CONNECTION_REFUSED

Encore understood our modification but it's still not working. There is still two things to do.

Allow external access
~~~~~~~~~~~~~~~~~~~~~

You must configure how you run the `webpack-dev-server`_.
This can easily be done in your ``package.json`` by adding ``--host 0.0.0.0`` argument:

.. code-block:: diff

    {
        ...
        "scripts": {
    -        "dev-server": "encore dev-server --public http://app.vm:8080",
    +        "dev-server": "encore dev-server --public http://app.vm:8080 --host 0.0.0.0",
            ...
        }
    }

.. warning::

    Using ``--host 0.0.0.0`` makes your development server accept all incoming connections.
    Be sure to run the development server inside your virtual machine and not outside, otherwise other computers can have access to it.

Fix "Invalid Host header" issue
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Webpack will respond ``Invalid Host header`` when trying to access files from the dev-server.
To fix this, add the argument ``--disable-host-check``:

.. code-block:: diff

    {
        ...
        "scripts": {
    -        "dev-server": "encore dev-server --public http://app.vm:8080 --host 0.0.0.0",
    +        "dev-server": "encore dev-server --public http://app.vm:8080 --host 0.0.0.0 --disable-host-check",
            ...
        }
    }

.. warning::

    This is usually not recommended to disable host checking, `more information here <https://webpack.js.org/configuration/dev-server/#devserverdisablehostcheck>`_.

.. _`NFS`: https://en.wikipedia.org/wiki/Network_File_System
.. _`polling`: https://webpack.js.org/configuration/watch/#watchoptionspoll
.. _`webpack-dev-server`: https://webpack.js.org/configuration/dev-server/
