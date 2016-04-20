.. index::
   single: Forms; Fields; textarea

textarea Field Type
===================

Renders a ``textarea`` HTML element.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``textarea`` tag                                                       |
+-------------+------------------------------------------------------------------------+
| Inherited   | - `attr`_                                                              |
| options     | - `data`_                                                              |
|             | - `disabled`_                                                          |
|             | - `empty_data`_                                                        |
|             | - `error_bubbling`_                                                    |
|             | - `error_mapping`_                                                     |
|             | - `label`_                                                             |
|             | - `label_attr`_                                                        |
|             | - `mapped`_                                                            |
|             | - `max_length`_                                                        |
|             | - `read_only`_                                                         |
|             | - `required`_                                                          |
|             | - `trim`_                                                              |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`text </reference/forms/types/text>`                              |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType` |
+-------------+------------------------------------------------------------------------+

.. tip::

    If you prefer to use an **advanced WYSIWYG editor** instead of a plain
    textarea, consider using the IvoryCKEditorBundle community bundle. Read
    `its documentation`_ to learn how to integrate it in your Symfony application.

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>`
type:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

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

.. _`its documentation`: https://symfony.com/doc/current/bundles/IvoryCKEditorBundle/index.html
