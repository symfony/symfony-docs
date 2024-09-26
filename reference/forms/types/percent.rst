PercentType Field
=================

The ``PercentType`` renders an input text field and specializes in handling
percentage data. If your percentage data is stored as a decimal (e.g. ``0.95``),
you can use this field out-of-the-box. If you store your data as a number
(e.g. ``95``), you should set the ``type`` option to ``integer``.

When ``symbol`` is not ``false``, the field will render the given string after
the input.

+---------------------------+-----------------------------------------------------------------------+
| Rendered as               | ``input`` ``text`` field                                              |
+---------------------------+-----------------------------------------------------------------------+
| Default invalid message   | Please enter a percentage value.                                      |
+---------------------------+-----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                         |
+---------------------------+-----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\PercentType` |
+---------------------------+-----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/rounding_mode.rst.inc

html5
~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, the HTML input will be rendered as a native HTML5
``<input type="number">`` element.

scale
~~~~~

**type**: ``integer`` **default**: ``0``

This specifies how many decimals will be allowed until the field rounds
the submitted value (via ``rounding_mode``). For example, if ``scale`` is set
to ``2``, a submitted value of ``20.123`` will be rounded to, for example,
``20.12`` (depending on your `rounding_mode`_).

symbol
~~~~~~

**type**: ``boolean`` or ``string`` **default**: ``%``

By default, fields are rendered with a percentage sign ``%`` after the input.
Setting the value to ``false`` will not display the percentage sign. Setting the
value to a ``string`` (e.g. ``‱``), will show that string instead of the default
``%`` sign.

type
~~~~

**type**: ``string`` **default**: ``fractional``

This controls how your data is stored on your object. For example, a percentage
corresponding to "55%", might be stored as ``0.55`` or ``55`` on your
object. The two "types" handle these two cases:

*   ``fractional``
    If your data is stored as a decimal (e.g. ``0.55``), use this type.
    The data will be multiplied by ``100`` before being shown to the
    user (e.g. ``55``). The submitted data will be divided by ``100``
    on form submit so that the decimal value is stored (``0.55``);

*   ``integer``
    If your data is stored as an integer (e.g. 55), then use this option.
    The raw value (``55``) is shown to the user and stored on your object.
    Note that this only works for integer values.

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
