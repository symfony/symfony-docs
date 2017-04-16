label_format
~~~~~~~~~~~~

.. versionadded:: 2.6
    The ``label_format`` option was introduced in Symfony 2.6.

**type**: ``string`` **default**: ``null``

This allows to change the label for a field. You have access to 2 variables:
``%name%`` and ```%id%``. The ``%id%`` is unique for each field and contains
the complete path to and the field name, the ``%name%`` contains just the field
name.

When using translation keys as labels, you often end up with duplicated labels.
For instance, ``registration_first_name`` and ``profile_first_name``. To avoid
this, you can use the label format::

    // ...
    $formBuilder
        ->add('registration', new RegistrationType(), array(
            'label_format' => 'form.person.%name%',
        ))
        ->add('profile', new ProfileType(), array(
            'label_format' => 'form.person.%name%',
        ))
    ;

The option is inherited to the child types. With the code above, the
``first_name`` field of both forms will have the ``form.person.first_name``
label.
