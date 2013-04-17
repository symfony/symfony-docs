attr
~~~~

**type**: array **default**: Empty array

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    $builder->add('save', 'button', array(
        'attr' => array('class' => 'save'),
    ));



