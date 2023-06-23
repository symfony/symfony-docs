The Config Component
====================

.. caution::

    This article explains how to define configuration trees in any PHP application
    using the Config component. If you are working on a Symfony application, you
    don't have to do any of this to configure your application. Instead, read the
    explanation of :doc:`this other article </configuration>`.

The Config component provides several classes to help you find, load,
combine, fill and validate configuration values of any kind, whatever
their source may be (YAML, XML, INI files, or for instance a database).

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
