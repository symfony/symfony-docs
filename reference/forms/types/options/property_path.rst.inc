``property_path``
~~~~~~~~~~~~~~~~~

**type**: ``PropertyPathInterface|string|null`` **default**: ``null``

By default (when the value of this option is ``null``) form fields read from and
write to the properties with the same names in the form's domain object. The
``property_path`` option lets you define which property a field reads from and
writes to. The value of this option can be any
:doc:`valid PropertyAccess syntax </components/property_access>`.
