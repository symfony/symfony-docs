Max
===

Validates that a value is not greater than the given limit.

.. code-block:: yaml

    properties:
        age:
            - Max: 99

Options
-------

* ``limit`` (**default**, required): The limit
* ``message``: The error message if validation fails
