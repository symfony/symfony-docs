format
~~~~~~

**type**: ``integer`` or ``string`` **default**: ``IntlDateFormatter::MEDIUM``

Option passed to the ``IntlDateFormatter`` class, used to transform user input
into the proper format. This is critical when the `widget`_ option is
set to ``single_text``, and will define how to transform the input. By default,
the format is determined based on the current user locale; you can override
it by passing the format as a string.
