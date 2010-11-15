NotBlank
========

Validates that a value is not empty (as determined by the `empty
<http://php.net/empty>`_ construct).

.. code-block:: yaml

    properties:
        firstName:
            - NotBlank: ~

Options
-------

* ``message``: The error message if validation fails
