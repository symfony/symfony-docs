property_path
~~~~~~~~~~~~~

**type**: ``PropertyPathInterface|string|null`` **default**: ``null``

Fields display a property value of the form's domain object by default.
When the form is submitted, the submitted value is written back into the
object.

If you want to override the property that a field reads from and writes
to, you can set the ``property_path`` option. Its default value (``null``)
will use the field's name as the property.
