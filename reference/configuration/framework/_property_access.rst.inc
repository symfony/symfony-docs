property_access
~~~~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

magic_call
..........

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __call() method <components-property-access-magic-call>` when
its ``getValue()`` method is called.

magic_get
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __get() method <components-property-access-magic-get>` when
its ``getValue()`` method is called.

magic_set
.........

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service uses PHP's
:ref:`magic __set() method <components-property-access-writing-to-objects>` when
its ``setValue()`` method is called.

throw_exception_on_invalid_index
................................

**type**: ``boolean`` **default**: ``false``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid index of an array.

throw_exception_on_invalid_property_path
........................................

**type**: ``boolean`` **default**: ``true``

When enabled, the ``property_accessor`` service throws an exception when you
try to access an invalid property path of an object.
