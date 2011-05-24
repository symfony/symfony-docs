.. index::
   single: Forms; Fields; search

search Field Type
=================

The ``search`` field is a text field that is used for the entry of searches,
and is rendered by default using a ``search`` input type which can be used
by user agents to present a specialized rendering for search boxes (such
as the `one used in WebKit-based browsers`_).

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input search`` field                                               |
+-------------+----------------------------------------------------------------------+
| Inherited   | - ``max_length``                                                     |
| options     | - ``required``                                                       |
|             | - ``label``                                                          |
|             | - ``read_only``                                                      |
|             | - ``trim``                                                           |
|             | - ``error_bubbling``                                                 |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`text</reference/forms/types/text>`                             |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SearchType` |
+-------------+----------------------------------------------------------------------+

Options
-------

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. _`one used in WebKit-based browsers`: http://alexking.org/blog/2006/11/12/safari-search-boxes
