attr
~~~~

**type**: ``array`` **default**: ``array()``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('save', ButtonType::class, array(
        'attr' => array('class' => 'save'),
    ));
