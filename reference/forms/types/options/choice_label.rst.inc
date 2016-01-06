choice_label
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_label`` option was introduced in Symfony 2.7.

**type**: ``callable`` or ``string`` **default**: ``null``

Normally, the array key of each item in the ``choices`` option is used as the
text that's shown to the user. The ``choice_label`` option allows you to take
more control::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('attending', ChoiceType::class, array(
        'choices' => array(
            'yes' => true,
            'no' => false,
            'maybe' => null,
        ),
        'choices_as_values' => true,
        'choice_label' => function ($value, $key, $index) {
            if ($value == true) {
                return 'Definitely!';
            }
            return strtoupper($key);

            // or if you want to translate some key
            //return 'form.choice.'.$key;
        },
    ));

This method is called for *each* choice, passing you the choice ``$value`` and the
``$key`` from the choices array (``$index`` is related to `choice_value`_). This
will give you:

.. image:: /images/reference/form/choice-example2.png
   :align: center

If your choice values are objects, then ``choice_label`` can also be a
:ref:`property path <reference-form-option-property-path>`. Imagine you have some
``Status`` class with a ``getDisplayName()`` method::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('attending', ChoiceType::class, array(
        'choices' => array(
            new Status(Status::YES),
            new Status(Status::NO),
            new Status(Status::MAYBE),
        ),
        'choices_as_values' => true,
        'choice_label' => 'displayName',
    ));
