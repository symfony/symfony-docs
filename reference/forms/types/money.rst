.. index::
   single: Forms; Fields; MoneyType

MoneyType Field
===============

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
|             | - `grouping`_                                                       |
|             | - `scale`_                                                          |
+-------------+---------------------------------------------------------------------+
| Overridden  | - `compound`_                                                       |
| options     |                                                                     |
+-------------+---------------------------------------------------------------------+
| Inherited   | - `data`_                                                           |
| options     | - `disabled`_                                                       |
|             | - `empty_data`_                                                     |
|             | - `error_bubbling`_                                                 |
|             | - `error_mapping`_                                                  |
|             | - `invalid_message`_                                                |
|             | - `invalid_message_parameters`_                                     |
|             | - `label`_                                                          |
|             | - `label_attr`_                                                     |
|             | - `mapped`_                                                         |
|             | - `read_only`_ (deprecated as of 2.8)                               |
|             | - `required`_                                                       |
+-------------+---------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                       |
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

This can be any `3 letter ISO 4217 code`_. You can also set this to false
to hide the currency symbol.

divisor
~~~~~~~

**type**: ``integer`` **default**: ``1``

If, for some reason, you need to divide your starting value by a number
before rendering it to the user, you can use the ``divisor`` option.
For example::

    use Symfony\Component\Form\Extension\Core\Type\MoneyType;
    // ...

    $builder->add('price', MoneyType::class, array(
        'divisor' => 100,
    ));

In this case, if the ``price`` field is set to ``9900``, then the value
``99`` will actually be rendered to the user. When the user submits the
value ``99``, it will be multiplied by ``100`` and ``9900`` will ultimately
be set back on your object.

.. include:: /reference/forms/types/options/grouping.rst.inc

scale
~~~~~

.. versionadded:: 2.7
    The ``scale`` option was introduced in Symfony 2.7. Prior to Symfony 2.7,
    it was known as ``precision``.

**type**: ``integer`` **default**: ``2``

If, for some reason, you need some scale other than 2 decimal places,
you can modify this value. You probably won't need to do this unless,
for example, you want to round to the nearest dollar (set the scale
to ``0``).

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`FormType </reference/forms/types/form>`:

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :end-before: DEFAULT_PLACEHOLDER

The default value is ``''`` (the empty string).

.. include:: /reference/forms/types/options/empty_data.rst.inc
    :start-after: DEFAULT_PLACEHOLDER

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

Form Variables
--------------

=============  ==========  ===============================================================
Variable       Type        Usage
=============  ==========  ===============================================================
money_pattern  ``string``  The format to use to display the money, including the currency.
=============  ==========  ===============================================================

.. _`3 letter ISO 4217 code`: https://en.wikipedia.org/wiki/ISO_4217
