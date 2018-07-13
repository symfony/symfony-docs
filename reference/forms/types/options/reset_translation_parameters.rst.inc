translation_parameters
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Translated `label`_ can contain
:ref:`placeholders <component-translation-placeholders>`.
This option allows you to pass an array of parameters in order to replace
placeholders with actual values.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order_list.submit_to_company: Send an order to %company%

you can specify placeholder value:

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, array(
        'label' => 'form.order_list.submit_to_company',
        'translation_parameters' => array(
            '%company%' => 'ACME Inc.'
        )
    ));

Note that `translation_parameters` of reset fields are merged with those of
parent form. In other words the parent's parameters are available for reset
fields but can be overriden:

.. code-block:: php

    // App\Controller\OrderListController

    $form = $this->createForm(OrderListType::class, null, array(
        'translation_parameters' => array(
            '%company%' => 'ACME Inc.'
        )
    ));

.. code-block:: php

    // App\Form\OrderListType
    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, array(
        'label' => 'form.order_list.submit_to_company',
    ));
