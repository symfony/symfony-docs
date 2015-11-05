choice_name
~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_name`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Returns the form name for each choice. That name is used as name of the
checkbox/radio form for this choice. It is also used as index of the choice
views in the template. Can be a callable (like for ``choice_label``) or
a property path.

The generated names must be valid form names, i.e. contain alpha-numeric
symbols, underscores, hyphens and colons only. They must start with an
alpha-numeric symbol or an underscore.

If ``null``, an incrementing integer is used as name.
