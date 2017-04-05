.. index::
   single: Form; Form::submit()

How to Use the submit() Function to Handle Form Submissions
===========================================================

.. versionadded:: 2.3
    The :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
    method was introduced in Symfony 2.3.

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

        if ($form->isSubmitted() && $form->isValid()) {
            // perform some action...

            return $this->redirectToRoute('task_success');
        }

        return $this->render('AppBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. tip::

    To see more about this method, read :ref:`form-handling-form-submissions`.

.. _form-call-submit-directly:

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

            if ($form->isSubmitted() && $form->isValid()) {
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

.. tip::

    When submitting a form via a "PATCH" request, you may want to update only a few
    submitted fields. To achieve this, you may pass an optional second boolean
    argument to ``submit()``. Passing ``false`` will remove any missing fields
    within the form object. Otherwise, the missing fields will be set to ``null``.

.. caution::

    When the second parameter ``$clearMissing`` is ``false``, like with the
    "PATCH" method, the validation extension will only handle the submitted
    fields. If the underlying data needs to be validated, this should be done
    manually, i.e using the validator.

.. _form-submit-request:

Passing a Request to Form::submit() (Deprecated)
------------------------------------------------

.. versionadded:: 2.3
    Before Symfony 2.3, the ``submit()`` method was known as ``bind()``.

Before Symfony 2.3, the :method:`Symfony\\Component\\Form\\FormInterface::submit`
method accepted a :class:`Symfony\\Component\\HttpFoundation\\Request` object as
a convenient shortcut to the previous example::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            // ...
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // perform some action...

                return $this->redirectToRoute('task_success');
            }
        }

        return $this->render('AppBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

Passing the :class:`Symfony\\Component\\HttpFoundation\\Request` directly to
:method:`Symfony\\Component\\Form\\FormInterface::submit` still works, but is
deprecated and has been removed in Symfony 3.0. You should use the method
:method:`Symfony\\Component\\Form\\FormInterface::handleRequest` instead.
