.. index::
   single: Form; Form::submit()

How to Use the submit() Function to Handle Form Submissions
===========================================================

With the ``handleRequest()`` method, it is really easy to handle form
submissions::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            // ...
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action...

            return $this->redirectToRoute('task_success');
        }

        return $this->render('AppBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. tip::

    To see more about this method, read :ref:`book-form-handling-form-submissions`.

.. _cookbook-form-call-submit-directly:

Calling Form::submit() manually
-------------------------------

In some cases, you want better control over when exactly your form is submitted
and what data is passed to it. Instead of using the
:method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
method, pass the submitted data directly to
:method:`Symfony\\Component\\Form\\FormInterface::submit`::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            // ...
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isValid()) {
                // perform some action...

                return $this->redirectToRoute('task_success');
            }
        }

        return $this->render('AppBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. tip::

    Forms consisting of nested fields expect an array in
    :method:`Symfony\\Component\\Form\\FormInterface::submit`. You can also submit
    individual fields by calling :method:`Symfony\\Component\\Form\\FormInterface::submit`
    directly on the field::

        $form->get('firstName')->submit('Fabien');
