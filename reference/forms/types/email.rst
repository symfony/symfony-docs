.. index::
   single: Forms; Fields; email

email Field Type
================

The ``email`` field is a text field that is rendered using the HTML5
``<input type="email" />`` tag.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``email`` field (a text box)                              |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `max_length`_                                                     |
| options     | - `required`_                                                       |
|             | - `label`_                                                          |
|             | - `trim`_                                                           |
|             | - `read_only`_                                                      |
|             | - `error_bubbling`_                                                 |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                          |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
