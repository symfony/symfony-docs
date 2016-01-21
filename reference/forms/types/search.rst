.. index::
   single: Forms; Fields; SearchType

SearchType Field
================

This renders an ``<input type="search" />`` field, which is a text box with
special functionality supported by some browsers.

Read about the input search field at `DiveIntoHTML5.info`_

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input search`` field                                               |
+-------------+----------------------------------------------------------------------+
| Inherited   | - `disabled`_                                                        |
| options     | - `empty_data`_                                                      |
|             | - `error_bubbling`_                                                  |
|             | - `error_mapping`_                                                   |
|             | - `label`_                                                           |
|             | - `label_attr`_                                                      |
|             | - `mapped`_                                                          |
|             | - `max_length`_ (deprecated as of 2.5)                               |
|             | - `read_only`_ (deprecated as of 2.8)                                |
|             | - `required`_                                                        |
|             | - `trim`_                                                            |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                        |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SearchType` |
+-------------+----------------------------------------------------------------------+

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/max_length.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. _`DiveIntoHTML5.info`: http://diveintohtml5.info/forms.html#type-search
