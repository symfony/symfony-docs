.. caution::

    The ``pattern`` option has been deprecated and will be removed in 3.0.
    Instead, use the ``attr`` option by setting it to an array with a ``pattern`` key.

pattern
~~~~~~~

**type**: ``string`` **default**: ``null``

This adds an HTML5 ``pattern`` attribute to restrict the field input by a
given regular expression.

.. caution:

    The ``pattern`` attribute provides client-side validation for convenience
    purposes only and must not be used as a replacement for reliable
    server-side validation.

.. note:

    When using validation constraints, this option is set automatically
    for some constraints to match the server-side validation.
