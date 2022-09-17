``label``
~~~~~~~~~

**type**: ``string`` or ``TranslatableMessage`` **default**: The label is "guessed" from the field name

Sets the label that will be used when rendering the field. Setting to ``false``
will suppress the label::

    use Symfony\Component\Translation\TranslatableMessage;

    $builder
        ->add('zipCode', null, [
            'label' => 'The ZIP/Postal code',
            // optionally, you can use TranslatableMessage objects as the label content
            'label' => new TranslatableMessage('address.zipCode', ['%country%' => $country], 'address'),
        ])

The label can also be set in the template:

.. configuration-block::

    .. code-block:: twig

        {{ form_label(form.name, 'Your name') }}

    .. code-block:: php

        echo $view['form']->label(
            $form['name'],
            'Your name'
        );
