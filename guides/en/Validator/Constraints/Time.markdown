Time
====

Validates that a value is a valid time string with format "HH:MM:SS".

    [yml]
    properties:
      createdAt:
        - DateTime: ~
    
Options
-------

  * `message`: The error message if the validation fails