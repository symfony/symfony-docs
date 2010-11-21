DateTime
========

Validates that a value is a valid datetime string with format "YYYY-MM-DD HH:MM:SS".

.. code-block:: yaml

    properties:
        createdAt:
            - DateTime: ~

Options
-------

* ``message``: The error message if the validation fails
