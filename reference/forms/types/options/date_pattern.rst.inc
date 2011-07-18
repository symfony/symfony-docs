pattern
~~~~~~~

**type**: ``string``

This option is only relevant when the `widget`_ is set to ``choice``.
The default pattern is based off the `format`_ option, and tries to
match the characters ``M``, ``d``, and ``y`` in the format pattern. If
no match is found, the default is the string ``{{ year }}-{{ month }}-{{ day }}``.
Tokens for this option include:

  * ``{{ year }}``: Replaced with the ``year`` widget
  * ``{{ month }}``: Replaced with the ``month`` widget
  * ``{{ day }}``: Replaced with the ``day`` widget