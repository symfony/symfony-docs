Regex
=====

Validates that a value matches a regular expression.

.. code-block:: yaml

    properties:
        title:
            - Regex: /\w+/

Options
-------

* ``pattern`` (**default**, required): The regular expression pattern
* ``match``: Whether the pattern must be matched or must not be matched.
  Default: ``true``
* ``message``: The error message if validation fails
