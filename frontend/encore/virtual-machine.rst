Using Encore in a Virtual Machine
=================================

Encore is compatible with virtual machines such as `VirtualBox`_ and `VMWare`_
but you may need to make some changes to your configuration to make it work.

File Watching Issues
--------------------

When using a virtual machine, your project root directory is shared with the
virtual machine using `NFS`_. This introduces issues with files watching, so
you must enable the `polling`_ option to make it work:

.. code-block:: javascript

    // webpack.config.js

    // ...

    // will be applied for `encore dev --watch` and `encore dev-server` commands
    Encore.configureWatchOptions(watchOptions => {
        watchOptions.poll = 250; // check for changes every 250 milliseconds
    });

Development Server Issues
-------------------------

Configure the Public Path
~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    You can skip this section if your application is running on
    ``http://localhost`` instead a custom local domain-name like
    ``http://app.vm``.

When running the development server, you will probably see the following errors
in the web console:

.. code-block:: text

    GET http://localhost:8080/build/vendors~app.css net::ERR_CONNECTION_REFUSED
    GET http://localhost:8080/build/runtime.js net::ERR_CONNECTION_REFUSED
    ...

If your Symfony application is running on a custom domain (e.g.
``http://app.vm``), you must configure the public path explicitly in your
``package.json``:

.. code-block:: diff

      {
          ...
          "scripts": {
    -        "dev-server": "encore dev-server",
    +        "dev-server": "encore dev-server --public http://app.vm:8080",
              ...
          }
      }

After restarting Encore and reloading your web page, you will probably see
different issues in the web console:

.. code-block:: text

    GET http://app.vm:8080/build/vendors~app.css net::ERR_CONNECTION_REFUSED
    GET http://app.vm:8080/build/runtime.js net::ERR_CONNECTION_REFUSED

You still need to make other configuration changes, as explained in the
following sections.

Allow External Access
~~~~~~~~~~~~~~~~~~~~~

Add the ``--host 0.0.0.0`` argument to the ``dev-server`` configuration in your
``package.json`` file to make the development server accept all incoming
connections:

.. code-block:: diff

      {
          ...
          "scripts": {
    -        "dev-server": "encore dev-server --public http://app.vm:8080",
    +        "dev-server": "encore dev-server --public http://app.vm:8080 --host 0.0.0.0",
              ...
          }
      }

.. caution::

    Make sure to run the development server inside your virtual machine only;
    otherwise other computers can have access to it.

Fix "Invalid Host header" Issue
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Webpack will respond ``Invalid Host header`` when trying to access files from
the dev-server. To fix this, set the ``allowedHosts`` option:

.. code-block:: javascript

    // webpack.config.js
    // ...

    Encore
        // ...

        .configureDevServerOptions(options => {
            options.allowedHosts = all;
        })

.. caution::

    Beware that `it's not recommended to set allowedHosts to all`_ in general, but
    here it's required to solve the issue when using Encore in a virtual machine.

.. _`VirtualBox`: https://www.virtualbox.org/
.. _`VMWare`: https://www.vmware.com
.. _`NFS`: https://en.wikipedia.org/wiki/Network_File_System
.. _`polling`: https://webpack.js.org/configuration/watch/#watchoptionspoll
.. _`it's not recommended to set allowedHosts to all`: https://webpack.js.org/configuration/dev-server/#devserverallowedhosts
