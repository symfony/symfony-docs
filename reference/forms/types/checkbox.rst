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
| Default invalid message   | The checkbox has an invalid value.                                     |
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

Handling Unchecked Checkboxes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When a form contains only checkboxes, unchecked checkboxes are omitted from the
HTTP request. In previous Symfony versions, it was impossible to distinguish between
a user visiting the page and a user submitting the form with all checkboxes unchecked.

The Form component now automatically detects forms that contain only checkboxes
and marks them as submitted even when all checkboxes are unchecked::

    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    // ...

    // form with a single checkbox, default data = true
    $form = $factory->createNamed('agree', CheckboxType::class, true);

    // POST request with no body (all checkboxes unchecked)
    $form->handleRequest($request);

    // before Symfony 8.1: form was never submitted, data stayed true
    // since Symfony 8.1:  form is submitted, data is false

This behavior also applies to collections of checkboxes and nested forms that
contain only checkboxes. ``PATCH`` requests are excluded, since partial updates
must leave omitted fields unchanged.

.. versionadded:: 8.1

    Support for submitting forms with all checkboxes unchecked was introduced in Symfony 8.1.

Field Options
-------------

.. include:: /reference/forms/types/options/false_values.rst.inc

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

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Form Variables
--------------

.. include:: /reference/forms/types/variables/check_or_radio_table.rst.inc
