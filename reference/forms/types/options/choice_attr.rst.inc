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

    $builder->add('fruits', ChoiceType::class, [
        'choices' => [
            'Apple' => 1,
            'Banana' => 2,
            'Durian' => 3,
        ],
        'choice_attr' => [
            'Apple' => ['data-color' => 'Red'],
            'Banana' => ['data-color' => 'Yellow'],
            'Durian' => ['data-color' => 'Green'],
        ],
    ]);

    // or use a callable
    $builder->add('attending', ChoiceType::class, [
        'choices' => [
            'Yes' => true,
            'No' => false,
            'Maybe' => null,
        ],
        'choice_attr' => function ($choice, string $key, mixed $value) {
            // adds a class like attending_yes, attending_no, etc
            return ['class' => 'attending_'.strtolower($key)];
        },
    ]);

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use App\Entity\Category;
        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'choice_attr' => ChoiceList::attr($this, function (?Category $category): array {
                return $category ? ['data-uuid' => $category->getUuid()] : [];
            }),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
