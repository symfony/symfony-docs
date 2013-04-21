.. index::
   single: Form; Form testing

How to use the (deprecated) bind Function to handle Form Submissions
====================================================================

In Symfony 2.3, a new :method:`Symfony\Component\Form\FormInterface::handleRequest`
method was added, which makes handling form submissions easier than ever::

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

            return $this->redirect($this->generateUrl('task_success'));
        }
        
        return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. tip::

    To see more about this method, read :ref:`book-form-handling-form-submissions`.

Prior to this, the :method:`Symfony\Component\Form\FormInterface::bind` method
was used instead::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            // ...
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                // perform some action...

                return $this->redirect($this->generateUrl('task_success'));
            }
        }

        return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

This still works, but is deprecated and will be removed in Symfony 3.0.