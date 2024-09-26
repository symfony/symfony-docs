NumberType Field
================

Renders an input text field and specializes in handling number input. This
type offers different options for the scale, rounding and grouping
that you want to use for your number.

+---------------------------+----------------------------------------------------------------------+
| Rendered as               | ``input`` ``text`` field                                             |
+---------------------------+----------------------------------------------------------------------+
| Default invalid message   | Please enter a number.                                               |
+---------------------------+----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                        |
+---------------------------+----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType` |
+---------------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/grouping.rst.inc

html5
~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, the HTML input will be rendered as a native HTML5 ``type="number"``
form.

input
~~~~~

**type**: ``string`` **default**: ``number``

The format of the input data - i.e. the format that the number is stored on
your underlying object. Valid values are ``number`` and ``string``. Setting
this option to ``string`` can be useful if the underlying data is a string
for precision reasons (for example, Doctrine uses strings for the ``decimal``
type).

scale
~~~~~

**type**: ``integer`` **default**: Locale-specific (usually around ``3``)

This specifies how many decimals will be allowed until the field rounds
the submitted value (via ``rounding_mode``). For example, if ``scale`` is set
to ``2``, a submitted value of ``20.123`` will be rounded to, for example,
``20.12`` (depending on your `rounding_mode`_).

.. include:: /reference/forms/types/options/rounding_mode.rst.inc

When the ``html5`` option is set to ``false``, the ``<input>`` element will
include an `inputmode HTML attribute`_ which depends on the value of this option.
If the ``scale`` value is ``0``, ``inputmode`` will be ``numeric``; if ``scale``
is set to any value greater than ``0``, ``inputmode`` will be ``decimal``.

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

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

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_html.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

.. _`inputmode HTML attribute`: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/inputmode
