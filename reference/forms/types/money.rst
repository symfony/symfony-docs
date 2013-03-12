.. index::
   single: Forms; Fields; money

money Field Type
================

Renders an input text field and specializes in handling submitted "money"
data.

This field type allows you to specify a currency, whose symbol is rendered
next to the text field. There are also several other options for customizing
how the input and output of the data is handled.

+-------------+---------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                            |
+-------------+---------------------------------------------------------------------+
| Options     | - `currency`_                                                       |
|             | - `divisor`_                                                        |
|             | - `precision`_                                                      |
|             | - `grouping`_                                                       |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `required`_                                                       |
| options     | - `label`_                                                          |
|             | - `read_only`_                                                      |
|             | - `disabled`_                                                       |
|             | - `error_bubbling`_                                                 |
|             | - `invalid_message`_                                                |
|             | - `invalid_message_parameters`_                                     |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`form</reference/forms/types/form>`                            |
+-------------+---------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\MoneyType` |
+-------------+---------------------------------------------------------------------+

Field Options
-------------

currency
~~~~~~~~

**type**: ``string`` **default**: ``EUR``

Specifies the currency that the money is being specified in. This determines
the currency symbol that should be shown by the text box. Depending on
the currency - the currency symbol may be shown before or after the input
text field.

This can also be set to false to hide the currency symbol.

divisor
~~~~~~~

**type**: ``integer`` **default**: ``1``

If, for some reason, you need to divide your starting value by a number
before rendering it to the user, you can use the ``divisor`` option.
For example::

    $builder->add('price', 'money', array(
        'divisor' => 100,
    ));

In this case, if the ``price`` field is set to ``9900``, then the value
``99`` will actually be rendered to the user. When the user submits the
value ``99``, it will be multiplied by ``100`` and ``9900`` will ultimately
be set back on your object.

precision
~~~~~~~~~

**type**: ``integer`` **default**: ``2``

For some reason, if you need some precision other than 2 decimal places,
you can modify this value. You probably won't need to do this unless,
for example, you want to round to the nearest dollar (set the precision
to ``0``).

.. include:: /reference/forms/types/options/grouping.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`form</reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc