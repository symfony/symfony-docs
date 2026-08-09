.. _reference-framework-form:

form
~~~~

.. _reference-form-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether to enable the form services or not in the service container. If
you don't use forms, setting this to ``false`` may increase your application's
performance because fewer services will be loaded into the container.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. note::

    This will automatically enable the `validation`_.

.. seealso::

    For more details, see :doc:`/forms`.

.. _reference-framework-form-csrf-protection:

csrf_protection
...............

enabled
'''''''

**type**: ``boolean`` **default**: ``true``

Whether to enable CSRF protection for all forms. You can also
:ref:`disable CSRF protection for individual forms <form-csrf-customization>`
or disable it for your entire application (see
:ref:`framework.csrf_protection <reference-framework-csrf-protection>`).

field_name
''''''''''

**type**: ``string`` **default**: ``_token``

This is the field name that you should give to the CSRF token field of your forms.

field_attr
''''''''''

**type**: ``array`` **default**: ``['data-controller' => 'csrf-protection']``

HTML attributes to add to the CSRF token field of your forms.

token_id
''''''''

**type**: ``string`` **default**: ``null``

The CSRF token ID used to validate the CSRF tokens of your forms. This setting
applies only to form types that use :ref:`service autoconfiguration <services-autoconfigure>`,
which typically means your own form types, not those registered by third-party bundles.
