.. caution::

    When using PHP 7.x, if the value is a string (e.g. ``1234asd``), the validator
    will not trigger an error. In this case, you must also use the
    :doc:`Type constraint </reference/constraints/Type>` with
    ``numeric``, ``integer`, etc. to reject strings.
