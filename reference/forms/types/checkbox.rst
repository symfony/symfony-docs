.. index::
   single: Forms; Fields; CheckboxType

CheckboxType Field
==================

Creates a single input checkbox. This should always be used for a field
that has a boolean value: if the box is checked, the field will be set to
true, if the box is unchecked, the value will be set to false. Optionally
you can specify an array of values that, if submitted, will be evaluated
to "false" as well (this differs from what HTTP defines, but can be handy
if you want to handle submitted values like "0" or "false").

+---------------------------+------------------------------------------------------------------------+
| Rendered as               | ``input`` ``checkbox`` field                                           |
+---------------------------+------------------------------------------------------------------------+
| Options                   | - `false_values`_                                                      |
|                           | - `value`_                                                             |
+---------------------------+------------------------------------------------------------------------+
| Overridden options        | - `compound`_                                                          |
|                           | - `empty_data`_                                                        |
|                           | - `invalid_message`_                                                   |
+---------------------------+------------------------------------------------------------------------+
| Inherited options         | - `attr`_                                                              |
|                           | - `data`_                                                              |
|                           | - `disabled`_                                                          |
|                           | - `error_bubbling`_                                                    |
|                           | - `error_mapping`_                                                     |
|                           | - `help`_                                                              |
|                           | - `help_attr`_                                                         |
|                           | - `help_html`_                                                         |
|                           | - `label`_                                                             |
|                           | - `label_attr`_                                                        |
|                           | - `label_format`_                                                      |
|                           | - `mapped`_                                                            |
|                           | - `required`_                                                          |
|                           | - `row_attr`_                                                          |
+---------------------------+------------------------------------------------------------------------+
| Default invalid message   | The checkbox has an invalid value.                                     |
+---------------------------+------------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                    |
+---------------------------+------------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                          |
+---------------------------+------------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType` |
+---------------------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Example Usage
-------------

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    // ...

    $builder->add('public', CheckboxType::class, [
        'label'    => 'Show this entry publicly?',
        'required' => false,
    ]);

Field Options
-------------

false_values
~~~~~~~~~~~~

**type**: ``array`` **default**: ``[null]``

An array of values to be interpreted as ``false``.

.. include:: /reference/forms/types/options/value.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/checkbox_compound.rst.inc

.. include:: /reference/forms/types/options/checkbox_empty_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

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

.. include:: /reference/forms/types/options/row_attr.rst.inc

Form Variables
--------------

.. include:: /reference/forms/types/variables/check_or_radio_table.rst.inc
