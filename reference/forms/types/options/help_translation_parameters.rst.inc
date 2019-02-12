help_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Translated `help`_ can
contain :ref:`placeholders <component-translation-placeholders>`.
This option allows you to pass an array of parameters in order to replace
placeholders with actual values.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order.id.help: This will be the reference in communications with %company%

you can specify placeholder value:

.. code-block:: php

    $builder->add('id', null, array(
        'help' => 'form.order.id.help',
        'help_translation_parameters' => [
            '%company%' => 'ACME Inc.'
        ]
    ));

Note that `help_translation_parameters` of children fields are merged with those
of its parent. In other words the parent's translation parameters are available
for children but can be overriden:

.. code-block:: php

    // App/Controller/OrderController.php
    use App\Form\OrderType;
    // ...

    $form = $this->createForm(OrderType::class, $order, array(
        // available to all children, grandchildren and so on.
        'help_translation_parameters' => array(
            '%company%' => 'ACME',
        ),
    ));

    .. code-block:: php

    // App/Form/OrderType.php
    // ...

    $builder->add('id', null, array(
        'help' => 'form.order.id.help',
        // Value of parent's 'help_translation_parameters' will be merged with
        // this field's empty 'help_translation_parameters'.
        // array('%company%' => 'ACME') will be used to translate 'help'.
    ));

    .. code-block:: php

    // App/Form/OrderType.php
    // ...

    $builder->add('id', null, array(
        'help' => 'form.order.id.help',
        'help_translation_parameters' => array(
            '%company%' => 'American Company Making Everything',
        ),
        // Value of parent's 'help_translation_parameters' will be merged with
        // this field's 'help_translation_parameters'.
        // array('%company%' => 'American Company Making Everything')
        // will be used to translate 'help'.
    ));
