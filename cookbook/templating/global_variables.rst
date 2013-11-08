.. index::
   single: Templating; Global variables

How to Inject Variables into all Templates (i.e. Global Variables)
==================================================================

Sometimes you want a variable to be accessible to all the templates you use.
This is possible inside your ``app/config/config.yml`` file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            globals:
                ga_tracking: UA-xxxxx-x

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config ...>
            <!-- ... -->
            <twig:global key="ga_tracking">UA-xxxxx-x</twig:global>
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
             // ...
             'globals' => array(
                 'ga_tracking' => 'UA-xxxxx-x',
             ),
        ));

Now, the variable ``ga_tracking`` is available in all Twig templates:

.. code-block:: html+jinja

    <p>The google tracking code is: {{ ga_tracking }}</p>

It's that easy!

Using Service Container Parameters
----------------------------------

You can also take advantage of the built-in :ref:`book-service-container-parameters`
system, which lets you isolate or reuse the value:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        ga_tracking: UA-xxxxx-x

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            globals:
                ga_tracking: "%ga_tracking%"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config ...>
            <twig:global key="ga_tracking">%ga_tracking%</twig:global>
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
             'globals' => array(
                 'ga_tracking' => '%ga_tracking%',
             ),
        ));

The same variable is available exactly as before.

Referencing Services
--------------------

Instead of using static values, you can also set the value to a service.
Whenever the global variable is accessed in the template, the service will be
requested from the service container and you get access to that object.

.. note::

    The service is not loaded lazily. In other words, as soon as Twig is
    loaded, your service is instantiated, even if you never use that global
    variable.

To define a service as a global Twig variable, prefix the string with ``@``.
This should feel familiar, as it's the same syntax you use in service configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            # ...
            globals:
                user_management: "@acme_user.user_management"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <twig:config ...>
            <!-- ... -->
            <twig:global key="user_management">@acme_user.user_management</twig:global>
        </twig:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
             // ...
             'globals' => array(
                 'user_management' => '@acme_user.user_management',
             ),
        ));

Using a Twig Extension
----------------------

If the global variable you want to set is more complicated - say an object -
then you won't be able to use the above method. Instead, you'll need to create
a :ref:`Twig Extension <reference-dic-tags-twig-extension>` and return the
global variable as one of the entries in the ``getGlobals`` method.
