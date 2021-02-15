.. index::
   single: Forms; Fields; UuidType

UuidType Field
==============

.. versionadded:: 5.3

    The ``UuidType`` field was introduced in Symfony 5.3.

Renders an input text field with the UUID string value and transforms it back to
a proper :ref:`Uuid object <uuid>` when submitting the form.

+---------------------------+-----------------------------------------------------------------------+
| Rendered as               | ``input`` ``text`` field                                              |
+---------------------------+-----------------------------------------------------------------------+
| Options                   | (none)                                                                |
+---------------------------+-----------------------------------------------------------------------+
| Overridden options        | - `compound`_                                                         |
|                           | - `invalid_message`_                                                  |
+---------------------------+-----------------------------------------------------------------------+
| Inherited options         | - `attr`_                                                             |
|                           | - `data`_                                                             |
|                           | - `disabled`_                                                         |
|                           | - `empty_data`_                                                       |
|                           | - `error_bubbling`_                                                   |
|                           | - `error_mapping`_                                                    |
|                           | - `help`_                                                             |
|                           | - `help_attr`_                                                        |
|                           | - `help_html`_                                                        |
|                           | - `invalid_message_parameters`_                                       |
|                           | - `label`_                                                            |
|                           | - `label_attr`_                                                       |
|                           | - `label_format`_                                                     |
|                           | - `mapped`_                                                           |
|                           | - `required`_                                                         |
|                           | - `row_attr`_                                                         |
+---------------------------+-----------------------------------------------------------------------+
| Default invalid message   | Please enter a valid UUID.                                            |
+---------------------------+-----------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                   |
+---------------------------+-----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                         |
+---------------------------+-----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\UuidType`    |
+---------------------------+-----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
