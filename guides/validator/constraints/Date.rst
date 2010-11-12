Date
====

Validates that a value is a valid date string with format "YYYY-MM-DD".

.. code-block:: yaml

    properties:
        birthday:
            - Date: ~
        
Options
-------

* ``message``: The error message if the validation fails