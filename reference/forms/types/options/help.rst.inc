help
~~~~

**type**: ``string`` **default**: null

Allows you to define extra information about the field, which will be rendered below the form field by default.

.. code-block:: php
    $builder->add('name', null, array(
        'help'   => 'My helper message.',
    ));
