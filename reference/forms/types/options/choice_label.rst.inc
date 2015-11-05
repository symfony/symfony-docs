choice_label
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_label`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Returns the label for each choice. Can be a callable (which receives the
choice as first and the key of the ``choices`` array as second argument)
or a property path.

If ``null``, the keys of the ``choices`` array are used as labels.

* Using a callable to set the label::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'yes' => true,
            'no' => false,
            'maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_label' => function ($choice, $key) {
            return 'form.choice.'.$key;
        },
    ));

* Using a property path to set the label::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            Status::getInstance(Status::YES),
            Status::getInstance(Status::NO),
            Status::getInstance(Status::MAYBE),
        ),
        'choices_as_values' => true,
        'choice_label' => 'displayName',
    ));
