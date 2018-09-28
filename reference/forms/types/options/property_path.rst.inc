property_path
~~~~~~~~~~~~~

**type**: ``any`` **default**: ``the field's name``

Fields display the values of the form's domain object properties. When the form
is submitted, the submitted values are written back into the object.

By default the object property matches the field name. The ``property_path``
option lets you define the property that a field reads from and writes to.
Use any :doc:`valid PropertyAccess syntax </components/property_access>` to
define that property.

If you wish the field to be ignored when reading or writing to the object
you can set the ``property_path`` option to ``false``, but using
``property_path`` for this purpose is deprecated, you should use the
``mapped`` option.
