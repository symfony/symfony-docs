.. index::
    single: Console; Usage

Using Console Commands, Shortcuts and Built-in Commands
=======================================================

In addition to the options you specify for your commands, there are some
built-in options as well as a couple of built-in commands for the console component.

.. note::

    These examples assume you have added a file ``app/console`` to run at
    the cli::

        #!/usr/bin/env php
        # app/console
        <?php

        use Symfony\Component\Console\Application;

        $application = new Application();
        // ...
        $application->run();

Built-in Commands
~~~~~~~~~~~~~~~~~

There is a built-in command ``list`` which outputs all the standard options
and the registered commands:

.. code-block:: bash

    $ php app/console list

You can get the same output by not running any command as well

.. code-block:: bash

    $ php app/console

The help command lists the help information for the specified command. For
example, to get the help for the ``list`` command:

.. code-block:: bash

    $ php app/console help list

Running ``help`` without specifying a command will list the global options:

.. code-block:: bash

    $ php app/console help

Global Options
~~~~~~~~~~~~~~

You can get help information for any command with the ``--help`` option. To
get help for the list command:

.. code-block:: bash

    $ php app/console list --help
    $ php app/console list -h

You can suppress output with:

.. code-block:: bash

    $ php app/console list --quiet
    $ php app/console list -q

You can get more verbose messages (if this is supported for a command)
with:

.. code-block:: bash

    $ php app/console list --verbose
    $ php app/console list -v

The verbose flag can optionally take a value between 1 (default) and 3 to
output even more verbose messages:

    $ php app/console list --verbose=2
    $ php app/console list -vv
    $ php app/console list --verbose=3
    $ php app/console list -vvv

If you set the optional arguments to give your application a name and version::

    $application = new Application('Acme Console Application', '1.2');

then you can use:

.. code-block:: bash

    $ php app/console list --version
    $ php app/console list -V

to get this information output:

.. code-block:: text

    Acme Console Application version 1.2

If you do not provide both arguments then it will just output:

.. code-block:: text

    console tool

You can force turning on ANSI output coloring with:

.. code-block:: bash

    $ php app/console list --ansi

or turn it off with:

.. code-block:: bash

    $ php app/console list --no-ansi

You can suppress any interactive questions from the command you are running with:

.. code-block:: bash

    $ php app/console list --no-interaction
    $ php app/console list -n

Shortcut Syntax
~~~~~~~~~~~~~~~

You do not have to type out the full command names. You can just type the
shortest unambiguous name to run a command. So if there are non-clashing
commands, then you can run ``help`` like this:

.. code-block:: bash

    $ php app/console h

If you have commands using ``:`` to namespace commands then you just have
to type the shortest unambiguous text for each part. If you have created the
``demo:greet`` as shown in :doc:`/components/console/introduction` then you
can run it with:

.. code-block:: bash

    $ php app/console d:g Fabien

If you enter a short command that's ambiguous (i.e. there are more than one
command that match), then no command will be run and some suggestions of
the possible commands to choose from will be output.
