``choice_attr``
~~~~~~~~~~~~~~~

**type**: ``array``, ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``[]``

Use this to add additional HTML attributes to each choice. This can be
an associative array where the keys match the choice keys and the values
are the attributes for each choice, a callable or a property path
(just like `choice_label`_).

If an array, the keys of the ``choices`` array must be used as keys::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('attending', ChoiceType::class, [
        'choices' => [
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ],
        'choice_attr' => function($choice, $key, $value) {
            // adds a class like attending_yes, attending_no, etc
            return ['class' => 'attending_'.strtolower($key)];
        },
    ]);
