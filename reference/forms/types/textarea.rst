.. index::
   single: Forms; Fields; textarea

textarea Field Type
===================

Renders a ``textarea`` HTML element. 

+-------------+------------------------------------------------------------------------+
| Rendered as | ``textarea`` field                                                     |
+-------------+------------------------------------------------------------------------+
| Options     | - ``max_length``                                                       |
+-------------+------------------------------------------------------------------------+
| Inherited   | - ``trim``                                                             |
| options     | - ``required``                                                         |
|             | - ``label``                                                            |
|             | - ``read_only``                                                        |
|             | - ``error_bubbling``                                                   |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                             |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType` |
+-------------+------------------------------------------------------------------------+

Options
-------

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
