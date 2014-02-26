.. index::
   single: Forms; Fields; form

form Field Type
===============

The ``form`` type predefines a couple of options that are then available
on all types for which ``form`` is the parent type.

+---------+--------------------------------------------------------------------+
| Options | - `compound`_                                                      |
|         | - `data`_                                                          |
|         | - `data_class`_                                                    |
|         | - `empty_data`_                                                    |
|         | - `required`_                                                      |
|         | - `label`_                                                         |
|         | - `label_attr`_                                                    |
|         | - `constraints`_                                                   |
|         | - `cascade_validation`_                                            |
|         | - `read_only`_                                                     |
|         | - `disabled`_                                                      |
|         | - `trim`_                                                          |
|         | - `mapped`_                                                        |
|         | - `property_path`_                                                 |
|         | - `attr`_                                                          |
|         | - `translation_domain`_                                            |
|         | - `block_name`_                                                    |
|         | - `max_length`_                                                    |
|         | - `by_reference`_                                                  |
|         | - `error_bubbling`_                                                |
|         | - `inherit_data`_                                                  |
|         | - `error_mapping`_                                                 |
|         | - `invalid_message`_                                               |
|         | - `invalid_message_parameters`_                                    |
|         | - `extra_fields_message`_                                          |
|         | - `post_max_size_message`_                                         |
|         | - `pattern`_                                                       |
+---------+--------------------------------------------------------------------+
| Parent  | none                                                               |
+---------+--------------------------------------------------------------------+
| Class   | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType` |
+---------+--------------------------------------------------------------------+

Options
-------

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/data_class.rst.inc

.. include:: /reference/forms/types/options/action.rst.inc

.. include:: /reference/forms/types/options/method.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/compound.rst.inc

.. _reference-form-option-required:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/constraints.rst.inc

.. include:: /reference/forms/types/options/cascade_validation.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/property_path.rst.inc

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/translation_domain.rst.inc

.. include:: /reference/forms/types/options/block_name.rst.inc

.. _reference-form-option-max_length:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/by_reference.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/inherit_data.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/extra_fields_message.rst.inc

.. include:: /reference/forms/types/options/post_max_size_message.rst.inc

.. _reference-form-option-pattern:

.. include:: /reference/forms/types/options/pattern.rst.inc
