.. index::
   single: Form; Form::submit()

How to Use the submit() Function to Handle Form Submissions
===========================================================

The recommended way of :ref:`processing Symfony forms <processing-forms>` is to
use the :method:`Symfony\\Component\\Form\\FormInterface::handleRequest` method
to detect when the form has been submitted. However, you can also use the
:method:`Symfony\\Component\\Form\\FormInterface::submit` method to have better
control over when exactly your form is submitted and what data is passed to it::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function new(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        if ($request->isMethod('POST')) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isSubmitted() && $form->isValid()) {
                // perform some action...

                return $this->redirectToRoute('task_success');
            }
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

.. tip::

    Forms consisting of nested fields expect an array in
    :method:`Symfony\\Component\\Form\\FormInterface::submit`. You can also submit
    individual fields by calling :method:`Symfony\\Component\\Form\\FormInterface::submit`
    directly on the field::

        $form->get('firstName')->submit('Fabien');

.. tip::

    When submitting a form via a "PATCH" request, you may want to update only a few
    submitted fields. To achieve this, you may pass an optional second boolean
    argument to ``submit()``. Passing ``false`` will remove any missing fields
    within the form object. Otherwise, the missing fields will be set to ``null``.

.. caution::

    When the second parameter ``$clearMissing`` is ``false``, like with the
    "PATCH" method, the validation will only apply to the submitted fields. If
    you need to validate all the underlying data, add the required fields
    manually so that they are validated::

        // 'email' and 'username' are added manually to force their validation
        $form->submit(array_merge(['email' => null, 'username' => null], $request->request->all()), false);
        
.. caution::

When submitting a form via a "POST" request and manually submitting the form with ``submit()``, ensure that list of fields in payload equals the list of validated fields in FormType class - in other case the form validation will fail.

//'json' represents payload data (used frequently by api client as React/Angular/Vue etc.)
$form->submit(array_merge($json, $request->request->all()));
