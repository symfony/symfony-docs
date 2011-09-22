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