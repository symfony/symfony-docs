Email
=====

Validates that a value is a valid email address.

    [yml]
    properties:
      email:
        - Email: ~

Options
-------

  * `checkMX`: Whether MX records should be checked for the domain. Default: `false`
  * `message`: The error message if the validation fails