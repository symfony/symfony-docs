empty_data
~~~~~~~~~~

**type**: ``mixed`` **default**: ``array()`` if ``multiple`` or ``expanded``, ``''`` otherwise

This option determines what value the field will return when the ``empty_value``
choice is selected.

For example, if you want the ``gender`` field to be set to ``null`` when no
value is selected, you can do it like this:

.. code-block:: php

    $builder->add('gender', 'choice', array(
        'choices' => array(
            'm' => 'Male',
            'f' => 'Female'
        ),
        'required'    => false,
        'empty_value' => 'Choose your gender',
        'empty_data'  => null
    ));
