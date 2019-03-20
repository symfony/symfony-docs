.. index::
   single: Forms; Fields; IntegerType

IntegerType Field
=================

Renders an input "number" field. Basically, this is a text field that's
good at handling data that's in an integer form. The input ``number`` field
looks like a text box, except that - if the user's browser supports HTML5
- it will have some extra front-end functionality.

This field has different options on how to handle input values that aren't
integers. By default, all non-integer values (e.g. 6.78) will round down
(e.g. 6).

+-------------+-----------------------------------------------------------------------+
| Rendered as | ``input`` ``number`` field                                            |
+-------------+-----------------------------------------------------------------------+
| Options     | - `grouping`_                                                         |
|             | - `rounding_mode`_                                                    |
+-------------+-----------------------------------------------------------------------+
| Overridden  | - `compound`_                                                         |
| options     | - `scale`_                                                            |
+-------------+-----------------------------------------------------------------------+
| Inherited   | - `data`_                                                             |
| options     | - `disabled`_                                                         |
|             | - `empty_data`_                                                       |
|             | - `error_bubbling`_                                                   |
|             | - `error_mapping`_                                                    |
|             | - `help`_                                                             |
|             | - `help_attr`_                                                        |
|             | - `help_html`_                                                        |
|             | - `invalid_message`_                                                  |
|             | - `invalid_message_parameters`_                                       |
|             | - `label`_                                                            |
|             | - `label_attr`_                                                       |
|             | - `label_format`_                                                     |
|             | - `mapped`_                                                           |
|             | - `required`_                                                         |
+-------------+-----------------------------------------------------------------------+
| Parent type | :doc:`FormType </reference/forms/types/form>`                         |
+-------------+-----------------------------------------------------------------------+
| Class       | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType` |
+-------------+-----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/grouping.rst.inc

rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``IntegerToLocalizedStringTransformer::ROUND_DOWN``

By default, if the user enters a non-integer number, it will be rounded
down. There are several other rounding methods and each is a constant
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

Overridden Options
------------------

.. include:: /reference/forms/types/options/compound_type.rst.inc

scale
~~~~~

**type**: ``integer`` **default**: ``0``

.. deprecated:: 4.2

    The ``scale`` option is deprecated since Symfony 4.2 and will be removed
    in 5.0.

This specifies how many decimals will be allowed until the field rounds the
submitted value (via ``rounding_mode``). This option inherits from
:doc:`number </reference/forms/types/number>` type and is overriden to ``0`` for
``IntegerType``.

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

.. include:: /reference/forms/types/options/help.rst.inc

.. include:: /reference/forms/types/options/help_attr.rst.inc

.. include:: /reference/forms/types/options/help_html.rst.inc

.. include:: /reference/forms/types/options/invalid_message.rst.inc

.. include:: /reference/forms/types/options/invalid_message_parameters.rst.inc

.. include:: /reference/forms/types/options/label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

.. include:: /reference/forms/types/options/mapped.rst.inc

.. include:: /reference/forms/types/options/required.rst.inc
