precision
~~~~~~~~~

**type**: ``integer`` **default**: Locale-specific (usually around ``3``)

This specifies how many decimals will be allowed until the field rounds
the submitted value (via ``rounding_mode``). For example, if ``precision``
is set to ``2``, a submitted value of ``20.123`` will be rounded to,
for example, ``20.12`` (depending on your ``rounding_mode``).
