.. index::
   single: Forms; Fields; text

text Field Type
===============

The text field represents the most basic input text field.

+-------------+--------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                           |
+-------------+--------------------------------------------------------------------+
| Inherited   | - `max_length`_                                                    |
| options     | - `required`_                                                      |
|             | - `label`_                                                         |
|             | - `trim`_                                                          |
|             | - `read_only`_                                                     |
|             | - `error_bubbling`_                                                |
+-------------+--------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                         |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType` |
+-------------+--------------------------------------------------------------------+


Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
