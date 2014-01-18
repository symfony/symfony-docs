.. index::
   single: Doctrine; ORM console commands
   single: CLI; Doctrine ORM

Console Commands
----------------

The Doctrine2 ORM integration offers several console commands under the
``doctrine`` namespace. To view the command list you can run the console
without any arguments:

.. code-block:: bash

    $ php app/console

A list of available commands will print out, many of which start with the
``doctrine:`` prefix. You can find out more information about any of these
commands (or any Symfony command) by running the ``help`` command. For example,
to get details about the ``doctrine:database:create`` task, run:

.. code-block:: bash

    $ php app/console help doctrine:database:create

Some notable or interesting tasks include:

* ``doctrine:ensure-production-settings`` - checks to see if the current
  environment is configured efficiently for production. This should always
  be run in the ``prod`` environment:

  .. code-block:: bash

      $ php app/console doctrine:ensure-production-settings --env=prod

* ``doctrine:mapping:import`` - allows Doctrine to introspect an existing
  database and create mapping information. For more information, see
  :doc:`/cookbook/doctrine/reverse_engineering`.

* ``doctrine:mapping:info`` - tells you all of the entities that Doctrine
  is aware of and whether or not there are any basic errors with the mapping.

* ``doctrine:query:dql`` and ``doctrine:query:sql`` - allow you to execute
  DQL or SQL queries directly from the command line.

.. note::

   To be able to load data fixtures to your database, you will need to have
   the DoctrineFixturesBundle bundle installed. To learn how to do it,
   read the ":doc:`/bundles/DoctrineFixturesBundle/index`" entry of the
   documentation.

.. tip::

    This page shows working with Doctrine within a controller. You may also
    want to work with Doctrine elsewhere in your application. The
    :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::getDoctrine`
    method of the controller returns the ``doctrine`` service, you can work with
    this in the same way elsewhere by injecting this into your own
    services. See :doc:`/book/service_container` for more on creating
    your own services.
