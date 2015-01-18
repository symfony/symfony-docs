max_length
~~~~~~~~~~

.. caution::

    The ``max_length`` option was deprecated in Symfony 2.5 and will be removed
    in Symfony 3.0. Use the ``attr`` option instead by setting it to an array
    with a ``maxlength`` key.

**type**: ``integer`` **default**: ``null``

If this option is not null, an attribute ``maxlength`` is added, which
is used by some browsers to limit the amount of text in a field.

This is just a browser validation, so data must still be validated
server-side.

