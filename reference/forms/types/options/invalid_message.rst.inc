invalid_message
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid``

This is the validation error message that's used if the data entered into
this field doesn't make sense (i.e. fails validation).

This might happen, for example, if the user enters a nonsense string into
a :doc:`time</reference/forms/types/time>` field that cannot be converted
into a real time or if the user enters a string (e.g. ``apple``) into a
number field.

Normal (business logic) validation (such as when setting a minimum length for
a field) should be set using validation messages with your validation rules
(:ref:`reference<book-validation-constraint-configuration>`).