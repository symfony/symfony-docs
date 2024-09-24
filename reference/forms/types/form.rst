FormType Field
==============

The ``FormType`` predefines a couple of options that are then available
on all types for which ``FormType`` is the parent.

+---------------------------+--------------------------------------------------------------------+
| Default invalid message   | This value is not valid.                                           |
+---------------------------+--------------------------------------------------------------------+
| Parent                    | none                                                               |
+---------------------------+--------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType` |
+---------------------------+--------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. _form-option-action:

.. include:: /reference/forms/types/options/action.rst.inc

.. _form-option-allow-extra-fields:

``allow_extra_fields``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Usually, if you submit extra fields that aren't configured in your form,
you'll get a "This form should not contain extra fields." validation error.

You can silence this validation error by enabling the ``allow_extra_fields``
option on the form.

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/compound.rst.inc

.. _reference-form-option-constraints:

.. include:: /reference/forms/types/options/constraints.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/data_class.rst.inc

.. _reference-form-option-empty-data:

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The actual default value of this option depends on other field options:

* If ``data_class`` is set and ``required`` is ``true``, then ``new $data_class()``;
* If ``data_class`` is set and ``required`` is ``false``, then ``null``;
* If ``data_class`` is not set and ``compound`` is ``true``, then ``[]``
  (empty array);
* If ``data_class`` is not set and ``compound`` is ``false``, then ``''``
  (empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

``is_empty_callback``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``callable`` **default**: ``null``

This callable takes form data and returns whether value is considered empty.

.. _reference-form-option-error-bubbling:

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/extra_fields_message.rst.inc

.. include:: /reference/forms/types/options/form_attr.rst.inc

``getter``
~~~~~~~~~~

**type**: ``callable`` **default**: ``null``

When provided, this callable will be invoked to read the value from
the underlying object that will be used to populate the form field.

More details are available in the section on :doc:`/form/data_mappers`.

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/help_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. _reference-form-option-mapped:

.. include:: /reference/forms/types/options/mapped.rst.inc

.. _form-option-method:

.. include:: /reference/forms/types/options/method.rst.inc

.. include:: /reference/forms/types/options/post_max_size_message.rst.inc

.. _reference-form-option-property-path:

.. include:: /reference/forms/types/options/property_path.rst.inc

.. _reference-form-option-required:

.. include:: /reference/forms/types/options/required.rst.inc

``setter``
~~~~~~~~~~

**type**: ``callable`` **default**: ``null``

When provided, this callable will be invoked to map the form value
back to the underlying object.

More details are available in the section on :doc:`/form/data_mappers`.

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/validation_groups.rst.inc

Inherited Options
-----------------

The following options are defined in the
:class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BaseType` class.
The ``BaseType`` class is the parent class for both the ``form`` type and
the :doc:`ButtonType </reference/forms/types/button>`, but it is not part
of the form type tree (i.e. it cannot be used as a form type on its own).

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/auto_initialize.rst.inc

.. include:: /reference/forms/types/options/block_name.rst.inc

.. include:: /reference/forms/types/options/block_prefix.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. _reference-form-option-label:

.. include:: /reference/forms/types/options/label.rst.inc

.. _reference-form-option-label-html:

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/translation_domain.rst.inc

.. include:: /reference/forms/types/options/label_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/priority.rst.inc
