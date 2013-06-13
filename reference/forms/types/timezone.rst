.. index::
   single: Forms; Fields; timezone

timezone Field Type
===================

The ``timezone`` type is a subset of the ``ChoiceType`` that allows the user
to select from all possible timezones.

The "value" for each timezone is the full timezone name, such as ``America/Chicago``
or ``Europe/Istanbul``.

Unlike the ``choice`` type, you don't need to specify a ``choices`` or
``choice_list`` option as the field type automatically uses a large list
of locales. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

+-------------+------------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `choice_list`_                                                       |
| Options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `multiple`_                                                          |
| options     | - `expanded`_                                                          |
|             | - `preferred_choices`_                                                 |
|             | - `empty_value`_                                                       |
|             | - `required`_                                                          |
|             | - `label`_                                                             |
|             | - `read_only`_                                                         |
|             | - `disabled`_                                                          |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `mapped`_                                                            |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`choice</reference/forms/types/choice>`                           |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TimezoneType` |
+-------------+------------------------------------------------------------------------+

Overridden Options
------------------

choice_list
~~~~~~~~~~~

**default**: :class:`Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\TimezoneChoiceList`

The Timezone type defaults the choice_list to all timezones returned by
:phpmethod:`DateTimeZone::listIdentifiers`, broken down by continent.

Inherited options
-----------------

These options inherit from the :doc:`choice</reference/forms/types/choice>` type:

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

.. include:: /reference/forms/types/options/empty_value.rst.inc

These options inherit from the :doc:`field</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
