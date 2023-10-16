``duplicate_preferred_choices``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

When using the ``preferred_choices`` option, those preferred choices are displayed
twice by default: at the top of the list and in the full list below. Set this
option to ``false``, to only display preferred choices at the top of the list::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('language', ChoiceType::class, [
        'choices' => [
            'English' => 'en',
            'Spanish' => 'es',
            'Bork' => 'muppets',
            'Pirate' => 'arr',
        ],
        'preferred_choices' => ['muppets', 'arr'],
        'duplicate_preferred_choices' => false,
    ]);
