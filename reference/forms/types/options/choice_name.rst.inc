``choice_name``
~~~~~~~~~~~~~~~

**type**: ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

Controls the internal field name of the choice. You normally don't care about this,
but in some advanced cases, you might. For example, this "name" becomes the index
of the choice views in the template and is used as part of the field name
attribute.

This can be a callable or a property path. See `choice_label`_ for similar usage.
By default, the choice key or an incrementing integer may be used (starting at ``0``).

.. caution::

    The configured value must be a valid form name. Make sure to only return
    valid names when using a callable. Valid form names must be composed of
    letters, digits, underscores, dashes and colons and must not start with
    a dash or a colon.
