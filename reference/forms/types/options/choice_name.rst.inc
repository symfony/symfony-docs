choice_name
~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_name`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Controls the internal field name of the choice. You normally don't care about this,
but in some advanced cases, you might. For example, this "name" becomes the index
of the choice views in the template.

This can be a callable or a property path. See `choice_label`_ for similar usage.
If ``null`` is used, an incrementing integer is used as the name.
