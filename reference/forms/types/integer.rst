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
| Rendered as | ``input`` ``text`` field                                              |
+-------------+-----------------------------------------------------------------------+
| Options     | - `rounding_mode`_                                                    |
|             | - `grouping`_                                                         |
+-------------+-----------------------------------------------------------------------+
| Inherited   | - `required`_                                                         |
| options     | - `label`_                                                            |
|             | - `read_only`_                                                        |
|             | - `error_bubbling`_                                                   |
+-------------+-----------------------------------------------------------------------+
| Parent type | :doc:`field</reference/forms/types/field>`                            |
+-------------+-----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType` |
+-------------+-----------------------------------------------------------------------+

Field Options
-------------

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``IntegerToLocalizedStringTransformer::ROUND_DOWN``

By default, if the user enters a non-integer number, it will be rounded
down. There are several other rounding methods, and each is a constant
on the :class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\IntegerToLocalizedStringTransformer`:

*   ``IntegerToLocalizedStringTransformer::ROUND_DOWN`` Rounding mode to
    round towards zero.

*   ``IntegerToLocalizedStringTransformer::ROUND_FLOOR`` Rounding mode to
    round towards negative infinity.

*   ``IntegerToLocalizedStringTransformer::ROUND_UP`` Rounding mode to round 
    away from zero.

*   ``IntegerToLocalizedStringTransformer::ROUND_CEILING`` Rounding mode
    to round towards positive infinity.

.. include:: /reference/forms/types/options/grouping.rst.inc

Inherited options
-----------------

These options inherit from the :doc:`field</reference/forms/types/field>` type:

.. include:: /reference/forms/types/options/required.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/read_only.rst.inc

.. include:: /reference/forms/types/options/error_bubbling.rst.inc
