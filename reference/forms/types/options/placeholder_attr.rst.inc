``placeholder_attr``
~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Use this to add additional HTML attributes to the placeholder choice::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('fruits', ChoiceType::class, [
        // ...
        'placeholder' => '...',
        'placeholder_attr' => [
            ['title' => 'Choose an option'],
        ],
    ]);

.. versionadded:: 6.3

    The ``placeholder_attr`` option was introduced in Symfony 6.3.
