help
~~~~

**type**: ``string`` or ``TranslatableInterface`` **default**: ``null``

Allows you to define a help message for the form field, which by default is
rendered below the field::

    use Symfony\Component\Translation\TranslatableMessage;

    $builder
        ->add('zipCode', null, [
            'help' => 'The ZIP/Postal code for your credit card\'s billing address.',
        ])

        // ...

        ->add('status', null, [
            'help' => new TranslatableMessage('order.status', ['%order_id%' => $order->getId()], 'store'),
        ])
    ;
