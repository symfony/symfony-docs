.. index::
    single: Forms; Changing the action and method

How to Change the Action and Method of a Form
=============================================

By default, a form will be submitted via an HTTP POST request to the same
URL under which the form was rendered. Sometimes you want to change these
parameters. You can do so in a few different ways.

If you use the :class:`Symfony\\Component\\Form\\FormBuilder` to build your
form, you can use ``setAction()`` and ``setMethod()``:

.. configuration-block::

    .. code-block:: php-symfony

        // AppBundle/Controller/DefaultController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;

        class DefaultController extends Controller
        {
            public function newAction()
            {
                $form = $this->createFormBuilder($task)
                    ->setAction($this->generateUrl('target_route'))
                    ->setMethod('GET')
                    ->add('task', 'text')
                    ->add('dueDate', 'date')
                    ->add('save', 'submit')
                    ->getForm();

                // ...
            }
        }

    .. code-block:: php-standalone

        use Symfony\Component\Form\Forms;

        // ...

        $formFactoryBuilder = Forms::createFormFactoryBuilder();

        // Form factory builder configuration ...

        $formFactory = $formFactoryBuilder->getFormFactory();

        $form = $formFactory->createBuilder('form', $task)
            ->setAction($this->generateUrl('target_route'))
            ->setMethod('GET')
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->add('save', 'submit')
            ->getForm();

.. note::

    This example assumes that you've created a route called ``target_route``
    that points to the controller that processes the form.

When using a form type class, you can pass the action and method as form
options:

.. configuration-block::

    .. code-block:: php-symfony

        // AppBundle/Controller/DefaultController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use AppBundle\Form\TaskType;

        class DefaultController extends Controller
        {
            public function newAction()
            {
                // ...

                $form = $this->createForm(new TaskType(), $task, array(
                    'action' => $this->generateUrl('target_route'),
                    'method' => 'GET',
                ));

                // ...
            }
        }
    

    .. code-block:: php-standalone

        use Symfony\Component\Form\Forms;
        use AppBundle\Form\TaskType;

        $formFactoryBuilder = Forms::createFormFactoryBuilder();

        // Form factory builder configuration ...

        $formFactory = $formFactoryBuilder->getFormFactory();

        $form = $formFactory->create(new TaskType(), $task, array(
            'action' => $this->generateUrl('target_route'),
            'method' => 'GET',
        ));

Finally, you can override the action and method in the template by passing them
to the ``form()`` or the ``form_start()`` helper functions:

.. code-block:: html+twig

    {# app/Resources/views/default/new.html.twig #}
    {{ form_start(form, {'action': path('target_route'), 'method': 'GET'}) }}

.. note::

    If the form's method is not GET or POST, but PUT, PATCH or DELETE, Symfony
    will insert a hidden field with the name ``_method`` that stores this method.
    The form will be submitted in a normal POST request, but Symfony's router
    is capable of detecting the ``_method`` parameter and will interpret it as
    a PUT, PATCH or DELETE request. See the :ref:`configuration-framework-http_method_override`
    option.
