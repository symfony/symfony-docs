.. index::
   single: Forms; Fields; FormType

FormType Field
==============

The ``FormType`` predefines a couple of options that are then available
on all types for which ``FormType`` is the parent.

+-----------+--------------------------------------------------------------------+
| Options   | - `action`_                                                        |
|           | - `allow_extra_fields`_                                            |
|           | - `by_reference`_                                                  |
|           | - `cascade_validation`_ (deprecated as of 2.8)                     |
|           | - `compound`_                                                      |
|           | - `constraints`_                                                   |
|           | - `data`_                                                          |
|           | - `data_class`_                                                    |
|           | - `empty_data`_                                                    |
|           | - `error_bubbling`_                                                |
|           | - `error_mapping`_                                                 |
|           | - `extra_fields_message`_                                          |
|           | - `inherit_data`_                                                  |
|           | - `invalid_message`_                                               |
|           | - `invalid_message_parameters`_                                    |
|           | - `label_attr`_                                                    |
|           | - `mapped`_                                                        |
|           | - `max_length`_ (deprecated as of 2.5)                             |
|           | - `method`_                                                        |
|           | - `pattern`_ (deprecated as of 2.5)                                |
|           | - `post_max_size_message`_                                         |
|           | - `property_path`_                                                 |
|           | - `read_only`_ (deprecated as of 2.8)                              |
|           | - `required`_                                                      |
|           | - `trim`_                                                          |
+-----------+--------------------------------------------------------------------+
| Inherited | - `attr`_                                                          |
| options   | - `auto_initialize`_                                               |
|           | - `block_name`_                                                    |
|           | - `disabled`_                                                      |
|           | - `label`_                                                         |
|           | - `translation_domain`_                                            |
+-----------+--------------------------------------------------------------------+
| Parent    | none                                                               |
+-----------+--------------------------------------------------------------------+
| Class     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType` |
+-----------+--------------------------------------------------------------------+

Field Options
-------------

.. _form-option-action:

.. include:: /reference/forms/types/options/action.rst.inc

allow_extra_fields
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

Usually, if you submit extra fields that aren't configured in your form,
you'll get a "This form should not contain extra fields." validation error.

You can silence this validation error by enabling the ``allow_extra_fields``
option on the form.

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/cascade_validation.rst.inc

.. include:: /reference/forms/types/options/compound.rst.inc

.. include:: /reference/forms/types/options/constraints.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/data_class.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The actual default value of this option depends on other field options:

* If ``data_class`` is set and ``required`` is ``true``, then ``new $data_class()``;
* If ``data_class`` is set and ``required`` is ``false``, then ``null``;
* If ``data_class`` is not set and ``compound`` is ``true``, then ``array()``
  (empty array);
* If ``data_class`` is not set and ``compound`` is ``false``, then ``''``
  (empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. _reference-form-option-error-bubbling:

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/extra_fields_message.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. _reference-form-option-mapped:

.. include:: /reference/forms/types/options/mapped.rst.inc

.. _reference-form-option-max_length:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. _form-option-method:

.. include:: /reference/forms/types/options/method.rst.inc

.. _reference-form-option-pattern:

.. include:: /reference/forms/types/options/pattern.rst.inc

.. include:: /reference/forms/types/options/post_max_size_message.rst.inc

.. _reference-form-option-property-path:

.. include:: /reference/forms/types/options/property_path.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. _reference-form-option-required:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

Inherited Options
-----------------

The following options are defined in the
:class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BaseType` class.
The ``BaseType`` class is the parent class for both the ``form`` type and
the :doc:`ButtonType </reference/forms/types/button>`, but it is not part
of the form type tree (i.e. it can not be used as a form type on its own).

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/auto_initialize.rst.inc

.. include:: /reference/forms/types/options/block_name.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/translation_domain.rst.inc
