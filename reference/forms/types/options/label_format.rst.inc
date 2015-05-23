label_format
~~~~~~~~~~~~

.. versionadded:: 2.6
    The ``label_format`` option was introduced in Symfony 2.6

**type**: ``string`` **default**: The label is the "humanized" version of the field name.

Sets the format applied to generate the ``<label>`` element, which will be used when
rendering the label for the field. This option supports two placeholders called
``%name%`` and ``%id%``::

    // defining the label format for all the form fields
    $form = $this->createForm('myform', $data, array('label_format' => 'form.label.%id%'));

    // defining the label format for an individual form field (otherwise the label
    // format is inherited from the parent form)
    $builder->add('some_field', 'some_type', array(
        // ...
        'label_format' => '%name% (%id%)',
    ));
