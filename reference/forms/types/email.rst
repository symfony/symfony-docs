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
|             | - `disabled`_                                                       |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `mapped`_                                                         |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/form>`                           |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType` |
+-------------+---------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`field</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
