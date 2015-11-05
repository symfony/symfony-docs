group_by
~~~~~~~~

.. versionadded:: 2.7
    The ``group_by`` option was introduced in Symfony 2.7.

**type**: ``array``, ``callable`` or ``string`` **default**: ``null``

Returns the grouping used for the choices. Can be an array/Traversable,
a callable (like for ``choice_label``) or a property path.

The return values of the callable/property path are used as group labels.
If ``null`` is returned, a choice is not grouped.

If ``null``, the structure of the ``choices`` array is used to construct
the groups::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Decided' => array(
                'Yes' => true,
                'No' => false,
            ),
            'Undecided' => array(
                'Maybe' => null,
            ),
        ),
        'choices_as_values' => true,
    ));
