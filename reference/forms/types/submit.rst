.. index::
   single: Forms; Fields; submit

submit Field Type
=================

.. versionadded:: 2.3
    The ``submit`` type was introduced in Symfony 2.3

A submit button.

+----------------------+----------------------------------------------------------------------+
| Rendered as          | ``button`` ``submit`` tag                                            |
+----------------------+----------------------------------------------------------------------+
| Inherited            | - `attr`_                                                            |
| options              | - `disabled`_                                                        |
|                      | - `label`_                                                           |
|                      | - `label_attr`_                                                      |
|                      | - `translation_domain`_                                              |
|                      | - `validation_groups`_                                               |
+----------------------+----------------------------------------------------------------------+
| Parent type          | :doc:`button</reference/forms/types/button>`                         |
+----------------------+----------------------------------------------------------------------+
| Class                | :class:`Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType` |
+----------------------+----------------------------------------------------------------------+

The Submit button has an additional method
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` that lets you
check whether this button was used to submit the form. This is especially
useful when :ref:`a form has multiple submit buttons <book-form-submitting-multiple-buttons>`::

    if ($form->get('save')->isClicked()) {
        // ...
    }

Inherited Options
-----------------

.. include:: /reference/forms/types/options/button_attr.rst.inc

.. include:: /reference/forms/types/options/button_disabled.rst.inc

.. include:: /reference/forms/types/options/button_label.rst.inc

.. include:: /reference/forms/types/options/label_attr.rst.inc

.. include:: /reference/forms/types/options/button_translation_domain.rst.inc

validation_groups
~~~~~~~~~~~~~~~~~

**type**: ``array`` **default**: ``null``

When your form contains multiple submit buttons, you can change the validation
group based on the button which was used to submit the form. Imagine a registration
form wizard with buttons to go to the previous or the next step::

    $form = $this->createFormBuilder($user)
        ->add('previousStep', 'submit', array(
            'validation_groups' => false,
        ))
        ->add('nextStep', 'submit', array(
            'validation_groups' => array('Registration'),
        ))
        ->getForm();

The special ``false`` ensures that no validation is performed when the previous
step button is clicked. When the second button is clicked, all constraints
from the "Registration" are validated.

.. seealso::

    You can read more about this in :ref:`the Form chapter <book-form-validation-groups>`
    of the book.

Form Variables
--------------

========  ===========  ==============================================================
Variable  Type         Usage
========  ===========  ==============================================================
clicked   ``Boolean``  Whether the button is clicked or not.
========  ===========  ==============================================================
