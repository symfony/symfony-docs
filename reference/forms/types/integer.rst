.. index::
   single: Forms; Fields; integer

integer Field Type
==================

Renders an input "number" field. Basically, this is a text field that's good
at handling data that's in an integer form. The input ``number`` field looks
like a text box, except that - if the user's browser supports HTML5 - it will
have some extra frontend functionality.

This field has different options on how to handle input values that aren't
integers. By default, all non-integer values (e.g. 6.78) will round down (e.g. 6).

+-------------+-----------------------------------------------------------------------+
| Rendered as | ``input`` ``number`` field                                            |
+-------------+-----------------------------------------------------------------------+
| Options     | - `rounding_mode`_                                                    |
|             | - `precision`_                                                        |
|             | - `grouping`_                                                         |
+-------------+-----------------------------------------------------------------------+
| Inherited   | - `empty_data`_                                                       |
| options     | - `required`_                                                         |
|             | - `label`_                                                            |
|             | - `label_attr`_                                                       |
|             | - `data`_                                                             |
|             | - `read_only`_                                                        |
|             | - `disabled`_                                                         |
|             | - `error_bubbling`_                                                   |
|             | - `error_mapping`_                                                    |
|             | - `invalid_message`_                                                  |
|             | - `invalid_message_parameters`_                                       |
|             | - `mapped`_                                                           |
+-------------+-----------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                             |
+-------------+-----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType` |
+-------------+-----------------------------------------------------------------------+

Field Options
-------------

.. include:: /reference/forms/types/options/precision.rst.inc

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``IntegerToLocalizedStringTransformer::ROUND_DOWN``

By default, if the user enters a non-integer number, it will be rounded
down. There are several other rounding methods, and each is a constant
on the :class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\IntegerToLocalizedStringTransformer`:

* ``IntegerToLocalizedStringTransformer::ROUND_DOWN`` Round towards zero.

* ``IntegerToLocalizedStringTransformer::ROUND_FLOOR`` Round towards negative
  infinity.

* ``IntegerToLocalizedStringTransformer::ROUND_UP`` Round away from zero.

* ``IntegerToLocalizedStringTransformer::ROUND_CEILING`` Round towards
  positive infinity.

* ``IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round down.

* ``IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round towards the
  even neighbor.

* ``IntegerToLocalizedStringTransformer::ROUND_HALF_UP`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round up.

.. include:: /reference/forms/types/options/grouping.rst.inc

Inherited Options
-----------------

These options inherit from the :doc:`form </reference/forms/types/form>` type:

.. include:: /reference/forms/types/options/empty_data.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/data.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/disabled.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc

.. include:: /reference/forms/types/options/error_mapping.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc
