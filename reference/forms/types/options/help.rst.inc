help
~~~~

**type**: ``string`` **default**: null

Allows you to define a help message for the form field, which by default is
rendered below the field::

    $builder->add('zipCode', null, [
        'help' => 'The ZIP/Postal code for your credit card\'s billing address.',
    ]);
