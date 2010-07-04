MinLength
=========

Validates that the string length of a value is not smaller than the given limit.

    [yml]
    properties:
      firstName:
        - MinLength: 3

Options
-------

  * `limit` (**default**, required): The limit
  * `message`: The error message if validation fails