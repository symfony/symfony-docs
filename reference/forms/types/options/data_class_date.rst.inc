data_class
~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The internal normalized representation of this type is an array, not a ``\DateTime``
object. Therefore, the ``data_class`` option is initialized to ``null`` to avoid
the ``FormType`` object from initializing it to ``\DateTime``.
