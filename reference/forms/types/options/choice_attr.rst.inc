choice_attr
~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_attr`` option was introduced in Symfony 2.7.

**type**: ``array``, ``callable``, ``string`` or :class:``Symfony\\Component\\PropertyAccess\\PropertyPath`` **default**: ``array()``

Use this to add additional HTML attributes to each choice. This can be used as
a callable or a property path (just like `choice_label`_).

Also, if used as an array, the keys of the ``choices`` array must be used as keys::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_attr' => array(
            // will be used for the second choice
            'No' => array('class' => 'singular_choice_option');
        ),
    ));

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_attr' => function() {
            // will be used for all choices
            return array('class' => 'choice_option');
        },
    ));
