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
of timezones. You *can* specify either of these options manually, but then
you should just use the ``choice`` type directly.

+-------------+------------------------------------------------------------------------+
| Rendered as | can be various tags (see :ref:`forms-reference-choice-tags`)           |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `choice_list`_                                                       |
| Options     |                                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | from the :doc:`choice </reference/forms/types/choice>` type            |
| options     |                                                                        |
|             | - `placeholder`_                                                       |
|             | - `expanded`_                                                          |
|             | - `multiple`_                                                          |
|             | - `preferred_choices`_                                                 |
|             |                                                                        |
|             | from the :doc:`form </reference/forms/types/form>` type                |
|             |                                                                        |
|             | - `data`_                                                              |
|             | - `disabled`_                                                          |
|             | - `empty_data`_                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `mapped`_                                                            |
|             | - `read_only`_                                                         |
|             | - `required`_                                                          |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`choice </reference/forms/types/choice>`                          |
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

Inherited Options
-----------------

These options inherit from the :doc:`choice </reference/forms/types/choice>` type:

.. include:: /reference/forms/types/options/placeholder.rst.inc

.. include:: /reference/forms/types/options/expanded.rst.inc

.. include:: /reference/forms/types/options/multiple.rst.inc

.. include:: /reference/forms/types/options/preferred_choices.rst.inc

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``multiple`` is ``false`` and ``expanded`` is ``false``, then ``''``
  (empty string);
* Otherwise ``array()`` (empty array).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
