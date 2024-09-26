``preferred_choices``
~~~~~~~~~~~~~~~~~~~~~

**type**: ``array``, ``callable``, ``string`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``[]``

This option allows you to display certain choices at the top of your list with a
visual separator between them and the complete list of options. If you have a
form of languages, you can list the most popular on top, like Bork and Pirate::

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
    ]);

This options can also be a callback function to give you more flexibility. This might
be especially useful if your values are objects::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('publishAt', ChoiceType::class, [
        'choices' => [
            'now' => new \DateTime('now'),
            'tomorrow' => new \DateTime('+1 day'),
            '1 week' => new \DateTime('+1 week'),
            '1 month' => new \DateTime('+1 month'),
        ],
        'preferred_choices' => function ($choice, $key, $value): bool {
            // prefer options within 3 days
            return $choice <= new \DateTime('+3 days');
        },
    ]);

This will "prefer" the "now" and "tomorrow" choices only:

.. image:: /_images/reference/form/choice-example3.png
    :alt: A choice list with "now" and "tomorrow" on top, separated by a line from "1 week" and "1 month".

Finally, if your values are objects, you can also specify a property path string
on the object that will return true or false.

The preferred choices are only meaningful when rendering a ``select`` element
(i.e. ``expanded`` false). The preferred choices and normal choices are separated
visually by a set of dotted lines (i.e. ``-------------------``). This can be customized
when rendering the field:

.. configuration-block::

    .. code-block:: twig

        {{ form_widget(form.publishAt, { 'separator': '=====' }) }}

    .. code-block:: html+php

        <?= $view['form']->widget($form['publishAt'], [
            'separator' => '=====',
        ]) ?>

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'preferred_choices' => ChoiceList::preferred($this, 'taggedAsFavorite'),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
