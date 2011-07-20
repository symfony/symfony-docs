invalid_message
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid``

This is the validation error message that's used when the data entered
is determined by internal validation of a field type. This might happen,
for example, if the user enters a string into a :doc:`time</reference/forms/types/time>`
field that cannot be converted into a real time. For normal validation
messages (such as when setting a minimum length for a field), set the
validation messages with your validation rules
(:ref:`reference<book-validation-constraint-configuration>`).