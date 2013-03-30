.. index::
   single: Forms; Fields; hidden

hidden Field Type
=================

The hidden type represents a hidden input field.

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input`` ``hidden`` field                                           |
+-------------+----------------------------------------------------------------------+
| Overriden   | - `required`_                                                        |
| Options     | - `error_bubbling`_                                                  |
+-------------+----------------------------------------------------------------------+
| Inherited   | - `data`_                                                            |
| options     | - `property_path`_                                                   |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                           |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType` |
+-------------+----------------------------------------------------------------------+

Overriden Options
-----------------

required
~~~~~~~~

**default**: ``false``

Hidden fields cannot have a required attribute.

error_bubbling
~~~~~~~~~~~~~~

**default**: ``true``

Pass errors to the root form, otherwise they will not be visible.

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/property_path.rst.inc
