.. index::
   single: Forms; Fields; checkbox

checkbox Field Type
===================

Creates a single input checkbox. This should always be used for a field that
has a Boolean value: if the box is checked, the field will be set to true,
if the box is unchecked, the value will be set to false.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``checkbox`` field                                           |
+-------------+------------------------------------------------------------------------+
| Options     | - `value`_                                                             |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `empty_data`_                                                        |
| options     | - `compound`_                                                          |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `data`_                                                              |
| options     | - `required`_                                                          |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `read_only`_                                                         |
|             | - `disabled`_                                                          |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `mapped`_                                                            |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                              |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType` |
+-------------+------------------------------------------------------------------------+

Example Usage
-------------

.. code-block:: php

    $builder->add('public', 'checkbox', array(
        'label'     => 'Show this entry publicly?',
        'required'  => false,
    ));

Field Options
-------------

.. include:: /reference/forms/types/options/value.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/checkbox_empty_data.rst.inc

.. include:: /reference/forms/types/options/checkbox_compound.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/data.rst.inc

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
