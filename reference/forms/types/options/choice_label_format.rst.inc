choice_label_format
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

Using this option, you can use a pre-defined format for each choice's label::

    $builder->add('fruits', 'choice', array(
        // ...
        'choice_label_format' => 'my_shopping_cart.choices.%name%.%choice%'
        'choices' => array(
          'apple',
          'banana',
          'cherry',
          '...',
          'zucchini',
        )
    ));

From the given example above, the resulting labels would look like this:

    my_form.choices.fruits.apple
    my_form.choices.fruits.banana
    my_form.choices.fruits.cherry
    ...
    my_form.choices.fruits.cherry

For the format itself, you have access to the following placeholders:

``%name%``
    The name of the field
``%id%``
    The ID of the field
``%value%``
    The value of the choice
``%choice%``
    The value of the label of the choice

.. tip::

    You can also define this option on the :doc:`form type </reference/forms/types/form>`.
    This let's all choice fields in that form use the given format.

.. tip::

    If you would like all your forms to use the same format, just :doc:`create a Form extension </cookbook/form/create_form_type_extension>`;
    have it extend the type ``form``, and add the `choice_label_format` as one of it's default options.
