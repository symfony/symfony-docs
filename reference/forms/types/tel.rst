.. index::
   single: Forms; Fields; tel

Tel Field Type
===============

.. versionadded:: 2.6
    The Tel field type was introduced in Symfony 2.7.

The tel field represents a text field for entering a telephone number.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``tel`` field                                            |
+-------------+--------------------------------------------------------------------+
| Inherited   | - `empty_data`_                                                    |
| options     | - `required`_                                                      |
|             | - `label`_                                                         |
|             | - `label_attr`_                                                    |
|             | - `data`_                                                          |
|             | - `trim`_                                                          |
|             | - `read_only`_                                                     |
|             | - `disabled`_                                                      |
|             | - `error_bubbling`_                                                |
|             | - `error_mapping`_                                                 |
|             | - `mapped`_                                                        |
+-------------+--------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                          |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TelType`  |
+-------------+--------------------------------------------------------------------+


Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
