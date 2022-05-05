.. index::
   single: Forms; Fields; TextareaType

TextareaType Field
==================

Renders a ``textarea`` HTML element.

+-------------+------------------------------------------------------------------------+
| Rendered as | ``textarea`` tag                                                       |
+-------------+------------------------------------------------------------------------+
| Parent type | :doc:`TextType </reference/forms/types/text>`                          |
+-------------+------------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType` |
+-------------+------------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

.. tip::

    If you prefer to use an **advanced WYSIWYG editor** instead of a plain
    ``<textarea>``, consider using the FOSCKEditorBundle community bundle. Read
    `its documentation`_ to learn how to integrate it in your Symfony application.

.. caution::

    When allowing users to type HTML code in the textarea (or using a
    WYSIWYG) editor, the application is vulnerable to XSS injection,
    clickjacking or CSS injection. Use the `sanitize_html`_ option to
    protect against these types of attacks.

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data_declaration.rst.inc

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data_description.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/sanitize_html.rst.inc

.. include:: /reference/forms/types/options/sanitizer.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. _`its documentation`: https://symfony.com/doc/current/bundles/FOSCKEditorBundle/index.html
