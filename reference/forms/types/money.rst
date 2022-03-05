.. index::
   single: Forms; Fields; MoneyType

MoneyType Field
===============

Renders an input text field and specializes in handling submitted "money"
data.

This field type allows you to specify a currency, whose symbol is rendered
next to the text field. There are also several other options for customizing
how the input and output of the data is handled.

+---------------------------+---------------------------------------------------------------------+
| Rendered as               | ``input`` ``text`` field                                            |
+---------------------------+---------------------------------------------------------------------+
| Default invalid message   | Please enter a valid money amount.                                  |
+---------------------------+---------------------------------------------------------------------+
| Legacy invalid message    | The value {{ value }} is not valid.                                 |
+---------------------------+---------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                       |
+---------------------------+---------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\MoneyType` |
+---------------------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

currency
~~~~~~~~

**type**: ``string`` **default**: ``EUR``

Specifies the currency that the money is being specified in. This determines
the currency symbol that should be shown by the text box. Depending on
the currency - the currency symbol may be shown before or after the input
text field.

This can be any `3 letter ISO 4217 code`_. You can also set this to false
to hide the currency symbol.

divisor
~~~~~~~

**type**: ``integer`` **default**: ``1``

If you need to divide your starting value by a number
before rendering it to the user, you can use the ``divisor`` option.
For example if you store prices as integer in order to avoid `rounding errors`_,
you can transform values in cents automatically::

    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    // ...

    $builder->add('price', MoneyType::class, [
        'divisor' => 100,
    ]);

In this case, if the ``price`` field is set to ``9900``, then the value
``99`` will actually be rendered to the user. When the user submits the
value ``99``, it will be multiplied by ``100`` and ``9900`` will ultimately
be set back on your object.

.. include:: /reference/forms/types/options/grouping.rst.inc

.. include:: /reference/forms/types/options/rounding_mode.rst.inc

html5
~~~~~

**type**: ``boolean`` **default**: ``false``

If set to ``true``, the HTML input will be rendered as a native HTML5
``<input type="number">`` element.

.. caution::

    As HTML5 number format is normalized, it is incompatible with ``grouping`` option.

scale
~~~~~

**type**: ``integer`` **default**: ``2``

If, for some reason, you need some scale other than 2 decimal places,
you can modify this value. You probably won't need to do this unless,
for example, you want to round to the nearest dollar (set the scale
to ``0``).

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

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

Form Variables
--------------

=============  ==========  ===============================================================
Variable       Type        Usage
=============  ==========  ===============================================================
money_pattern  ``string``  The format to use to display the money, including the currency.
=============  ==========  ===============================================================

.. _`3 letter ISO 4217 code`: https://en.wikipedia.org/wiki/ISO_4217
.. _`rounding errors`: https://0.30000000000000004.com/
