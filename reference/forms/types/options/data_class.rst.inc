.. versionadded:: 2.4
    The ``data_class`` option was introduced in Symfony 2.4.

data_class
~~~~~~~~~~

**type**: ``string``

This option is used to set the appropriate data mapper to be used by the form,
so you can use it for any form field type which requires an object.

    $builder->add('media', 'sonata_media_type', array(
        'data_class' => 'Acme\DemoBundle\Entity\Media',
    ));
