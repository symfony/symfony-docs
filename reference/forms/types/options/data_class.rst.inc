data_class
~~~~~~~~~~

**type**: ``string``

This option is used to set the appropriate data mapper to be used by the form,
so you can use it for any form field type which requires an object.

.. code-block:: php

    $builder->add('media', 'sonata_media_type', array(
        'data_class' => 'Acme\DemoBundle\Entity\Media',
    ));
