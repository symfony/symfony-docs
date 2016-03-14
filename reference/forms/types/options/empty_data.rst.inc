empty_data
~~~~~~~~~~

**type**: ``mixed``

.. This file should only be included with start-after or end-before that's
   set to this placeholder value. Its purpose is to let us include only
   part of this file.

DEFAULT_PLACEHOLDER

This option determines what value the field will return when the submitted
value is empty.

But you can customize this to your needs. For example, if you want the
``gender`` choice field to be explicitly set to ``null`` when no value is
selected, you can do it like this::

    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('gender', ChoiceType::class, array(
        'choices' => array(
            'm' => 'Male',
            'f' => 'Female'
        ),
        'required'    => false,
        'placeholder' => 'Choose your gender',
        'empty_data'  => null
    ));

.. note::

    If you want to set the ``empty_data`` option for your entire form class,
    see the cookbook article :doc:`/cookbook/form/use_empty_data`.
