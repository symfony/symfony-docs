choice_label
~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``choice_label`` option was introduced in Symfony 2.7.

**type**: ``string``, ``callable`` or ``false`` **default**: ``null``

Normally, the array key of each item in the ``choices`` option is used as the
text that's shown to the user. The ``choice_label`` option allows you to take
more control::

    $builder->add('attending', 'choice', array(
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

    $builder->add('attending', 'choice', array(
        'choices' => array(
            new Status(Status::YES),
            new Status(Status::NO),
            new Status(Status::MAYBE),
        ),
        'choices_as_values' => true,
        'choice_label' => 'displayName',
    ));

If set to ``false``, all the tag labels will be discarded for radio or checkbox
inputs. You can also return ``false`` from the callable to discard certain labels.
