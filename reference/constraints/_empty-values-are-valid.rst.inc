.. note::

    As with most of the other constraints, ``null`` values and empty strings
    are considered valid values. Otherwise, in addition to validating this value,
    you would also be requiring it, making it impossible to be optional. That's
    why most of the times this constraint is combined with
    :doc:`NotBlank </reference/constraints/NotBlank>`.
