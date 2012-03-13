disabled
~~~~~~~~
**type**: ``boolean``  **default**: ``false``

If you don't want a user to modify the value of a field, you can set 
the disabled option to true. Any submitted value will be ignored.

.. code-block:: php

    use Symfony\Component\Form\TextField
      
    $field = new TextField('status', array(
        'data' => 'Old data',
        'disabled' => true,
    ));
    $field->submit('New data');

    // prints "Old data"
    echo $field->getData();


