The Config Component
====================

The Config component provides utilities to define and manage the configuration
options of PHP applications. It allows you to:

* Define a configuration structure, its validation rules, default values and documentation;
* Support different configuration formats (YAML, XML, INI, etc.);
* Merge multiple configurations from different sources into a single configuration.

.. note::

    You don't have to use this component to configure Symfony applications.
    Instead, read the docs about :doc:`how to configure Symfony applications </configuration>`.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/config

.. include:: /components/require_autoload.rst.inc

Learn More
----------

.. toctree::
    :maxdepth: 1
    :glob:

    config/*
    /bundles/configuration
    /bundles/extension
    /bundles/prepend_extension
