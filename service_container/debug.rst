.. index::
    single: DependencyInjection; Debug
    single: Service Container; Debug

How to Debug the Service Container & List Services
==================================================

You can find out what services are registered with the container using the
console. To show all services and the class for each service, run:

.. code-block:: terminal

    $ php bin/console debug:container

By default, only public services are shown, but you can also view private services:

.. code-block:: terminal

    $ php bin/console debug:container --show-private

To see a list of all of the available types that can be used for autowiring, run:

.. code-block:: terminal

    $ php bin/console debug:container --types

.. versionadded:: 3.3
   The ``--types`` option was introduced in Symfony 3.3.

Detailed Info about a Single Service
------------------------------------

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: terminal

    $ php bin/console debug:container 'AppBundle\Service\Mailer'

    # to show the service arguments:
    $ php bin/console debug:container 'AppBundle\Service\Mailer' --show-arguments

.. versionadded:: 3.3
   The ``--show-arguments`` option was introduced in Symfony 3.3.
