.. index::
   single: Forms; Fields; radio

radio Field Type
================

Creates a single radio button. If the radio button is selected, the field will
be set to the specified value. Radio buttons cannot be unchecked - the value only
changes when another radio button with the same name gets checked.

The ``radio`` type isn't usually used directly. More commonly it's used
internally by other types such as :doc:`choice </reference/forms/types/choice>`.
If you want to have a Boolean field, use :doc:`checkbox </reference/forms/types/checkbox>`.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``radio`` field                                           |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `value`_                                                          |
| options     | - `data`_                                                           |
|             | - `empty_data`_                                                     |
|             | - `required`_                                                       |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `read_only`_                                                      |
|             | - `disabled`_                                                       |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `mapped`_                                                         |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`checkbox </reference/forms/types/checkbox>`                   |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\RadioType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`checkbox </reference/forms/types/checkbox>`
type:

.. include:: /reference/forms/types/options/value.rst.inc

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

Form Variables
--------------

.. include:: /reference/forms/types/variables/check_or_radio_table.rst.inc
