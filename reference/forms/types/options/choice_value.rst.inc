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

.. caution::

    In Symfony 2.7, there was a small backwards-compatibility break with how the
    `value` attribute of options is generated. This is not a problem unless you
    rely on the option values in JavaScript. See `issue #14825`_ for details.

.. _`issue #14825`: https://github.com/symfony/symfony/pull/14825
