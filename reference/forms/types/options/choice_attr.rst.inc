choice_attr
~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_attr`` option was introduced in Symfony 2.7.

**type**: ``array``, ``callable`` or ``string`` **default**: ``array()``

Returns the additional HTML attributes for choices. Can be an array, a callable
(like for ``choice_label``) or a property path.

If an array, the keys of the ``choices`` array must be used as keys::

    $builder->add('attending', 'choice', array(
        'choices' => array(
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_attr' => array(
            'Maybe' => array('class' => 'greyed-out'),
        ),
    ));
