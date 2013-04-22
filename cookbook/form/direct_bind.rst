.. index::
   single: Form; Form::bind

How to use the bind Function to handle Form Submissions
=======================================================

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

Using Form::bind() to handle a request (deprecated)
---------------------------------------------------

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

Passing the :class:`Symfony\\Component\HttpFoundation\\Request` directly to
``bind`` still works, but is deprecated and will be removed in Symfony 3.0.
However, you *can* safely pass array values directly to bind.

Passing an Array directly to Form::bind
---------------------------------------

In some cases, you may want to collect and pass an array of values directly
to a Form, instead of using the ``handleRequest`` method. This is absolutely
valid and not deprecated (passing a :class:`Symfony\\Component\HttpFoundation\\Request`
object to ``Form::bind`` is deprecated, but passing an array of ok)::

    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder()
            // ...
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request->request->get($form->getName()));

            if ($form->isValid()) {
                // perform some action...

                return $this->redirect($this->generateUrl('task_success'));
            }
        }

        return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
