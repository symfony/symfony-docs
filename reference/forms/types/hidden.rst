.. index::
   single: Forms; Fields; hidden

HiddenType Field
================

The hidden type represents a hidden input field.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | ``input`` ``hidden`` field                                           |
+---------------------------+----------------------------------------------------------------------+
| Overridden options        | - `compound`_                                                        |
|                           | - `error_bubbling`_                                                  |
|                           | - `invalid_message`_                                                 |
|                           | - `required`_                                                        |
+---------------------------+----------------------------------------------------------------------+
| Inherited options         | - `attr`_                                                            |
|                           | - `data`_                                                            |
|                           | - `empty_data`_                                                      |
|                           | - `error_mapping`_                                                   |
|                           | - `mapped`_                                                          |
|                           | - `property_path`_                                                   |
|                           | - `row_attr`_                                                        |
+---------------------------+----------------------------------------------------------------------+
| Default invalid message   | The hidden field is invalid.                                         |
+---------------------------+----------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                  |
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

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/property_path.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc
