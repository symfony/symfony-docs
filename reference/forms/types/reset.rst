.. index::
   single: Forms; Fields; ResetType

ResetType Field
===============

A button that resets all fields to their original values.

+----------------------+---------------------------------------------------------------------+
| Rendered as          | ``input`` ``reset`` tag                                             |
+----------------------+---------------------------------------------------------------------+
| Inherited            | - `attr`_                                                           |
| options              | - `disabled`_                                                       |
|                      | - `label`_                                                          |
|                      | - `label_attr`_                                                     |
|                      | - `translation_domain`_                                             |
+----------------------+---------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType </reference/forms/types/button>`                   |
+----------------------+---------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType` |
+----------------------+---------------------------------------------------------------------+

Inherited Options
-----------------

Note theses examples come from the ButtonType documentation. If you want to use the ResetType field, you have to use ResetType::class instead of ButtonType::class to generate a reset button.

.. include:: /reference/forms/types/options/button_attr.rst.inc

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc
