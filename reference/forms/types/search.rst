.. index::
   single: Forms; Fields; search

search Field Type
=================

This renders an ``<input type="search" />`` field, which is a text box with
special functionality supported by some browsers.

Read about the input search field at `DiveIntoHTML5.info`_

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input search`` field                                               |
+-------------+----------------------------------------------------------------------+
| Inherited   | - `max_length`_                                                      |
| options     | - `required`_                                                        |
|             | - `label`_                                                           |
|             | - `trim`_                                                            |
|             | - `read_only`_                                                       |
|             | - `error_bubbling`_                                                  |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`text</reference/forms/types/text>`                             |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SearchType` |
+-------------+----------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. _`DiveIntoHTML5.info`: http://diveintohtml5.info/forms.html#type-search
