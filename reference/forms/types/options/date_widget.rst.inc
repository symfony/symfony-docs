widget
~~~~~~

**type**: ``string`` **default**: ``choice``

The basic way in which this field should be rendered. Can be one of the following:

* ``choice``: renders three select inputs.  The order of the selects is defined
  in the `pattern`_ option.

* ``text``: renders a three field input of type text (month, day, year).

* ``single_text``: renders a single input of type text. User's input is validated
  based on the `format`_ option.

Note: If ``single_text`` is used, ``DateType`` is limited to dates between Fri, 13 Dec 1901 20:45:54 GMT and Tue, 19 Jan 2038 03:14:07 GMT on 32bit systems. This is due to a `limitation in PHP itself <http://php.net/manual/en/function.date.php>`_:

    The valid range of a timestamp is typically from Fri, 13 Dec 1901 20:45:54 GMT to Tue, 19 Jan 2038 03:14:07 GMT. (These are the dates that correspond to the minimum and maximum values for a 32-bit signed integer). However, before PHP 5.1.0 this range was limited from 01-01-1970 to 19-01-2038 on some systems (e.g. Windows).