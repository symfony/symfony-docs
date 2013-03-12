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
|             | - `disabled`_                                                      |
|             | - `error_bubbling`_                                                |
+-------------+--------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/form>`                           |
+-------------+--------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType` |
+-------------+--------------------------------------------------------------------+


Inherited Options
-----------------

These options inherit from the :doc:`form</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
