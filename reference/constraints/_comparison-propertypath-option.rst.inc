``propertyPath``
~~~~~~~~~~~~~~~~

**type**: ``string``

It defines the object property whose value is used to make the comparison.

For example, if you want to compare the ``$endDate`` property of some object
with regard to the ``$startDate`` property of the same object, use
``propertyPath="startDate"`` in the comparison constraint of ``$endDate``.

.. tip::

    When using this option, its value is available in error messages as the
    ``{{ compared_value_path }}`` placeholder. Although it is not intended to
    include it in the error messages displayed to end users, it is useful when
    using APIs for doing any mapping logic on client-side.
