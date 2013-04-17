.. index::
   single: Forms; Fields; reset

reset Field Type
================

.. versionadded:: 2.3
    The ``reset`` type was added in Symfony 2.3

A button that resets all fields to their original values.

+----------------------+---------------------------------------------------------------------+
| Rendered as          | ``input`` ``reset`` tag                                             |
+----------------------+---------------------------------------------------------------------+
| Inherited            | - `attr`_                                                           |
| options              | - `disabled`_                                                       |
|                      | - `label`_                                                          |
|                      | - `translation_domain`_                                             |
+----------------------+---------------------------------------------------------------------+
| Parent type          | :doc:`button</reference/forms/types/button>`                        |
+----------------------+---------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType` |
+----------------------+---------------------------------------------------------------------+

Inherited options
-----------------

.. include:: /reference/forms/types/options/button_attr.rst.inc

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc
