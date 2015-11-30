.. index::
   single: Forms; Fields; CheckboxType

CheckboxType Field
==================

Creates a single input checkbox. This should always be used for a field
that has a boolean value: if the box is checked, the field will be set to
true, if the box is unchecked, the value will be set to false.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``input`` ``checkbox`` field                                           |
+-------------+------------------------------------------------------------------------+
| Options     | - `value`_                                                             |
+-------------+------------------------------------------------------------------------+
| Overridden  | - `compound`_                                                          |
| options     | - `empty_data`_                                                        |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `data`_                                                              |
| options     | - `disabled`_                                                          |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `mapped`_                                                            |
|             | - `read_only`_ (deprecated as of 2.8)                                  |
|             | - `required`_                                                          |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                          |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType` |
+-------------+------------------------------------------------------------------------+

Example Usage
-------------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    // ...

    $builder->add('public', CheckboxType::class, array(
        'label'    => 'Show this entry publicly?',
        'required' => false,
    ));

Field Options
-------------

.. include:: /reference/forms/types/options/value.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/checkbox_compound.rst.inc

.. include:: /reference/forms/types/options/checkbox_empty_data.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

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
