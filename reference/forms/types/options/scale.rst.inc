scale
~~~~~

.. versionadded:: 2.7
    The ``scale`` option was introduced in Symfony 2.7. Prior to Symfony 2.7,
    it was known as ``precision``.

**type**: ``integer`` **default**: Locale-specific (usually around ``3``)

This specifies how many decimals will be allowed until the field rounds
the submitted value (via ``rounding_mode``). For example, if ``scale`` is set
to ``2``, a submitted value of ``20.123`` will be rounded to, for example,
``20.12`` (depending on your `rounding_mode`_).
