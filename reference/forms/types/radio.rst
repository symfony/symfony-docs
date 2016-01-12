.. index::
   single: Forms; Fields; RadioType

RadioType Field
===============

Creates a single radio button. If the radio button is selected, the field
will be set to the specified value. Radio buttons cannot be unchecked -
the value only changes when another radio button with the same name gets
checked.

The ``RadioType`` isn't usually used directly. More commonly it's used
internally by other types such as :doc:`ChoiceType </reference/forms/types/choice>`.
If you want to have a boolean field, use :doc:`CheckboxType </reference/forms/types/checkbox>`.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``radio`` field                                           |
+-------------+---------------------------------------------------------------------+
| Inherited   | from the :doc:`CheckboxType </reference/forms/types/checkbox>`:     |
| options     |                                                                     |
|             | - `value`_                                                          |
|             |                                                                     |
|             | from the :doc:`FormType </reference/forms/types/form>`:             |
|             |                                                                     |
|             | - `data`_                                                           |
|             | - `disabled`_                                                       |
|             | - `empty_data`_                                                     |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `mapped`_                                                         |
|             | - `read_only`_ (deprecated as of 2.8)                               |
|             | - `required`_                                                       |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`CheckboxType </reference/forms/types/checkbox>`               |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RadioType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`CheckboxType </reference/forms/types/checkbox>`:

.. include:: /reference/forms/types/options/value.rst.inc

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/checkbox_empty_data.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

Form Variables
--------------

.. include:: /reference/forms/types/variables/check_or_radio_table.rst.inc
