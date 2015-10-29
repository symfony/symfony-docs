choice_value
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_value`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Returns the string value for each choice. This value is displayed in the
``value`` attributes and submitted in the POST/PUT requests. Can be a
callable (like for ``choice_label``) or a property path.

If ``null``, an incrementing integer is used as value.
