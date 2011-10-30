attr
~~~~

**type**: array **default**: Empty array

If you want to add extra attributes to HTML field representation
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for some widget::

    $builder->add('body', 'textarea', array(
        'attr' => array('class' => 'tinymce'),
    ));



