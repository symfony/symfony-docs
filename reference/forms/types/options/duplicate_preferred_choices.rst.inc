``duplicate_preferred_choices``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

When using the ``preferred_choices`` option, the selected choices are displayed
twice by default: at the top of your list and in the complete list. You can
change this behavior: when setting this option to ``false``, preferred choices
will only be displayed at the top of the list.

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

.. versionadded:: 6.4

    The ``duplicate_preferred_choices`` option was introduced in Symfony 6.4.
