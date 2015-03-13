payload
~~~~~~~

**type**: ``mixed`` **default**: ``null``

.. versionadded:: 2.6
    The ``payload`` option was introduced in Symfony 2.6.

This option can be used to attach arbitrary domain-specific data to a constraint.
The configured payload is not used by the Validator component, but its processing
is completely up to.

For example, you may want to used
:doc:`several error levels </cookbook/validation/severity>` to present failed
constraint differently in the front-end depending on the severity of the
error.
