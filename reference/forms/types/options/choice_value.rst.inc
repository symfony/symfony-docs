choice_value
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_value`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Returns the string "value" for each choice. This is used in the ``value`` attribute
in HTML and submitted in the POST/PUT requests. You don't normally need to worry
about this, but it might be handy when processing an API request (since you can
configure the value that will be sent in the API request).

This can be a callable or a property path. See `choice_label`_ for similar usage.
If ``null`` is used, an incrementing integer is used as the name.
