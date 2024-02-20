How to Submit a Form with Multiple Buttons
==========================================

When your form contains more than one submit button, you will want to check
which of the buttons was clicked to adapt the program flow in your controller.
To do this, add a second button with the caption "Save and Add" to your form::

    $form = $this->createFormBuilder($task)
        ->add('task', TextType::class)
        ->add('dueDate', DateType::class)
        ->add('save', SubmitType::class, ['label' => 'Create Task'])
        ->add('saveAndAdd', SubmitType::class, ['label' => 'Save and Add'])
        ->getForm();

In your controller, use the button's
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` method for
querying if the "Save and Add" button was clicked::

    if ($form->isSubmitted() && $form->isValid()) {
        // ... perform some action, such as saving the task to the database

        $nextAction = $form->get('saveAndAdd')->isClicked()
            ? 'task_new'
            : 'task_success';

        return $this->redirectToRoute($nextAction);
    }

Or you can get the button's name by using the
:method:`Symfony\\Component\\Form\\Form::getClickedButton` method of the form::

    if ($form->getClickedButton() && 'saveAndAdd' === $form->getClickedButton()->getName()) {
        // ...
    }

    // when using nested forms, two or more buttons can have the same name;
    // in those cases, compare the button objects instead of the button names
    if ($form->getClickedButton() === $form->get('saveAndAdd')){
        // ...
    }
