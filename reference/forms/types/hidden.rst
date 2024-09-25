HiddenType Field
================

The hidden type represents a hidden input field.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | ``input`` ``hidden`` field                                           |
+---------------------------+----------------------------------------------------------------------+
| Default invalid message   | The hidden field is invalid.                                         |
+---------------------------+----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                        |
+---------------------------+----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType` |
+---------------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

``error_bubbling``
~~~~~~~~~~~~~~~~~~

**default**: ``true``

Pass errors to the root form, otherwise they will not be visible.

.. include:: /reference/forms/types/options/invalid_message.rst.inc

``required``
~~~~~~~~~~~~

**default**: ``false``

Hidden fields cannot have a required attribute.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/property_path.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
