.. index::
   single: Doctrine; ORM console commands
   single: CLI; Doctrine ORM

Console Commands
----------------

The Doctrine2 ORM integration offers several console commands under the
``doctrine`` namespace. To view the command list you can use the ``list``
command:

.. code-block:: bash

    $ php bin/console list doctrine

A list of available commands will print out. You can find out more information
about any of these commands (or any Symfony command) by running the ``help``
command. For example, to get details about the ``doctrine:database:create``
task, run:

.. code-block:: bash

    $ php bin/console help doctrine:database:create

Some notable or interesting tasks include:

* ``doctrine:ensure-production-settings`` - checks to see if the current
  environment is configured efficiently for production. This should always
  be run in the ``prod`` environment:

  .. code-block:: bash

      $ php bin/console doctrine:ensure-production-settings --env=prod

* ``doctrine:mapping:import`` - allows Doctrine to introspect an existing
  database and create mapping information. For more information, see
  :doc:`/cookbook/doctrine/reverse_engineering`.

* ``doctrine:mapping:info`` - tells you all of the entities that Doctrine
  is aware of and whether or not there are any basic errors with the mapping.

* ``doctrine:query:dql`` and ``doctrine:query:sql`` - allow you to execute
  DQL or SQL queries directly from the command line.
