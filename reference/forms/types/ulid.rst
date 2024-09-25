UlidType Field
==============

Renders an input text field with the ULID string value and transforms it back to
a proper :ref:`Ulid object <ulid>` when submitting the form.

+---------------------------+-----------------------------------------------------------------------+
| Rendered as               | ``input`` ``text`` field                                              |
+---------------------------+-----------------------------------------------------------------------+
| Default invalid message   | Please enter a valid ULID.                                            |
+---------------------------+-----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                         |
+---------------------------+-----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\UlidType`    |
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

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
