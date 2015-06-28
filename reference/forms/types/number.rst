.. index::
   single: Forms; Fields; number

number Field Type
=================

Renders an input text field and specializes in handling number input. This
type offers different options for the precision, rounding and grouping
that you want to use for your number.

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                             |
+-------------+----------------------------------------------------------------------+
| Options     | - `grouping`_                                                        |
|             | - `precision`_                                                       |
|             | - `rounding_mode`_                                                   |
+-------------+----------------------------------------------------------------------+
| Overridden  | - `compound`_                                                        |
| options     |                                                                      |
+-------------+----------------------------------------------------------------------+
| Inherited   | - `data`_                                                            |
| options     | - `disabled`_                                                        |
|             | - `empty_data`_                                                      |
|             | - `error_bubbling`_                                                  |
|             | - `error_mapping`_                                                   |
|             | - `invalid_message`_                                                 |
|             | - `invalid_message_parameters`_                                      |
|             | - `label`_                                                           |
|             | - `label_attr`_                                                      |
|             | - `mapped`_                                                          |
|             | - `read_only`_                                                       |
|             | - `required`_                                                        |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                            |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType` |
+-------------+----------------------------------------------------------------------+

Field Options
-------------

.. include:: /reference/forms/types/options/grouping.rst.inc

.. include:: /reference/forms/types/options/precision.rst.inc

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``IntegerToLocalizedStringTransformer::ROUND_HALFUP``

If a submitted number needs to be rounded (based on the ``precision``
option), you have several configurable options for that rounding. Each
option is a constant on the
:class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\IntegerToLocalizedStringTransformer`:

* ``IntegerToLocalizedStringTransformer::ROUND_DOWN`` Rounding mode to
  round towards zero.

* ``IntegerToLocalizedStringTransformer::ROUND_FLOOR`` Rounding mode to
  round towards negative infinity.

* ``IntegerToLocalizedStringTransformer::ROUND_UP`` Rounding mode to round
  away from zero.

* ``IntegerToLocalizedStringTransformer::ROUND_CEILING`` Rounding mode
  to round towards positive infinity.

* ``IntegerToLocalizedStringTransformer::ROUND_HALFDOWN`` Rounding mode
  to round towards "nearest neighbor" unless both neighbors are equidistant,
  in which case round down.

* ``IntegerToLocalizedStringTransformer::ROUND_HALFEVEN`` Rounding mode
  to round towards the "nearest neighbor" unless both neighbors are equidistant,
  in which case, round towards the even neighbor.

* ``IntegerToLocalizedStringTransformer::ROUND_HALFUP`` Rounding mode
  to round towards "nearest neighbor" unless both neighbors are equidistant,
  in which case round up.

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>`
type:

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
