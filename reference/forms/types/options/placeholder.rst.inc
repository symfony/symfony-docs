``placeholder``
~~~~~~~~~~~~~~~

**type**: ``string`` or ``TranslatableMessage`` or ``boolean``

This option determines whether or not a special "empty" option (e.g. "Choose
an option") will appear at the top of a select widget. This option only
applies if the ``multiple`` option is set to false.

* Add an empty value with "Choose an option" as the text::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('states', ChoiceType::class, [
        'placeholder' => 'Choose an option',

        // or if you want to translate the text
        'placeholder' => new TranslatableMessage('form.placeholder.select_option', [], 'form'),
    ]);

* Guarantee that no "empty" value option is displayed::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('states', ChoiceType::class, [
        'placeholder' => false,
    ]);

If you leave the ``placeholder`` option unset, then a blank (with no text)
option will automatically be added if and only if the ``required`` option
is false::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    // a blank (with no text) option will be added
    $builder->add('states', ChoiceType::class, [
        'required' => false,
    ]);
