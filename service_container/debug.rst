How to Debug the Service Container & List Services
==================================================

You can find out what services are registered with the container using the
console. To show all services (public and private) and their PHP classes, run:

.. code-block:: terminal

    $ php bin/console debug:container

    # add this option to display "hidden services" too (those whose ID starts with a dot)
    $ php bin/console debug:container --show-hidden

To see a list of all of the available types that can be used for autowiring, run:

.. code-block:: terminal

    $ php bin/console debug:autowiring

Detailed Info about a Single Service
------------------------------------

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: terminal

    $ php bin/console debug:container App\Service\Mailer

    # to show the service arguments:
    $ php bin/console debug:container App\Service\Mailer --show-arguments
