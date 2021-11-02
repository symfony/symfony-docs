.. index::
   single: Forms; Fields; SubmitType

SubmitType Field
================

A submit button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` ``submit`` tag                                            |
+----------------------+----------------------------------------------------------------------+
| Parent type          | :doc:`ButtonType </reference/forms/types/button>`                    |
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

Options
-------

validate
~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Set this option to ``false`` to disable the client-side validation of the form
performed by the browser.

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

**type**: ``array`` **default**: ``[]``

The content of the `label`_ option is translated before displaying it, so it
can contain :ref:`translation placeholders <component-translation-placeholders>`.
This option defines the values used to replace those placeholders.

Given this translation message:

.. code-block:: yaml

    # translations/messages.en.yaml
    form.order.submit_to_company: 'Send an order to %company%'

You can specify the placeholder values as follows::

    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    // ...

    $builder->add('send', SubmitType::class, [
        'label' => 'form.order.submit_to_company',
        'label_translation_parameters' => [
            '%company%' => 'ACME Inc.',
        ],
    ]);

The ``label_translation_parameters`` option of buttons is merged with the same
option of its parents, so buttons can reuse and/or override any of the parent
placeholders.

.. include:: /reference/forms/types/options/attr_translation_parameters.rst.inc

.. include:: /reference/forms/types/options/row_attr.rst.inc

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
