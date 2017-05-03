data
~~~~

**type**: ``mixed`` **default**: Defaults to field of the underlying structure.

When you create a form, each field initially displays the value of the
corresponding property of the form's domain data (e.g. if you bind an object to
the form). If you want to override this initial value for the form or
an individual field, you can set it in the data option::


    $builder->add('token', 'hidden', array(
        'data' => 'abcdef',
    ));

.. caution::

    The ``data`` option *always* overrides the value taken from the domain data
    (object) when rendering. This means the object value is also overriden when
    the form edits an already persisted object, causing it to lose it's
    persisted value when the form is submitted.
