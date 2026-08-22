``choice_help``
~~~~~~~~~~~~~~~

**type**: ``array``, ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

.. versionadded:: 8.2

    The ``choice_help`` option was introduced in Symfony 8.2.

Use this to display a help message next to each choice. Its value is resolved
exactly like `choice_label`_: an associative array whose keys match the choice
keys, a callable or a property path. The resolved help is translated with the
``choice_translation_domain`` option, like the label.

If an array, the keys of the ``choices`` array must be used as keys::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('fruits', ChoiceType::class, [
        'choices' => [
            'Apple' => 1,
            'Banana' => 2,
        ],
        'choice_help' => [
            'Apple' => 'Keeps the doctor away',
            'Banana' => 'Rich in potassium',
        ],
        'expanded' => true,
    ]);

    // or use a callable
    $builder->add('category', ChoiceType::class, [
        'choices' => $categories,
        'choice_label' => 'name',
        'choice_help' => function (?Category $category): string {
            return $category?->getDescription() ?? '';
        },
        'expanded' => true,
    ]);

Only expanded choices render this help: every radio button or checkbox gets a
help element, pointed at by the ``aria-describedby`` attribute of the input. A
collapsed ``<select>`` renders nothing, because an ``<option>`` element cannot
contain other elements. The value is still exposed as ``ChoiceView::$help``, so
a custom form theme can render it.

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use App\Entity\Category;
        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'choice_help' => ChoiceList::help($this, function (?Category $category): string {
                return $category?->getDescription() ?? '';
            }),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
