``extra_fields_message``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This form should not contain extra fields.``

This is the validation error message that's used if the submitted form data
contains one or more fields that are not part of the form definition. The
placeholder ``{{ extra_fields }}`` can be used to display a comma separated
list of the submitted extra field names.

This message can be pluralized, see
:ref:`formatting pluralized messages <component-translation-pluralization>` for
details.
