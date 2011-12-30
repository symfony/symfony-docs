data
~~~~

**type**: mixed **default**: Defaults to field of the underlying object (if there is one)

When you create a form, each field initially displays the value of the 
corresponding property of the form's domain object (if an object is bound
to the form). If you want to override the initial value for the form or just
an individual field, you can set it in the data option::

    $builder->add('token', 'hidden', array(
        'data' => 'abcdef',
    ));


