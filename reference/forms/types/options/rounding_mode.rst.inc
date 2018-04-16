rounding_mode
~~~~~~~~~~~~~

**type**: ``integer`` **default**: ``NumberToLocalizedStringTransformer::ROUND_HALF_UP``

If a submitted number needs to be rounded (based on the `scale`_
option), you have several configurable options for that rounding. Each
option is a constant on the :class:`Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer`:

* ``NumberToLocalizedStringTransformer::ROUND_DOWN`` Round towards zero.

* ``NumberToLocalizedStringTransformer::ROUND_FLOOR`` Round towards negative infinity.

* ``NumberToLocalizedStringTransformer::ROUND_UP`` Round away from zero.

* ``NumberToLocalizedStringTransformer::ROUND_CEILING`` Round towards positive infinity.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_DOWN`` Round towards the "nearest neighbor". If both neighbors are equidistant, round down.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_EVEN`` Round towards the "nearest neighbor". If both neighbors are equidistant, round towards the even neighbor.

* ``NumberToLocalizedStringTransformer::ROUND_HALF_UP`` Round towards the "nearest neighbor". If both neighbors are equidistant, round up.
