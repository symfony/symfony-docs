cascade_validation
~~~~~~~~~~~~~~~~~~

**type**: Boolean **default**: false

Set this option to ``true`` to force validation on embedded form types.
For example, if you have a ``ProductType`` with an embedded ``CategoryType``,
setting ``cascade_validation`` to ``true`` on ``ProductType`` will cause
the data from ``CategoryType`` to also be validated.

Instead of using this option, you can also use the ``Valid`` constraint in
your model to force validation on a child object stored on a property.




