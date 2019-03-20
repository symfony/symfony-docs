.. index::
   single: Forms; Fields; TimezoneType

TimezoneType Field
==================

The ``TimezoneType`` is a subset of the ``ChoiceType`` that allows the
user to select from all possible timezones.

The "value" for each timezone is the full timezone name, such as ``America/Chicago``
or ``Europe/Istanbul``.

Unlike the ``ChoiceType``, you don't need to specify a ``choices`` option as the
field type automatically uses a large list of timezones. You *can* specify the option
manually, but then you should just use the ``ChoiceType`` directly.

+-------------+------------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+-------------+------------------------------------------------------------------------+
| Options     | - `input`_                                                             |
|             | - `regions`_                                                           |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `choices`_                                                           |
| options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | from the :doc:`ChoiceType </reference/forms/types/choice>`             |
| options     |                                                                        |
|             | - `expanded`_                                                          |
|             | - `multiple`_                                                          |
|             | - `placeholder`_                                                       |
|             | - `preferred_choices`_                                                 |
|             | - `trim`_                                                              |
|             |                                                                        |
|             | from the :doc:`FormType </reference/forms/types/form>`                 |
|             |                                                                        |
|             | - `data`_                                                              |
|             | - `disabled`_                                                          |
|             | - `empty_data`_                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `help`_                                                              |
|             | - `help_attr`_                                                         |
|             | - `help_html`_                                                         |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `label_format`_                                                      |
|             | - `mapped`_                                                            |
|             | - `required`_                                                          |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`ChoiceType </reference/forms/types/choice>`                      |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimezoneType` |
+-------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

input
~~~~~

**type**: ``string`` **default**: ``string``

The format of the *input* data - i.e. the format that the timezone is stored
on your underlying object. Valid values are:

* ``string`` (e.g. ``America/New_York``)
* ``datetimezone`` (a ``DateTimeZone`` object)

regions
~~~~~~~

**type**: ``int`` **default**: ``\DateTimeZone::ALL``

.. deprecated:: 4.2

    This option was deprecated in Symfony 4.2.

The available regions in the timezone choice list. For example: ``DateTimeZone::AMERICA | DateTimeZone::EUROPE``

Overridden Options
------------------

choices
~~~~~~~

**default**: An array of timezones.

The Timezone type defaults the choices to all timezones returned by
:phpmethod:`DateTimeZone::listIdentifiers`, broken down by continent.

.. caution::

    If you want to override the built-in choices of the timezone type, you
    will also have to set the ``choice_loader`` option to ``null``.

Inherited Options
-----------------

These options inherit from the :doc:`ChoiceType </reference/forms/types/choice>`:

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/choice_type_trim.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``[]`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
