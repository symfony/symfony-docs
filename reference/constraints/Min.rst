Min
===

Validates that a value is not smaller than the given limit.

.. code-block:: yaml

    properties:
        age:
            - Min: 1

Options
-------

* ``limit`` (**default**, required): The limit
* ``message``: The error message if validation fails
