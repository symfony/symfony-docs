ColorType Field
===============

The ``ColorType`` field is a text field that is rendered using the HTML5
``<input type="color">`` tag. Depending on each browser, the behavior of this
form field can vary substantially. Some browsers display it as a simple text
field, while others display a native color picker.

The value of the underlying ``<input type="color">`` field is always a
7-character string specifying an RGB color in lower case hexadecimal notation.
That's why it's not possible to select semi-transparent colors with this
element.

+---------------------------+---------------------------------------------------------------------+
| Rendered as               | ``input`` ``color`` field (a text box)                              |
+---------------------------+---------------------------------------------------------------------+
| Default invalid message   | Please select a valid color.                                        |
+---------------------------+---------------------------------------------------------------------+
| Parent type               | :doc:`TextType </reference/forms/types/text>`                       |
+---------------------------+---------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ColorType` |
+---------------------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

html5
~~~~~

**type**: ``boolean`` **default**: ``false``

When this option is set to ``true``, the form type checks that its value matches
the `HTML5 color format`_ (``/^#[0-9a-f]{6}$/i``). If it doesn't match it,
you'll see the following error message: *"This value is not a valid HTML5 color"*.

Overridden Options
------------------

.. include:: /reference/forms/types/options/invalid_message.rst.inc

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

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. include:: /reference/forms/types/options/trim.rst.inc

.. _`HTML5 color format`: https://html.spec.whatwg.org/multipage/input.html#color-state-(type=color)
