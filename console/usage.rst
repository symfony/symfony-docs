.. index::
    single: Console; Usage

How to Use the Console
======================

The :doc:`/components/console/usage` page of the components documentation looks
at the global console options. When you use the console as part of the full-stack
framework, some additional global options are available as well.

By default, console commands run in the ``dev`` environment and you may want to
change this for some commands. For example, you may want to run some commands in
the ``prod`` environment for performance reasons. Also, the result of some
commands will be different depending on the environment. For example, the
``cache:clear`` command will clear the cache for the specified environment only.
To clear the ``prod`` cache you need to run:

.. code-block:: terminal

    $ php bin/console cache:clear --no-warmup --env=prod

    # this is equivalent:
    $ php bin/console cache:clear --no-warmup -e prod

In addition to changing the environment, you can also choose to disable debug mode.
This can be useful where you want to run commands in the ``dev`` environment
but avoid the performance hit of collecting debug data:

.. code-block:: terminal

    $ php bin/console list --no-debug
