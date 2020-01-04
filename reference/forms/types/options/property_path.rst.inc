``property_path``
~~~~~~~~~~~~~~~~~

**type**: ``any`` **default**: ``the field's name``

By default form fields read from and write to the properties with the same names
in the form's domain object. The ``property_path`` option lets you define which
property a field reads from and writes to. The value of this option can be any
:doc:`valid PropertyAccess syntax </components/property_access>`.

If you wish the field to be ignored when reading or writing to the object
you can set the ``property_path`` option to ``false``, but using
``property_path`` for this purpose is deprecated, you should use the
``mapped`` option.
