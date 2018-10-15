rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``NumberToLocalizedStringTransformer::ROUND_HALF_UP``

If a submitted number needs to be rounded (based on the `scale`_
option), you have several configurable options for that rounding. Each
option is a constant on the :class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer`:

* ``NumberToLocalizedStringTransformer::ROUND_DOWN`` Round towards zero. It
  rounds ``1.4`` to ``1`` and ``-1.4`` to ``-1``.

* ``NumberToLocalizedStringTransformer::ROUND_FLOOR`` Round towards negative
  infinity. It rounds ``1.4`` to ``1`` and ``-1.4`` to ``-2``.

* ``NumberToLocalizedStringTransformer::ROUND_UP`` Round away from zero. It
  rounds ``1.4`` to ``2`` and ``-1.4`` to ``-2``.

* ``NumberToLocalizedStringTransformer::ROUND_CEILING`` Round towards positive
  infinity. It rounds ``1.4`` to ``2`` and ``-1.4`` to ``-1``.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_DOWN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round down. It rounds
  ``2.5`` and ``1.6`` to ``2``, ``1.5`` and ``1.4`` to ``1``.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_EVEN`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round towards the even
  neighbor. It rounds ``2.5``, ``1.6`` and ``1.5`` to ``2`` and ``1.4`` to ``1``.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_UP`` Round towards the
  "nearest neighbor". If both neighbors are equidistant, round up. It rounds
  ``2.5`` to ``3``, ``1.6`` and ``1.5`` to ``2`` and ``1.4`` to ``1``.
