.. index::
   single: Config
   single: Components; Config

The Config Component
====================

    The Config component provides several classes to help you find, load,
    combine, autofill and validate configuration values of any kind, whatever
    their source may be (YAML, XML, INI files, or for instance a database).

.. caution::

    The ``IniFileLoader`` parses the file contents using the
    :phpfunction:`parse_ini_file` function, therefore, you can only set
    parameters to string values. To set parameters to other data types
    (e.g. boolean, integer, etc), the other loaders are recommended.

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
