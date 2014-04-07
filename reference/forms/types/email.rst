.. index::
   single: Forms; Fields; email

email Field Type
================

The ``email`` field is a text field that is rendered using the HTML5
``<input type="email" />`` tag.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``email`` field (a text box)                              |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `max_length`_ (deprecated as of 2.5)                              |
| options     | - `empty_data`_                                                     |
|             | - `required`_                                                       |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `data`_                                                           |
|             | - `trim`_                                                           |
|             | - `read_only`_                                                      |
|             | - `disabled`_                                                       |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `mapped`_                                                         |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                           |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

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
