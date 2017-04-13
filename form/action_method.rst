.. index::
    single: Forms; Changing the action and method

How to Change the Action and Method of a Form
=============================================

By default, a form will be submitted via an HTTP POST request to the same
URL under which the form was rendered. Sometimes you want to change these
parameters. You can do so in a few different ways.

If you use the :class:`Symfony\\Component\\Form\\FormBuilder` to build your
form, you can use ``setAction()`` and ``setMethod()``::

    $form = $this->createFormBuilder($task)
        ->setAction($this->generateUrl('target_route'))
        ->setMethod('GET')
        ->add('task', TextType::class)
        ->add('dueDate', DateType::class)
        ->add('save', SubmitType::class)
        ->getForm();

.. note::

    This example assumes that you've created a route called ``target_route``
    that points to the controller that processes the form.

When using a form type class, you can pass the action and method as form
options::

    use AppBundle\Form\TaskType;
    // ...

    $form = $this->createForm(TaskType::class, $task, array(
        'action' => $this->generateUrl('target_route'),
        'method' => 'GET',
    ));

Finally, you can override the action and method in the template by passing them
to the ``form()`` or the ``form_start()`` helper functions:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/default/new.html.twig #}
        {{ form_start(form, {'action': path('target_route'), 'method': 'GET'}) }}

    .. code-block:: html+php

        <!-- app/Resources/views/default/new.html.php -->
        <?php echo $view['form']->start($form, array(
            // The path() method was introduced in Symfony 2.8. Prior to 2.8,
            // you had to use generate().
            'action' => $view['router']->path('target_route'),
            'method' => 'GET',
        )) ?>

.. note::

    If the form's method is not GET or POST, but PUT, PATCH or DELETE, Symfony
    will insert a hidden field with the name ``_method`` that stores this method.
    The form will be submitted in a normal POST request, but Symfony's router
    is capable of detecting the ``_method`` parameter and will interpret it as
    a PUT, PATCH or DELETE request. See the :ref:`configuration-framework-http_method_override`
    option.
