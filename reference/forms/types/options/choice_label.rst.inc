``choice_label``
~~~~~~~~~~~~~~~~

**type**: ``string``, ``callable``, ``false`` or :class:`Symfony\\Component\\PropertyAccess\\PropertyPath` **default**: ``null``

By default, the array key of each item in the ``choices`` option is used as the
text that's shown to the user. The ``choice_label`` option allows you to take
more control::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('attending', ChoiceType::class, [
        'choices' => [
            'yes' => true,
            'no' => false,
            'maybe' => null,
        ],
        'choice_label' => function ($choice, string $key, mixed $value): TranslatableMessage|string {
            if (true === $choice) {
                return 'Definitely!';
            }

            return strtoupper($key);

            // or if you want to translate some key
            //return 'form.choice.'.$key;
            //return new TranslatableMessage($key, false === $choice ? [] : ['%status%' => $value], 'store');
        },
    ]);

This method is called for *each* choice, passing you the ``$choice`` and
``$key`` from the choices array (additional ``$value`` is related to `choice_value`_).
This will give you:

.. image:: /_images/reference/form/choice-example2.png
    :alt: A choice list with the options "Definitely!", "NO" and "MAYBE".

If your choice values are objects, then ``choice_label`` can also be a
:ref:`property path <reference-form-option-property-path>`. Imagine you have some
``Status`` class with a ``getDisplayName()`` method::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('attending', ChoiceType::class, [
        'choices' => [
            new Status(Status::YES),
            new Status(Status::NO),
            new Status(Status::MAYBE),
        ],
        'choice_label' => 'displayName',
    ]);

If set to ``false``, all the tag labels will be discarded for radio or checkbox
inputs. You can also return ``false`` from the callable to discard certain labels.

.. tip::

    When defining a custom type, you should use the
    :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList` class helper::

        use Symfony\Component\Form\ChoiceList\ChoiceList;

        // ...
        $builder->add('choices', ChoiceType::class, [
            'choice_label' => ChoiceList::label($this, 'displayName'),
        ]);

    See the :ref:`"choice_loader" option documentation <reference-form-choice-loader>`.
