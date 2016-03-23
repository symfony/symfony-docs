choice_name
~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_name`` option was introduced in Symfony 2.7.

**type**: ``callable``, ``string`` or :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` **default**: ``null``

Controls the internal field name of the choice. You normally don't care about
this, but in some advanced cases, you might. For example, this "name" becomes
the index of the choice views in the template.

This can be a callable or a property path. Both needs to return a non empty
string, if ``null`` is used, an incrementing integer is used as the name.

See `choice_label`_ for similar usage.
