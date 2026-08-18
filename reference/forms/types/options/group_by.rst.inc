``group_by``
~~~~~~~~~~~~

**type**: ``string``, ``callable`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

You can group the ``<option>`` elements of a ``<select>`` into ``<optgroup>``
by passing a multi-dimensional array to ``choices``. See the
:ref:`Grouping Options <form-choices-simple-grouping>` section about that.

The ``group_by`` option is an alternative way to group choices, which gives you
a bit more flexibility.

Take the following example::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('publishAt', ChoiceType::class, [
        'choices' => [
            'now' => new \DateTime('now'),
            'tomorrow' => new \DateTime('+1 day'),
            '1 week' => new \DateTime('+1 week'),
            '1 month' => new \DateTime('+1 month'),
        ],
        'group_by' => function($choice, $key, $value) {
            if ($choice <= new \DateTime('+3 days')) {
                return 'Soon';
            }

            return 'Later';
        },
    ]);

This groups the dates that are within 3 days into "Soon" and everything else into
a "Later" ``<optgroup>``:

.. image:: /_images/reference/form/choice-example5.png
    :alt: A choice list with "now" and "tomorrow" grouped under "Soon", and "1 week" and "1 month" grouped under "Later".

If you return ``null``, the option won't be grouped. You can also pass a string
"property path" that will be called to get the group. See the `choice_label`_ for
details about using a property path.

The group labels are translated using the ``choice_translation_domain`` option,
so you can return a translation key. The callback can also return any
:class:`Symfony\\Contracts\\Translation\\TranslatableInterface` instance, like
it's possible with the `choice_label`_ option::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\Translation\TranslatableMessage;
    // ...

    $builder->add('country', ChoiceType::class, [
        'choices' => $countries,
        'group_by' => fn (Country $country) => new TranslatableMessage(
            'continent.'.$country->getContinent(),
            [],
            'countries',
        ),
    ]);

.. versionadded:: 8.2

    Support for returning ``TranslatableInterface`` instances from ``group_by``
    was introduced in Symfony 8.2.

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'group_by' => ChoiceList::groupBy($this, 'category'),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
