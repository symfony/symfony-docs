.. index::
    single: Console; Usage

How to use the Console
======================

The :doc:`/components/console/usage` page of the components documentation looks
at the global console options. When you use the console as part of the full
stack framework, some additional global options are available as well.

By default, console commands run in the ``dev`` environment and you may want
to change this for some commands. For example, you may want to run some commands
in the ``prod`` environment for performance reasons. Also, the result of some commands
will be different depending on the environment. For example, the ``cache:clear``
command will clear and warm the cache for the specified environment only. To
clear and warm the ``prod`` cache you need to run:

.. code-block:: bash

    $ php app/console cache:clear --env=prod

or the equivalent:

.. code-block:: bash

    $ php app/console cache:clear -e prod

In addition to changing the environment, you can also choose to disable debug mode.
This can be useful where you want to run commands in the ``dev`` environment
but avoid the performance hit of collecting debug data:

.. code-block:: bash

    $ php app/console list --no-debug

There is an interactive shell which allows you to enter commands without having to
specify ``php app/console`` each time, which is useful if you need to run several
commands. To enter the shell run:

.. code-block:: bash

    $ php app/console --shell
    $ php app/console -s

You can now just run commands with the command name:

.. code-block:: bash

    Symfony > list

When using the shell you can choose to run each command in a separate process:

.. code-block:: bash

    $ php app/console --shell --process-isolation
    $ php app/console -s --process-isolation

When you do this, the output will not be colorized and interactivity is not
supported so you will need to pass all command params explicitly.

.. note::

    Unless you are using isolated processes, clearing the cache in the shell
    will not have an effect on subsequent commands you run. This is because
    the original cached files are still being used.