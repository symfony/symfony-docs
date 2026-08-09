set_locale_from_accept_language
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If this option is set to ``true``, the ``Request`` locale will automatically be
set to the value of the ``Accept-Language`` HTTP header.

When the ``_locale`` request attribute is passed, the ``Accept-Language`` header
is ignored.
