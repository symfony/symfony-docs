input
~~~~~

**type**: ``string`` **default**: ``datetime``

The format of the *input* data - i.e. the format that the date is stored on
your underlying object. Valid values are:

* ``string`` (e.g. ``2011-06-05``)
* ``datetime`` (a ``DateTime`` object)
* ``array`` (e.g. ``array('year' => 2011, 'month' => 06, 'day' => 05)``)
* ``timestamp`` (e.g. ``1307232000``)

The value that comes back from the form will also be normalized back into
this format.

Note: If ``timestamp`` is used, ``DateType`` is limited to dates between
Fri, 13 Dec 1901 20:45:54 GMT and Tue, 19 Jan 2038 03:14:07 GMT on 32bit
systems. This is due to a `limitation in PHP itself <http://php.net/manual/en/function.date.php>`_:

    The valid range of a timestamp is typically from Fri, 13 Dec 1901 20:45:54 GMT
    to Tue, 19 Jan 2038 03:14:07 GMT. (These are the dates that correspond 
    to the minimum and maximum values for a 32-bit signed integer). However,
    before PHP 5.1.0 this range was limited from 01-01-1970 to 19-01-2038 on
    some systems (e.g. Windows).