MaxLength
=========

Validates that the string length of a value is not greater than the given limit.

    [yml]
    properties:
      firstName:
        - MaxLength: 20

Options
-------

  * `limit` (**default**, required): The limit
  * `message`: The error message if validation fails