IntegerType Field
=================

Renders an input "number" field. Basically, this is a text field that's
good at handling data that's in an integer form. The input ``number`` field
looks like a text box, except that - if the user's browser supports HTML5
- it will have some extra front-end functionality.

This field has different options on how to handle input values that aren't
integers. By default, all non-integer values (e.g. 6.78) will round down
(e.g. 6).

+---------------------------+-----------------------------------------------------------------------+
| Rendered as               | ``input`` ``number`` field                                            |
+---------------------------+-----------------------------------------------------------------------+
| Default invalid message   | Please enter an integer.                                              |
+---------------------------+-----------------------------------------------------------------------+
| Parent type               | :doc:`FormType </reference/forms/types/form>`                         |
+---------------------------+-----------------------------------------------------------------------+
| Class                     | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType` |
+---------------------------+-----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Field Options
-------------

.. include:: /reference/forms/types/options/grouping.rst.inc

``rounding_mode``
~~~~~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``\NumberFormatter::ROUND_DOWN``

By default, if the user enters a non-integer number, it will be rounded
down. You have several configurable options for that rounding. Each option
is a constant on the :phpclass:`NumberFormatter` class:

* ``\NumberFormatter::ROUND_DOWN`` Round towards zero. It
  rounds ``1.4`` to ``1`` and ``-1.4`` to ``-1``.

* ``\NumberFormatter::ROUND_FLOOR`` Round towards negative
  infinity. It rounds ``1.4`` to ``1`` and ``-1.4`` to ``-2``.

* ``\NumberFormatter::ROUND_UP`` Round away from zero. It
  rounds ``1.4`` to ``2`` and ``-1.4`` to ``-2``.

* ``\NumberFormatter::ROUND_CEILING`` Round towards positive
  infinity. It rounds ``1.4`` to ``2`` and ``-1.4`` to ``-1``.

* ``\NumberFormatter::ROUND_HALFDOWN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round down. It rounds
  ``2.5`` and ``1.6`` to ``2``, ``1.5`` and ``1.4`` to ``1``.

* ``\NumberFormatter::ROUND_HALFEVEN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round towards the even
  neighbor. It rounds ``2.5``, ``1.6`` and ``1.5`` to ``2`` and ``1.4`` to ``1``.

* ``\NumberFormatter::ROUND_HALFUP`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round up. It rounds
  ``2.5`` to ``3``, ``1.6`` and ``1.5`` to ``2`` and ``1.4`` to ``1``.

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
