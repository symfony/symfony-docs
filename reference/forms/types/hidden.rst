.. index::
   single: Forms; Fields; hidden

hidden Field Type
=================

The hidden type represents a hidden input field.

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input`` ``hidden`` field                                           |
+-------------+----------------------------------------------------------------------+
| Inherited   | - ``data``                                                           |
| options     | - ``property_path``                                                  |
|             | - `read_only`_                                                       |
|             | - `disabled`_                                                        |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                           |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType` |
+-------------+----------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/property_path.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc