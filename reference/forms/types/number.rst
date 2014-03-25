.. index::
   single: Forms; Fields; number

number Field Type
=================

Renders an input text field and specializes in handling number input. This
type offers different options for the precision, rounding, and grouping that
you want to use for your number.

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                             |
+-------------+----------------------------------------------------------------------+
| Options     | - `rounding_mode`_                                                   |
|             | - `precision`_                                                       |
|             | - `grouping`_                                                        |
+-------------+----------------------------------------------------------------------+
| Inherited   | - `empty_data`_                                                      |
| options     | - `required`_                                                        |
|             | - `label`_                                                           |
|             | - `label_attr`_                                                      |
|             | - `data`_                                                            |
|             | - `read_only`_                                                       |
|             | - `disabled`_                                                        |
|             | - `error_bubbling`_                                                  |
|             | - `error_mapping`_                                                   |
|             | - `invalid_message`_                                                 |
|             | - `invalid_message_parameters`_                                      |
|             | - `mapped`_                                                          |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`form </reference/forms/types/form>`                            |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType` |
+-------------+----------------------------------------------------------------------+

Field Options
-------------

.. include:: /reference/forms/types/options/precision.rst.inc

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``NumberToLocalizedStringTransformer::ROUND_HALFUP``

If a submitted number needs to be rounded (based on the ``precision``
option), you have several configurable options for that rounding. Each
option is a constant on the :class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer`:
    
* ``NumberToLocalizedStringTransformer::ROUND_DOWN`` Round towards zero.

* ``NumberToLocalizedStringTransformer::ROUND_FLOOR`` Round towards negative
  infinity.

* ``NumberToLocalizedStringTransformer::ROUND_UP`` Round away from zero.

* ``NumberToLocalizedStringTransformer::ROUND_CEILING`` Round towards
  positive infinity.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_DOWN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round down.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_EVEN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round towards the
  even neighbor.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_UP`` Round towards the
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
