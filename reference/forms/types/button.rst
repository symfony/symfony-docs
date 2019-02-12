.. index::
   single: Forms; Fields; ButtonType

ButtonType Field
================

A simple, non-responsive button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` tag                                                       |
+----------------------+----------------------------------------------------------------------+
| Inherited            | - `attr`_                                                            |
| options              | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `translation_domain`_                                              |
|                      | - `label_translation_parameters`_                                    |
|                      | - `attr_translation_parameters`_                                     |
+----------------------+----------------------------------------------------------------------+
| Parent type          | none                                                                 |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType` |
+----------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

Inherited Options
-----------------

The following options are defined in the
:class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\BaseType` class.
The ``BaseType`` class is the parent class for both the ``button`` type
and the :doc:`FormType </reference/forms/types/form>`, but it is not part
of the form type tree (i.e. it cannot be used as a form type on its own).

attr
~~~~

**type**: ``array`` **default**: ``[]``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('save', ButtonType::class, [
        'attr' => ['class' => 'save'],
    ]);

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc

label_translation_parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``array()``

Translated `label`_ can contain
:ref:`placeholders <component-translation-placeholders>`.
This option allows you to pass an array of parameters in order to replace
placeholders with actual values.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yml
    form.order.submit_to_company: Send an order to %company%

you can specify placeholder value:

.. code-block:: php

    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('send', ButtonType::class, array(
        'label' => 'form.order.submit_to_company',
        'label_translation_parameters' => array(
            '%company%' => 'ACME Inc.',
        ),
    ));

Note that `label_translation_parameters` of buttons are merged with those of its
parent. In other words the parent's translation parameters are available for
children's buttons but can be overriden:

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
    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('send', ButtonType::class, array(
        'label' => 'form.order.submit_to_company',
        // Value of parent's 'label_translation_parameters' will be merged with
        // this field's empty 'label_translation_parameters'.
        // array('%company%' => 'ACME') will be used to translate this label.
    ));

.. code-block:: php

    // App/Form/OrderType.php
    use Symfony\Component\Form\Extension\Core\Type\ButtonType;
    // ...

    $builder->add('send', ButtonType::class, array(
        'label' => 'form.order.submit_to_company',
        'label_translation_parameters' => array(
            '%company%' => 'American Company Making Everything',
        ),
        // Value of parent's 'label_translation_parameters' will be merged with
        // this button's 'label_translation_parameters'.
        // array('%company%' => 'American Company Making Everything')
        // will be used to translate this label.
    ));

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc
