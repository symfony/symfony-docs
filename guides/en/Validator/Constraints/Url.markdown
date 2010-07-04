Url
===

Validates that a value is a valid URL string.

    [yml]
    properties:
      website:
        - Url: ~
    
Options
-------

  * `protocols`: A list of allowed protocols. Default: "http", "https", "ftp"
    and "ftps".
  * `message`: The error message if validation fails