.. caution::

    The ``max_length`` option has been deprecated and will be removed in 3.0.
    Instead, use the ``attr`` option by setting it to an array with a ``maxlength`` key.

max_length
~~~~~~~~~~

**type**: ``integer`` **default**: ``null``

If this option is not null, an attribute ``maxlength`` is added, which
is used by some browsers to limit the amount of text in a field.

This is just a browser validation, so data must still be validated
server-side.

