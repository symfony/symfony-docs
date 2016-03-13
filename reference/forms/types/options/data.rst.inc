data
~~~~

**type**: ``mixed`` **default**: Defaults to field of the underlying object (if there is one)

When you create a form, each field initially displays the value of the
corresponding property of the form's domain object (if an object is bound
to the form). If you want to override the initial value for the form or
just an individual field, you can set it in the data option::

    use Symfony\Component\Form\Extension\Core\Type\HiddenType;
    // ...

    $builder->add('token', HiddenType::class, array(
        'data' => 'abcdef',
    ));

.. note::

    The default values for form fields are taken directly from the underlying
    data structure (e.g. an entity or an array). The ``data`` option overrides
    this default value.
