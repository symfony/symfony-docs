.. index::
    single: Forms; Validation groups based on clicked button

How to Choose Validation Groups Based on the Clicked Button
===========================================================

When your form contains multiple submit buttons, you can change the validation
group depending on which button is used to submit the form. For example,
consider a form in a wizard that lets you advance to the next step or go back
to the previous step. Also assume that when returning to the previous step,
the data of the form should be saved, but not validated.

First, we need to add the two buttons to the form::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('nextStep', SubmitType::class)
        ->add('previousStep', SubmitType::class)
        ->getForm();

Then, we configure the button for returning to the previous step to run
specific validation groups. In this example, we want it to suppress validation,
so we set its ``validation_groups`` option to false::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('previousStep', SubmitType::class, [
            'validation_groups' => false,
        ])
        ->getForm();

Now the form will skip your validation constraints. It will still validate
basic integrity constraints, such as checking whether an uploaded file was too
large or whether you tried to submit text in a number field.

.. seealso::

    To see how to use a service to resolve ``validation_groups`` dynamically
    read the :doc:`/form/validation_group_service_resolver` article.
