.. index::
    single: Console; Usage

How to Use the Console
======================

The :doc:`/components/console/usage` page of the components documentation looks
at the global console options. When you use the console as part of the full-stack
framework, some additional global options are available as well.

By default, console commands run in the ``dev`` environment and you may want
to change this for some commands. For example, you may want to run some commands
in the ``prod`` environment for performance reasons. Also, the result of some commands
will be different depending on the environment. For example, the ``cache:clear``
command will clear and warm the cache for the specified environment only. To
clear and warm the ``prod`` cache you need to run:

.. code-block:: bash

    $ php bin/console cache:clear --env=prod

or the equivalent:

.. code-block:: bash

    $ php bin/console cache:clear -e prod

In addition to changing the environment, you can also choose to disable debug mode.
This can be useful where you want to run commands in the ``dev`` environment
but avoid the performance hit of collecting debug data:

.. code-block:: bash

    $ php bin/console list --no-debug
