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

Debugging Service Tags
----------------------

Run the following command to find out what services are :doc:`tagged </service_container/tags>`
with a specific tag:

.. code-block:: terminal

    $ php bin/console debug:container --tag=kernel.event_listener

Partial search is also available:

.. code-block:: terminal

    $ php bin/console debug:container --tag=kernel

    Select one of the following tags to display its information:
     [0] kernel.event_listener
     [1] kernel.event_subscriber
     [2] kernel.reset
     [3] kernel.cache_warmer
     [4] kernel.locale_aware
     [5] kernel.fragment_renderer
     [6] kernel.cache_clearer

Detailed Info about a Single Service
------------------------------------

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: terminal

    $ php bin/console debug:container App\Service\Mailer

    # to show the service arguments:
    $ php bin/console debug:container App\Service\Mailer --show-arguments
