Email
=====

Validates that a value is a valid email address.

.. code-block:: yaml

    properties:
        email:
            - Email: ~

Options
-------

* ``checkMX``: Whether MX records should be checked for the domain. Default: ``false``
* ``message``: The error message if the validation fails
