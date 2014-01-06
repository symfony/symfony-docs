.. index::
   single: Config
   single: Components; Config

The Config Component
====================

Introduction
------------

The Config component provides several classes to help you find, load, combine,
autofill and validate configuration values of any kind, whatever their source
may be (YAML, XML, INI files, or for instance a database).

.. caution::

    ``IniFileLoader`` parses with the `parse_ini_file` function, therefore
    it can only configure parameters as string values. For other
    data types support (e.g. Boolean, integer, etc), the other loaders
    are recommended.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/config`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/Config).

Sections
--------

* :doc:`/components/config/resources`
* :doc:`/components/config/caching`
* :doc:`/components/config/definition`

.. _Packagist: https://packagist.org/packages/symfony/config
