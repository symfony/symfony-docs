The Contracts Component
=======================

    The Contracts component provides a set of abstractions extracted out of the
    Symfony components. They can be used to build on semantics that the Symfony
    components proved useful - and that already have battle-tested implementations.

Installation
------------

Contracts are provided as separate packages, so you can install only the ones
your projects really need:

.. code-block:: terminal

    $ composer require symfony/cache-contracts
    $ composer require symfony/event-dispatcher-contracts
    $ composer require symfony/deprecation-contracts
    $ composer require symfony/http-client-contracts
    $ composer require symfony/service-contracts
    $ composer require symfony/translation-contracts

.. include:: /components/require_autoload.rst.inc

Usage
-----

The abstractions in this package are useful to achieve loose coupling and
interoperability. By using the provided interfaces as type hints, you are able
to reuse any implementations that match their contracts. It could be a Symfony
component, or another package provided by the PHP community at large.

Depending on their semantics, some interfaces can be combined with
:doc:`autowiring </service_container/autowiring>` to seamlessly inject a service
in your classes.

Others might be useful as labeling interfaces, to hint about a specific behavior
that can be enabled when using :ref:`autoconfiguration <services-autoconfigure>`
or manual :doc:`service tagging </service_container/tags>` (or any other means
provided by your framework.)

Design Principles
-----------------

* Contracts are split by domain, each into their own sub-namespaces;
* Contracts are small and consistent sets of PHP interfaces, traits, normative
  docblocks and reference test suites when applicable, ...;
* Contracts must have a proven implementation to enter this repository;
* Contracts must be backward compatible with existing Symfony components.

Packages that implement specific contracts should list them in the ``provide``
section of their ``composer.json`` file, using the ``symfony/*-implementation``
convention. For example:

.. code-block:: javascript

    {
        "...": "...",
        "provide": {
            "symfony/cache-implementation": "3.0"
        }
    }

Frequently Asked Questions
--------------------------

How Is this Different From PHP-FIG's PSRs?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When applicable, the provided contracts are built on top of `PHP-FIG`_'s PSRs.
However, PHP-FIG has different goals and different processes. Symfony Contracts
focuses  on providing abstractions that are useful on their own while still
compatible with implementations provided by Symfony.

.. _`PHP-FIG`: https://www.php-fig.org/
