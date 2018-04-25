.. index::
   single: Forms; Fields; TextType

TextType Field
==============

The TextType field represents the most basic input text field.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                           |
+-------------+--------------------------------------------------------------------+
| Inherited   | - `data`_                                                          |
| options     | - `disabled`_                                                      |
|             | - `empty_data`_                                                    |
|             | - `error_bubbling`_                                                |
|             | - `error_mapping`_                                                 |
|             | - `help`_                                                          |
|             | - `label`_                                                         |
|             | - `label_attr`_                                                    |
|             | - `label_format`_                                                  |
|             | - `mapped`_                                                        |
|             | - `required`_                                                      |
|             | - `trim`_                                                          |
+-------------+--------------------------------------------------------------------+
| Overridden  | - `compound`_                                                      |
| options     |                                                                    |
+-------------+--------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                      |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType` |
+-------------+--------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc
