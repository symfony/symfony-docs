uid
~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Read the :doc:`UID component documentation </components/uid>` for complete
details about each option.

default_uuid_version
....................

**type**: ``integer`` **default**: ``7``

The version of UUID to generate by default (used by the ``create()`` method of
the UUID factory and for time-based UUIDs).

name_based_uuid_version
.......................

**type**: ``integer`` **default**: ``5``

The version of UUID to generate for name-based UUIDs.

name_based_uuid_namespace
.........................

**type**: ``string`` **default**: ``null``

A UUID that serves as the namespace for generating name-based UUIDs
(e.g. ``'6ba7b810-9dad-11d1-80b4-00c04fd430c8'``).

time_based_uuid_version
.......................

**type**: ``integer`` **default**: ``7``

The version of UUID to generate for time-based UUIDs.

time_based_uuid_node
....................

**type**: ``string`` | ``integer`` **default**: ``null``

The node to use for generating time-based UUIDs (e.g. ``'121212121212'``).
