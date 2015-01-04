model_timezone
~~~~~~~~~~~~~~

.. caution::

    The ``model_timezone`` option has been deprecated and will be removed
    in 3.0. If you pass `DateTime` instances as data to the form type, its
    timezone is ignored.

**type**: ``string`` **default**: system default timezone

Timezone that the input data is stored in. This must be one of the
`PHP supported timezones`_.

.. _`PHP supported timezones`: http://php.net/manual/en/timezones.php
