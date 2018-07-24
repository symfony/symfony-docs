Translator Configuration Reference
==================================

The translator configuration options are defined by the FrameworkBundle under
the ``framework`` key in the ``config/packages/translation.{yaml|xml|php}`` file.

.. code-block:: terminal

    # displays the default config values defined by Symfony
    $ php bin/console config:dump-reference framework.translator

    # displays the actual config values used by your application
    $ php bin/console debug:config framework.translator

Configuration
-------------

* ``framework``

  * ``translator``

    * `default_path`_
    * `enabled`_
    * `fallbacks`_
    * `logging`_
    * :ref:`paths <reference-translator-paths>`

enabled
~~~~~~~

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable the ``translator`` service in the service container.

fallbacks
~~~~~~~~~

**type**: ``string|array`` **default**: ``array('en')``

This option is used when the translation key for the current locale wasn't
found.

.. seealso::

    For more details, see :doc:`/translation`.

logging
~~~~~~~

**default**: ``true`` when the debug mode is enabled, ``false`` otherwise.

When ``true``, a log entry is made whenever the translator cannot find a translation
for a given key. The logs are made to the ``translation`` channel and at the
``debug`` for level for keys where there is a translation in the fallback
locale and the ``warning`` level if there is no translation to use at all.

.. _reference-translator-paths:

paths
~~~~~

**type**: ``array`` **default**: ``[]``

This option allows to define an array of paths where the component will look
for translation files.

default_path
~~~~~~~~~~~~

.. versionadded:: 3.4
    The ``default_path`` option was introduced in Symfony 3.4.

**type**: ``string`` **default**: ``%kernel.project_dir%/translations``

This option allows to define the path where the application translations files
are stored.
