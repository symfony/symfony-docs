.. index::
   single: Forms; Fields; NumberType

NumberType Field
================

Renders an input text field and specializes in handling number input. This
type offers different options for the scale, rounding and grouping
that you want to use for your number.

+-------------+----------------------------------------------------------------------+
| Rendered as | ``input`` ``text`` field                                             |
+-------------+----------------------------------------------------------------------+
| Options     | - `grouping`_                                                        |
|             | - `scale`_                                                           |
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
|             | - `read_only`_ (deprecated as of 2.8)                                |
|             | - `required`_                                                        |
+-------------+----------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                        |
+-------------+----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType` |
+-------------+----------------------------------------------------------------------+

Field Options
-------------

.. include:: /reference/forms/types/options/grouping.rst.inc

.. include:: /reference/forms/types/options/scale.rst.inc

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``NumberToLocalizedStringTransformer::ROUND_HALFUP``

If a submitted number needs to be rounded (based on the `scale`_
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
