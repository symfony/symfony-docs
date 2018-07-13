translation_parameters
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Translated `label`_, `help`_ and some attr_ (``title``, ``placeholder``) can
contain :ref:`placeholders <component-translation-placeholders>`.
This option allows you to pass an array of parameters in order to replace
placeholders with actual values.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order_list.id: Identifier of the order to %company%

you can specify placeholder value:

.. code-block:: php

    $builder->add('id', null, array(
        'label' => 'form.order_list.id',
        'translation_parameters' => [
            '%company%' => 'ACME Inc.'
        ]
    ));

Note that `translation_parameters` of children fields are merged with those
of parent. In other words the parent's parameters are available for children
but can be overriden:

.. code-block:: php

    // App\Controller\OrderListController

    $form = $this->createForm(OrderListType::class, null, array(
        'translation_parameters' => array(
            '%company%' => 'ACME Inc.'
        )
    ));

.. code-block:: php

    // App\Form\OrderListType

    $builder->add('id', null, array(
        'label' => 'form.order_list.id'
    ));
