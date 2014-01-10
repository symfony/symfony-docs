.. index::
   single: Forms; Fields; button

button Field Type
=================

.. versionadded:: 2.3
    The ``button`` type was added in Symfony 2.3

A simple, non-responsive button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` tag                                                       |
+----------------------+----------------------------------------------------------------------+
| Options              | - `attr`_                                                            |
|                      | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `label_attr`_                                                      |
|                      | - `translation_domain`_                                              |
+----------------------+----------------------------------------------------------------------+
| Overridden Options   | - `auto_initialize`                                                  |
+----------------------+----------------------------------------------------------------------+
| Parent type          | none                                                                 |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType` |
+----------------------+----------------------------------------------------------------------+

Options
-------

.. include:: /reference/forms/types/options/button_attr.rst.inc

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/button_auto_initialize.rst.inc
