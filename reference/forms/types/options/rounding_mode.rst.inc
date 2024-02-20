rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``\NumberFormatter::ROUND_DOWN`` for ``IntegerType``
and ``\NumberFormatter::ROUND_HALFUP`` for ``MoneyType`` and ``NumberType``

* IntegerType
**default**: ``\NumberFormatter::ROUND_DOWN``

* MoneyType,  NumberType and PercentType
**default**: ``\NumberFormatter::ROUND_HALFUP``


If a submitted number needs to be rounded (based on the `scale`_ option), you
have several configurable options for that rounding. Each option is a constant
on the :phpclass:`NumberFormatter` class:

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
