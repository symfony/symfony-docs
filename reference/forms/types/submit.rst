.. index::
   single: Forms; Fields; SubmitType

SubmitType Field
================

A submit button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` ``submit`` tag                                            |
+----------------------+----------------------------------------------------------------------+
| Inherited            | - `attr`_                                                            |
| options              | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `label_format`_                                                    |
|                      | - `translation_domain`_                                              |
|                      | - `label_translation_parameters`_                                    |
|                      | - `attr_translation_parameters`_                                     |
|                      | - `validation_groups`_                                               |
+----------------------+----------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType</reference/forms/types/button>`                     |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType` |
+----------------------+----------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_debug_form.rst.inc

The Submit button has an additional method
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` that lets
you check whether this button was used to submit the form. This is especially
useful when :doc:`a form has multiple submit buttons </form/multiple_buttons>`::

    if ($form->get('save')->isClicked()) {
        // ...
    }

Inherited Options
-----------------

attr
~~~~

**type**: ``array`` **default**: ``[]``

If you want to add extra attributes to the HTML representation of the button,
you can use ``attr`` option. It's an associative array with HTML attribute
as a key. This can be useful when you need to set a custom class for the button::

    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $builder->add('save', SubmitType::class, [
        'attr' => ['class' => 'save'],
    ]);

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/label_format.rst.inc

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

    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $builder->add('send', SubmitType::class, array(
        'label' => 'form.order.submit_to_company',
        'label_translation_parameters' => array(
            '%company%' => 'ACME Inc.',
        ),
    ));

Note that `label_translation_parameters` of submits are merged with those of its
parent. In other words the parent's translation parameters are available for
children's submits but can be overriden:

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
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $builder->add('send', SubmitType::class, array(
        'label' => 'form.order.submit_to_company',
        // Value of parent's 'label_translation_parameters' will be merged with
        // this field's empty 'label_translation_parameters'.
        // array('%company%' => 'ACME') will be used to translate this label.
    ));

.. code-block:: php

    // App/Form/OrderType.php
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $builder->add('send', SubmitType::class, array(
        'label' => 'form.order.submit_to_company',
        'label_translation_parameters' => array(
            '%company%' => 'American Company Making Everything',
        ),
        // Value of parent's 'label_translation_parameters' will be merged with
        // this submit's 'label_translation_parameters'.
        // array('%company%' => 'American Company Making Everything')
        // will be passed to translate this label.
    ));

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc

validation_groups
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``null``

When your form contains multiple submit buttons, you can change the validation
group based on the button which was used to submit the form. Imagine a registration
form wizard with buttons to go to the previous or the next step::

    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $form = $this->createFormBuilder($user)
        ->add('previousStep', SubmitType::class, [
            'validation_groups' => false,
        ])
        ->add('nextStep', SubmitType::class, [
            'validation_groups' => ['Registration'],
        ])
        ->getForm();

The special ``false`` ensures that no validation is performed when the previous
step button is clicked. When the second button is clicked, all constraints
from the "Registration" are validated.

.. seealso::

    You can read more about this in :doc:`/form/data_based_validation`.

Form Variables
--------------

========  ===========  ==============================================================
Variable  Type         Usage
========  ===========  ==============================================================
clicked   ``boolean``  Whether the button is clicked or not.
========  ===========  ==============================================================
