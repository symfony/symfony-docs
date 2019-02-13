.. index::
   single: Forms; Fields; ResetType

ResetType Field
===============

A button that resets all fields to their original values.

+----------------------+---------------------------------------------------------------------+
| Rendered as          | ``input`` ``reset`` tag                                             |
+----------------------+---------------------------------------------------------------------+
| Inherited            | - `attr`_                                                           |
| options              | - `disabled`_                                                       |
|                      | - `label`_                                                          |
|                      | - `translation_domain`_                                             |
|                      | - `label_translation_parameters`_                                   |
|                      | - `attr_translation_parameters`_                                    |
+----------------------+---------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType </reference/forms/types/button>`                   |
+----------------------+---------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType` |
+----------------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Inherited Options
-----------------

attr
~~~~

**type**: ``array`` **default**: ``[]``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('save', ResetType::class, [
        'attr' => ['class' => 'save'],
    ]);

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc

label_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Translated `label`_ can contain
:ref:`placeholders <component-translation-placeholders>`.
This option allows you to pass an array of parameters in order to replace
placeholders with actual values.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order.reset: Reset an order to %company%

you can specify placeholder value:

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, array(
        'label' => 'form.order.reset',
        'label_translation_parameters' => array(
            '%company%' => 'ACME Inc.',
        ),
    ));

Note that ``label_translation_parameters`` of resets are merged with those of its
parent. In other words the parent's translation parameters are available for
children's resets but can be overriden:

.. code-block:: php

    // App/Controller/OrderController.php
    use App\Form\OrderType;
    // ...

    $form = $this->createForm(OrderType::class, $order, array(
        // available to all children, grandchildren and so on.
        'label_translation_parameters' => array(
            '%company%' => 'ACME',
        ),
    ));

.. code-block:: php

    // App/Form/OrderType.php
    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, array(
        'label' => 'form.order.reset',
        // Value of parent's 'label_translation_parameters' will be merged with
        // this field's empty 'label_translation_parameters'.
        // array('%company%' => 'ACME') will be used to translate this label.
    ));

.. code-block:: php

    // App/Form/OrderType.php
    use Symfony\Component\Form\Extension\Core\Type\ResetType;
    // ...

    $builder->add('send', ResetType::class, array(
        'label' => 'form.order.reset',
        'label_translation_parameters' => array(
            '%company%' => 'American Company Making Everything',
        ),
        // Value of parent's 'label_translation_parameters' will be merged with
        // this reset's 'label_translation_parameters'.
        // array('%company%' => 'American Company Making Everything')
        // will be passed to translate this label.
    ));

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc
