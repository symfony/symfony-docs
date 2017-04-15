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

.. note::

    If a private service is only used as an argument to just *one* other service,
    it won't be displayed by the ``debug:container`` command, even when using
    the ``--show-private`` option. See :ref:`Inline Private Services <inlined-private-services>`
    for more details.

You can get more detailed information about a particular service by specifying
its id:

.. code-block:: terminal

    $ php bin/console debug:container app.mailer

By default, the arguments of the services are not included in the output. Add the
``--show-arguments`` option to show them:

.. versionadded:: 3.3
   The ``--show-arguments`` option was introduced in Symfony 3.3.

.. code-block:: terminal

    $ php bin/console debug:container app.mailer --show-arguments
