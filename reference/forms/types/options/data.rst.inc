data
~~~~

**type**: ``mixed`` **default**: Defaults to field of the underlying structure.

When you attach a form type to a form, it becomes a field that initially maps
the value of the corresponding property or key of the form's domain data. If
you want to override the initial value which will be rendered in the view for
the form or any field, you can set it in the data option::

    $builder->add('token', 'hidden', array(
        'data' => 'abcdef',
    ));

.. note::

    The default values for form fields are taken directly from the underlying
    data structure matching the field name with a property of an object or a
    key of an array. The ``data`` option overrides this default value.
