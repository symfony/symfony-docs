.. index::
    single: Forms; Multiple Submit Buttons

How to Submit a Form with Multiple Buttons
==========================================

.. versionadded:: 2.3
    Support for buttons in forms was introduced in Symfony 2.3.

When your form contains more than one submit button, you will want to check
which of the buttons was clicked to adapt the program flow in your controller.
To do this, add a second button with the caption "Save and add" to your form::

    $form = $this->createFormBuilder($task)
        ->add('task', TextType::class)
        ->add('dueDate', DateType::class)
        ->add('save', SubmitType::class, array('label' => 'Create Task'))
        ->add('saveAndAdd', SubmitType::class, array('label' => 'Save and Add'))
        ->getForm();

In your controller, use the button's
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` method for
querying if the "Save and add" button was clicked::

    if ($form->isSubmitted() && $form->isValid()) {
        // ... perform some action, such as saving the task to the database

        $nextAction = $form->get('saveAndAdd')->isClicked()
            ? 'task_new'
            : 'task_success';

        return $this->redirectToRoute($nextAction);
    }
