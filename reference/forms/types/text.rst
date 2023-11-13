TextType Field
==============

The TextType field represents the most basic input text field.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                           |
+-------------+--------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                      |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType` |
+-------------+--------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

From an HTTP perspective, submitted data is always a string or an array of strings.
So by default, the form will treat any empty string as null. If you prefer to get
an empty string, explicitly set the ``empty_data`` option to an empty string.

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

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

.. include:: /reference/forms/types/options/sanitize_html.rst.inc

.. include:: /reference/forms/types/options/sanitizer.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc
