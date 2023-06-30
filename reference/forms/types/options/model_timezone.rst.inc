``model_timezone``
~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: system default timezone

Timezone that the input data is stored in. This must be one of the
`PHP supported timezones`_.

.. deprecated:: 6.4

    Starting from Symfony 6.4, it's deprecated to pass ``DateTime`` or ``DateTimeImmutable``
    values to this form field with a different timezone than the one configured with
    the ``model_timezone`` option.

.. _`PHP supported timezones`: https://www.php.net/manual/en/timezones.php
