MaxLength
=========

Validates that the string length of a value is not greater than the given limit.

.. code-block:: yaml

    properties:
        firstName:
            - MaxLength: 20

Options
-------

* ``limit`` (**default**, required): The limit
* ``message``: The error message if validation fails