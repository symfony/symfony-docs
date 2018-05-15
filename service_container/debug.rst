.. index::
    single: DependencyInjection; Debug
    single: Service Container; Debug

How to Debug the Service Container & List Services
==================================================

You can find out what services are registered with the container using the
console. To show all services (public and private) and their PHP classes, run:

.. code-block:: terminal

    $ php bin/console debug:container

Hidden services, those whose ID starts with a dot character (``.``) aren't
included by default in the output of this command. Add the ``--show-hidden``
option to list them too:

.. code-block:: terminal

    $ php bin/console debug:container --show-hidden

.. versionadded:: 4.1
    Hidden services and the ``--show-hidden`` option were introduced in Symfony 4.1.

To see a list of all of the available types that can be used for autowiring, run:

.. code-block:: terminal

    $ php bin/console debug:autowiring

Detailed Info about a Single Service
------------------------------------

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: terminal

    $ php bin/console debug:container 'App\Service\Mailer'

    # to show the service arguments:
    $ php bin/console debug:container 'App\Service\Mailer' --show-arguments
