.. index::
   single: Forms

Forms
=====

Dealing with HTML forms is one of the most common - and challenging - tasks for
a web developer. Symfony2 integrates a Form component that makes dealing with
forms easy. In this chapter, you'll build a complex form from the ground-up,
learning the most important features of the form library along the way.

.. note::

   The Symfony Form component is a standalone library that can be used outside
   of Symfony2 projects. For more information, see the `Symfony2 Form component`_
   on GitHub.

.. index::
   single: Forms; Create a simple form

Creating a Simple Form
----------------------

Suppose you're building a simple todo list application that will need to
display "tasks". Because your users will need to edit and create tasks, you're
going to need to build a form. But before you begin, first focus on the generic
``Task`` class that represents and stores the data for a single task::

    // src/Acme/TaskBundle/Entity/Task.php
    namespace Acme\TaskBundle\Entity;

    class Task
    {
        protected $task;

        protected $dueDate;

        public function getTask()
        {
            return $this->task;
        }

        public function setTask($task)
        {
            $this->task = $task;
        }

        public function getDueDate()
        {
            return $this->dueDate;
        }

        public function setDueDate(\DateTime $dueDate = null)
        {
            $this->dueDate = $dueDate;
        }
    }

.. note::

   If you're coding along with this example, create the ``AcmeTaskBundle``
   first by running the following command (and accepting all of the default
   options):

   .. code-block:: bash

        $ php app/console generate:bundle --namespace=Acme/TaskBundle

This class is a "plain-old-PHP-object" because, so far, it has nothing
to do with Symfony or any other library. It's quite simply a normal PHP object
that directly solves a problem inside *your* application (i.e. the need to
represent a task in your application). Of course, by the end of this chapter,
you'll be able to submit data to a ``Task`` instance (via an HTML form), validate
its data, and persist it to the database.

.. index::
   single: Forms; Create a form in a controller

Building the Form
~~~~~~~~~~~~~~~~~

Now that you've created a ``Task`` class, the next step is to create and
render the actual HTML form. In Symfony2, this is done by building a form
object and then rendering it in a template. For now, this can all be done
from inside a controller::

    // src/Acme/TaskBundle/Controller/DefaultController.php
    namespace Acme\TaskBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Acme\TaskBundle\Entity\Task;
    use Symfony\Component\HttpFoundation\Request;

    class DefaultController extends Controller
    {
        public function newAction(Request $request)
        {
            // create a task and give it some dummy data for this example
            $task = new Task();
            $task->setTask('Write a blog post');
            $task->setDueDate(new \DateTime('tomorrow'));

            $form = $this->createFormBuilder($task)
                ->add('task', 'text')
                ->add('dueDate', 'date')
                ->add('save', 'submit')
                ->getForm();

            return $this->render('AcmeTaskBundle:Default:new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

.. tip::

   This example shows you how to build your form directly in the controller.
   Later, in the ":ref:`book-form-creating-form-classes`" section, you'll learn
   how to build your form in a standalone class, which is recommended as
   your form becomes reusable.

Creating a form requires relatively little code because Symfony2 form objects
are built with a "form builder". The form builder's purpose is to allow you
to write simple form "recipes", and have it do all the heavy-lifting of actually
building the form.

In this example, you've added two fields to your form - ``task`` and ``dueDate`` -
corresponding to the ``task`` and ``dueDate`` properties of the ``Task`` class.
You've also assigned each a "type" (e.g. ``text``, ``date``), which, among
other things, determines which HTML form tag(s) is rendered for that field.
Finally, you added a submit button for submitting the form to the server.

.. versionadded:: 2.3
    Support for submit buttons was introduced in Symfony 2.3. Before that, you had
    to add buttons to the form's HTML manually.

Symfony2 comes with many built-in types that will be discussed shortly
(see :ref:`book-forms-type-reference`).

.. index::
  single: Forms; Basic template rendering

Rendering the Form
~~~~~~~~~~~~~~~~~~

Now that the form has been created, the next step is to render it. This is
done by passing a special form "view" object to your template (notice the
``$form->createView()`` in the controller above) and using a set of form
helper functions:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}

        {{ form(form) }}

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Default/new.html.php -->

        <?php echo $view['form']->form($form) ?>

.. image:: /images/book/form-simple.png
    :align: center

.. note::

    This example assumes that you submit the form in a "POST" request and to
    the same URL that it was displayed in. You will learn later how to
    change the request method and the target URL of the form.

That's it! By printing ``form(form)``, each field in the form is rendered, along
with a label and error message (if there is one). The ``form`` function also
surrounds everything in the necessary HTML ``<form>`` tag. As easy as this is,
it's not very flexible (yet). Usually, you'll want to render each form field
individually so you can control how the form looks. You'll learn how to do
that in the ":ref:`form-rendering-template`" section.

Before moving on, notice how the rendered ``task`` input field has the value
of the ``task`` property from the ``$task`` object (i.e. "Write a blog post").
This is the first job of a form: to take data from an object and translate
it into a format that's suitable for being rendered in an HTML form.

.. tip::

   The form system is smart enough to access the value of the protected
   ``task`` property via the ``getTask()`` and ``setTask()`` methods on the
   ``Task`` class. Unless a property is public, it *must* have a "getter" and
   "setter" method so that the Form component can get and put data onto the
   property. For a Boolean property, you can use an "isser" or "hasser" method
   (e.g. ``isPublished()`` or ``hasReminder()``) instead of a getter (e.g.
   ``getPublished()`` or ``getReminder()``).

.. index::
  single: Forms; Handling form submissions

.. _book-form-handling-form-submissions:

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

The second job of a form is to translate user-submitted data back to the
properties of an object. To make this happen, the submitted data from the
user must be written into the form. Add the following functionality to your
controller::

    // ...
    use Symfony\Component\HttpFoundation\Request;

    public function newAction(Request $request)
    {
        // just setup a fresh $task object (remove the dummy data)
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database

            return $this->redirect($this->generateUrl('task_success'));
        }

        // ...
    }

.. versionadded:: 2.3
    The :method:`Symfony\\Component\\Form\\FormInterface::handleRequest` method
    was introduced in Symfony 2.3. Previously, the ``$request`` was passed
    to the ``submit`` method - a strategy which is deprecated and will be
    removed in Symfony 3.0. For details on that method, see :ref:`cookbook-form-submit-request`.

This controller follows a common pattern for handling forms, and has three
possible paths:

#. When initially loading the page in a browser, the form is simply created and
   rendered. :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
   recognizes that the form was not submitted and does nothing.
   :method:`Symfony\\Component\\Form\\FormInterface::isValid` returns ``false``
   if the form was not submitted.

#. When the user submits the form, :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
   recognizes this and immediately writes the submitted data back into the
   ``task`` and ``dueDate`` properties of the ``$task`` object. Then this object
   is validated. If it is invalid (validation is covered in the next section),
   :method:`Symfony\\Component\\Form\\FormInterface::isValid` returns ``false``
   again, so the form is rendered together with all validation errors;

   .. note::

       You can use the method :method:`Symfony\\Component\\Form\\FormInterface::isSubmitted`
       to check whether a form was submitted, regardless of whether or not the
       submitted data is actually valid.

#. When the user submits the form with valid data, the submitted data is again
   written into the form, but this time :method:`Symfony\\Component\\Form\\FormInterface::isValid`
   returns ``true``. Now you have the opportunity to perform some actions using
   the ``$task`` object (e.g. persisting it to the database) before redirecting
   the user to some other page (e.g. a "thank you" or "success" page).

   .. note::

      Redirecting a user after a successful form submission prevents the user
      from being able to hit the "Refresh" button of their browser and re-post
      the data.

.. index::
   single: Forms; Multiple Submit Buttons

.. _book-form-submitting-multiple-buttons:

Submitting Forms with Multiple Buttons
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    Support for buttons in forms was introduced in Symfony 2.3.

When your form contains more than one submit button, you will want to check
which of the buttons was clicked to adapt the program flow in your controller.
To do this, add a second button with the caption "Save and add" to your form::

    $form = $this->createFormBuilder($task)
        ->add('task', 'text')
        ->add('dueDate', 'date')
        ->add('save', 'submit')
        ->add('saveAndAdd', 'submit')
        ->getForm();

In your controller, use the button's
:method:`Symfony\\Component\\Form\\ClickableInterface::isClicked` method for
querying if the "Save and add" button was clicked::

    if ($form->isValid()) {
        // ... perform some action, such as saving the task to the database

        $nextAction = $form->get('saveAndAdd')->isClicked()
            ? 'task_new'
            : 'task_success';

        return $this->redirect($this->generateUrl($nextAction));
    }

.. index::
   single: Forms; Validation

.. _book-forms-form-validation:

Form Validation
---------------

In the previous section, you learned how a form can be submitted with valid
or invalid data. In Symfony2, validation is applied to the underlying object
(e.g. ``Task``). In other words, the question isn't whether the "form" is
valid, but whether or not the ``$task`` object is valid after the form has
applied the submitted data to it. Calling ``$form->isValid()`` is a shortcut
that asks the ``$task`` object whether or not it has valid data.

Validation is done by adding a set of rules (called constraints) to a class. To
see this in action, add validation constraints so that the ``task`` field cannot
be empty and the ``dueDate`` field cannot be empty and must be a valid \DateTime
object.

.. configuration-block::

    .. code-block:: yaml

        # Acme/TaskBundle/Resources/config/validation.yml
        Acme\TaskBundle\Entity\Task:
            properties:
                task:
                    - NotBlank: ~
                dueDate:
                    - NotBlank: ~
                    - Type: \DateTime

    .. code-block:: php-annotations

        // Acme/TaskBundle/Entity/Task.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Task
        {
            /**
             * @Assert\NotBlank()
             */
            public $task;

            /**
             * @Assert\NotBlank()
             * @Assert\Type("\DateTime")
             */
            protected $dueDate;
        }

    .. code-block:: xml

        <!-- Acme/TaskBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\TaskBundle\Entity\Task">
                <property name="task">
                    <constraint name="NotBlank" />
                </property>
                <property name="dueDate">
                    <constraint name="NotBlank" />
                    <constraint name="Type">\DateTime</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // Acme/TaskBundle/Entity/Task.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Type;

        class Task
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('task', new NotBlank());

                $metadata->addPropertyConstraint('dueDate', new NotBlank());
                $metadata->addPropertyConstraint(
                    'dueDate',
                    new Type('\DateTime')
                );
            }
        }

That's it! If you re-submit the form with invalid data, you'll see the
corresponding errors printed out with the form.

.. _book-forms-html5-validation-disable:

.. sidebar:: HTML5 Validation

   As of HTML5, many browsers can natively enforce certain validation constraints
   on the client side. The most common validation is activated by rendering
   a ``required`` attribute on fields that are required. For browsers that
   support HTML5, this will result in a native browser message being displayed
   if the user tries to submit the form with that field blank.

   Generated forms take full advantage of this new feature by adding sensible
   HTML attributes that trigger the validation. The client-side validation,
   however, can be disabled by adding the ``novalidate`` attribute to the
   ``form`` tag or ``formnovalidate`` to the submit tag. This is especially
   useful when you want to test your server-side validation constraints,
   but are being prevented by your browser from, for example, submitting
   blank fields.

   .. configuration-block::

       .. code-block:: html+jinja

           {# src/Acme/DemoBundle/Resources/views/Default/new.html.twig #}

           {{ form(form, {'attr': {'novalidate': 'novalidate'}}) }}

       .. code-block:: html+php

           <!-- src/Acme/DemoBundle/Resources/views/Default/new.html.php -->

           <?php echo $view['form']->form($form, array(
               'attr' => array('novalidate' => 'novalidate'),
           )) ?>

Validation is a very powerful feature of Symfony2 and has its own
:doc:`dedicated chapter </book/validation>`.

.. index::
   single: Forms; Validation groups

.. _book-forms-validation-groups:

Validation Groups
~~~~~~~~~~~~~~~~~

If your object takes advantage of :ref:`validation groups <book-validation-validation-groups>`,
you'll need to specify which validation group(s) your form should use::

    $form = $this->createFormBuilder($users, array(
        'validation_groups' => array('registration'),
    ))->add(...);

If you're creating :ref:`form classes <book-form-creating-form-classes>` (a
good practice), then you'll need to add the following to the ``setDefaultOptions()``
method::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('registration'),
        ));
    }

In both of these cases, *only* the ``registration`` validation group will
be used to validate the underlying object.

.. index::
   single: Forms; Disabling validation

Disabling Validation
~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    The ability to set ``validation_groups`` to false was introduced in Symfony 2.3.

Sometimes it is useful to suppress the validation of a form altogether. For
these cases you can set the ``validation_groups`` option to ``false``::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => false,
        ));
    }

Note that when you do that, the form will still run basic integrity checks,
for example whether an uploaded file was too large or whether non-existing
fields were submitted. If you want to suppress validation, you can use the
:ref:`POST_SUBMIT event <cookbook-dynamic-form-modification-suppressing-form-validation>`.

.. index::
   single: Forms; Validation groups based on submitted data

Groups based on the Submitted Data
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need some advanced logic to determine the validation groups (e.g.
based on submitted data), you can set the ``validation_groups`` option
to an array callback::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array(
                'Acme\AcmeBundle\Entity\Client',
                'determineValidationGroups',
            ),
        ));
    }

This will call the static method ``determineValidationGroups()`` on the
``Client`` class after the form is submitted, but before validation is executed.
The Form object is passed as an argument to that method (see next example).
You can also define whole logic inline by using a ``Closure``::

    use Symfony\Component\Form\FormInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => function(FormInterface $form) {
                $data = $form->getData();
                if (Entity\Client::TYPE_PERSON == $data->getType()) {
                    return array('person');
                } else {
                    return array('company');
                }
            },
        ));
    }

.. index::
   single: Forms; Validation groups based on clicked button

Groups based on the Clicked Button
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.3
    Support for buttons in forms was introduced in Symfony 2.3.

When your form contains multiple submit buttons, you can change the validation
group depending on which button is used to submit the form. For example,
consider a form in a wizard that lets you advance to the next step or go back
to the previous step. Also assume that when returning to the previous step,
the data of the form should be saved, but not validated.

First, we need to add the two buttons to the form::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('nextStep', 'submit')
        ->add('previousStep', 'submit')
        ->getForm();

Then, we configure the button for returning to the previous step to run
specific validation groups. In this example, we want it to suppress validation,
so we set its ``validation_groups`` option to false::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('previousStep', 'submit', array(
            'validation_groups' => false,
        ))
        ->getForm();

Now the form will skip your validation constraints. It will still validate
basic integrity constraints, such as checking whether an uploaded file was too
large or whether you tried to submit text in a number field.

.. index::
   single: Forms; Built-in field types

.. _book-forms-type-reference:

Built-in Field Types
--------------------

Symfony comes standard with a large group of field types that cover all of
the common form fields and data types you'll encounter:

.. include:: /reference/forms/types/map.rst.inc

You can also create your own custom field types. This topic is covered in
the ":doc:`/cookbook/form/create_custom_field_type`" article of the cookbook.

.. index::
   single: Forms; Field type options

Field Type Options
~~~~~~~~~~~~~~~~~~

Each field type has a number of options that can be used to configure it.
For example, the ``dueDate`` field is currently being rendered as 3 select
boxes. However, the :doc:`date field </reference/forms/types/date>` can be
configured to be rendered as a single text box (where the user would enter
the date as a string in the box)::

    ->add('dueDate', 'date', array('widget' => 'single_text'))

.. image:: /images/book/form-simple2.png
    :align: center

Each field type has a number of different options that can be passed to it.
Many of these are specific to the field type and details can be found in
the documentation for each type.

.. sidebar:: The ``required`` Option

    The most common option is the ``required`` option, which can be applied to
    any field. By default, the ``required`` option is set to ``true``, meaning
    that HTML5-ready browsers will apply client-side validation if the field
    is left blank. If you don't want this behavior, either set the ``required``
    option on your field to ``false`` or
    :ref:`disable HTML5 validation <book-forms-html5-validation-disable>`.

    Also note that setting the ``required`` option to ``true`` will **not**
    result in server-side validation to be applied. In other words, if a
    user submits a blank value for the field (either with an old browser
    or web service, for example), it will be accepted as a valid value unless
    you use Symfony's ``NotBlank`` or ``NotNull`` validation constraint.

    In other words, the ``required`` option is "nice", but true server-side
    validation should *always* be used.

.. sidebar:: The ``label`` Option

    The label for the form field can be set using the ``label`` option,
    which can be applied to any field::

        ->add('dueDate', 'date', array(
            'widget' => 'single_text',
            'label'  => 'Due Date',
        ))

    The label for a field can also be set in the template rendering the
    form, see below. If you don't need a label associated to your input,
    you can disable it by setting its value to ``false``.

.. index::
   single: Forms; Field type guessing

.. _book-forms-field-guessing:

Field Type Guessing
-------------------

Now that you've added validation metadata to the ``Task`` class, Symfony
already knows a bit about your fields. If you allow it, Symfony can "guess"
the type of your field and set it up for you. In this example, Symfony can
guess from the validation rules that both the ``task`` field is a normal
``text`` field and the ``dueDate`` field is a ``date`` field::

    public function newAction()
    {
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('task')
            ->add('dueDate', null, array('widget' => 'single_text'))
            ->add('save', 'submit')
            ->getForm();
    }

The "guessing" is activated when you omit the second argument to the ``add()``
method (or if you pass ``null`` to it). If you pass an options array as the
third argument (done for ``dueDate`` above), these options are applied to
the guessed field.

.. caution::

    If your form uses a specific validation group, the field type guesser
    will still consider *all* validation constraints when guessing your
    field types (including constraints that are not part of the validation
    group(s) being used).

.. index::
   single: Forms; Field type guessing

Field Type Options Guessing
~~~~~~~~~~~~~~~~~~~~~~~~~~~

In addition to guessing the "type" for a field, Symfony can also try to guess
the correct values of a number of field options.

.. tip::

    When these options are set, the field will be rendered with special HTML
    attributes that provide for HTML5 client-side validation. However, it
    doesn't generate the equivalent server-side constraints (e.g. ``Assert\Length``).
    And though you'll need to manually add your server-side validation, these
    field type options can then be guessed from that information.

* ``required``: The ``required`` option can be guessed based on the validation
  rules (i.e. is the field ``NotBlank`` or ``NotNull``) or the Doctrine metadata
  (i.e. is the field ``nullable``). This is very useful, as your client-side
  validation will automatically match your validation rules.

* ``maxlength``: If the field is some sort of text field, then the ``maxlength``
  option attribute can be guessed from the validation constraints (if ``Length`` or
  ``Range`` is used) or from the Doctrine metadata (via the field's length).

.. note::

  These field options are *only* guessed if you're using Symfony to guess
  the field type (i.e. omit or pass ``null`` as the second argument to ``add()``).

If you'd like to change one of the guessed values, you can override it by
passing the option in the options field array::

    ->add('task', null, array('attr' => array('maxlength' => 4)))

.. index::
   single: Forms; Rendering in a template

.. _form-rendering-template:

Rendering a Form in a Template
------------------------------

So far, you've seen how an entire form can be rendered with just one line
of code. Of course, you'll usually need much more flexibility when rendering:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}
        {{ form_start(form) }}
            {{ form_errors(form) }}

            {{ form_row(form.task) }}
            {{ form_row(form.dueDate) }}
        {{ form_end(form) }}

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Default/newAction.html.php -->
        <?php echo $view['form']->start($form) ?>
            <?php echo $view['form']->errors($form) ?>

            <?php echo $view['form']->row($form['task']) ?>
            <?php echo $view['form']->row($form['dueDate']) ?>
        <?php echo $view['form']->end($form) ?>

Take a look at each part:

* ``form_start(form)`` - Renders the start tag of the form.

* ``form_errors(form)`` - Renders any errors global to the whole form
  (field-specific errors are displayed next to each field);

* ``form_row(form.dueDate)`` - Renders the label, any errors, and the HTML
  form widget for the given field (e.g. ``dueDate``) inside, by default, a
  ``div`` element;

* ``form_end()`` - Renders the end tag of the form and any fields that have not
  yet been rendered. This is useful for rendering hidden fields and taking
  advantage of the automatic :ref:`CSRF Protection <forms-csrf>`.

The majority of the work is done by the ``form_row`` helper, which renders
the label, errors and HTML form widget of each field inside a ``div`` tag
by default. In the :ref:`form-theming` section, you'll learn how the ``form_row``
output can be customized on many different levels.

.. tip::

    You can access the current data of your form via ``form.vars.value``:

    .. configuration-block::

        .. code-block:: jinja

            {{ form.vars.value.task }}

        .. code-block:: html+php

            <?php echo $form->vars['value']->getTask() ?>

.. index::
   single: Forms; Rendering each field by hand

Rendering each Field by Hand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``form_row`` helper is great because you can very quickly render each
field of your form (and the markup used for the "row" can be customized as
well). But since life isn't always so simple, you can also render each field
entirely by hand. The end-product of the following is the same as when you
used the ``form_row`` helper:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_start(form) }}
            {{ form_errors(form) }}

            <div>
                {{ form_label(form.task) }}
                {{ form_errors(form.task) }}
                {{ form_widget(form.task) }}
            </div>

            <div>
                {{ form_label(form.dueDate) }}
                {{ form_errors(form.dueDate) }}
                {{ form_widget(form.dueDate) }}
            </div>

            <div>
                {{ form_widget(form.save) }}
            </div>

        {{ form_end(form) }}

    .. code-block:: html+php

        <?php echo $view['form']->start($form) ?>

            <?php echo $view['form']->errors($form) ?>

            <div>
                <?php echo $view['form']->label($form['task']) ?>
                <?php echo $view['form']->errors($form['task']) ?>
                <?php echo $view['form']->widget($form['task']) ?>
            </div>

            <div>
                <?php echo $view['form']->label($form['dueDate']) ?>
                <?php echo $view['form']->errors($form['dueDate']) ?>
                <?php echo $view['form']->widget($form['dueDate']) ?>
            </div>

            <div>
                <?php echo $view['form']->widget($form['save']) ?>
            </div>

        <?php echo $view['form']->end($form) ?>

If the auto-generated label for a field isn't quite right, you can explicitly
specify it:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_label(form.task, 'Task Description') }}

    .. code-block:: html+php

        <?php echo $view['form']->label($form['task'], 'Task Description') ?>

Some field types have additional rendering options that can be passed
to the widget. These options are documented with each type, but one common
options is ``attr``, which allows you to modify attributes on the form element.
The following would add the ``task_field`` class to the rendered input text
field:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form_widget(form.task, {'attr': {'class': 'task_field'}}) }}

    .. code-block:: html+php

        <?php echo $view['form']->widget($form['task'], array(
            'attr' => array('class' => 'task_field'),
        )) ?>

If you need to render form fields "by hand" then you can access individual
values for fields such as the ``id``, ``name`` and ``label``. For example
to get the ``id``:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form.task.vars.id }}

    .. code-block:: html+php

        <?php echo $form['task']->vars['id']?>

To get the value used for the form field's name attribute you need to use
the ``full_name`` value:

.. configuration-block::

    .. code-block:: html+jinja

        {{ form.task.vars.full_name }}

    .. code-block:: html+php

        <?php echo $form['task']->vars['full_name'] ?>

Twig Template Function Reference
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're using Twig, a full reference of the form rendering functions is
available in the :doc:`reference manual </reference/forms/twig_reference>`.
Read this to know everything about the helpers available and the options
that can be used with each.

.. index::
   single: Forms; Changing the action and method

.. _book-forms-changing-action-and-method:

Changing the Action and Method of a Form
----------------------------------------

So far, the ``form_start()`` helper has been used to render the form's start
tag and we assumed that each form is submitted to the same URL in a POST request.
Sometimes you want to change these parameters. You can do so in a few different
ways. If you build your form in the controller, you can use ``setAction()`` and
``setMethod()``::

    $form = $this->createFormBuilder($task)
        ->setAction($this->generateUrl('target_route'))
        ->setMethod('GET')
        ->add('task', 'text')
        ->add('dueDate', 'date')
        ->add('save', 'submit')
        ->getForm();

.. note::

    This example assumes that you've created a route called ``target_route``
    that points to the controller that processes the form.

In :ref:`book-form-creating-form-classes` you will learn how to move the
form building code into separate classes. When using an external form class
in the controller, you can pass the action and method as form options::

    $form = $this->createForm(new TaskType(), $task, array(
        'action' => $this->generateUrl('target_route'),
        'method' => 'GET',
    ));

Finally, you can override the action and method in the template by passing them
to the ``form()`` or the ``form_start()`` helper:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}
        {{ form(form, {'action': path('target_route'), 'method': 'GET'}) }}

        {{ form_start(form, {'action': path('target_route'), 'method': 'GET'}) }}

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Default/newAction.html.php -->
        <?php echo $view['form']->form($form, array(
            'action' => $view['router']->generate('target_route'),
            'method' => 'GET',
        )) ?>

        <?php echo $view['form']->start($form, array(
            'action' => $view['router']->generate('target_route'),
            'method' => 'GET',
        )) ?>

.. note::

    If the form's method is not GET or POST, but PUT, PATCH or DELETE, Symfony2
    will insert a hidden field with the name ``_method`` that stores this method.
    The form will be submitted in a normal POST request, but Symfony2's router
    is capable of detecting the ``_method`` parameter and will interpret it as
    a PUT, PATCH or DELETE request. Read the cookbook chapter
    ":doc:`/cookbook/routing/method_parameters`" for more information.

.. index::
   single: Forms; Creating form classes

.. _book-form-creating-form-classes:

Creating Form Classes
---------------------

As you've seen, a form can be created and used directly in a controller.
However, a better practice is to build the form in a separate, standalone PHP
class, which can then be reused anywhere in your application. Create a new class
that will house the logic for building the task form::

    // src/Acme/TaskBundle/Form/Type/TaskType.php
    namespace Acme\TaskBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('task')
                ->add('dueDate', null, array('widget' => 'single_text'))
                ->add('save', 'submit');
        }

        public function getName()
        {
            return 'task';
        }
    }

This new class contains all the directions needed to create the task form
(note that the ``getName()`` method should return a unique identifier for this
form "type"). It can be used to quickly build a form object in the controller::

    // src/Acme/TaskBundle/Controller/DefaultController.php

    // add this new use statement at the top of the class
    use Acme\TaskBundle\Form\Type\TaskType;

    public function newAction()
    {
        $task = ...;
        $form = $this->createForm(new TaskType(), $task);

        // ...
    }

Placing the form logic into its own class means that the form can be easily
reused elsewhere in your project. This is the best way to create forms, but
the choice is ultimately up to you.

.. _book-forms-data-class:

.. sidebar:: Setting the ``data_class``

    Every form needs to know the name of the class that holds the underlying
    data (e.g. ``Acme\TaskBundle\Entity\Task``). Usually, this is just guessed
    based off of the object passed to the second argument to ``createForm``
    (i.e. ``$task``). Later, when you begin embedding forms, this will no
    longer be sufficient. So, while not always necessary, it's generally a
    good idea to explicitly specify the ``data_class`` option by adding the
    following to your form type class::

        use Symfony\Component\OptionsResolver\OptionsResolverInterface;

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\TaskBundle\Entity\Task',
            ));
        }

.. tip::

    When mapping forms to objects, all fields are mapped. Any fields on the
    form that do not exist on the mapped object will cause an exception to
    be thrown.

    In cases where you need extra fields in the form (for example: a "do you
    agree with these terms" checkbox) that will not be mapped to the underlying
    object, you need to set the ``mapped`` option to ``false``::

        use Symfony\Component\Form\FormBuilderInterface;

        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('task')
                ->add('dueDate', null, array('mapped' => false))
                ->add('save', 'submit');
        }

    Additionally, if there are any fields on the form that aren't included in
    the submitted data, those fields will be explicitly set to ``null``.

    The field data can be accessed in a controller with::

        $form->get('dueDate')->getData();

    In addition, the data of an unmapped field can also be modified directly::

        $form->get('dueDate')->setData(new \DateTime());

Defining your Forms as Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Defining your form type as a service is a good practice and makes it really
easy to use in your application.

.. note::

    Services and the service container will be handled
    :doc:`later on in this book </book/service_container>`. Things will be
    more clear after reading that chapter.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/TaskBundle/Resources/config/services.yml
        services:
            acme_demo.form.type.task:
                class: Acme\TaskBundle\Form\Type\TaskType
                tags:
                    - { name: form.type, alias: task }

    .. code-block:: xml

        <!-- src/Acme/TaskBundle/Resources/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd>

            <service id="acme_demo.form.type.task"
                class="Acme\TaskBundle\Form\Type\TaskType">
                <tag name="form.type" alias="task" />
            </service>
        </container>

    .. code-block:: php

        // src/Acme/TaskBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->register(
                'acme_demo.form.type.task',
                'Acme\TaskBundle\Form\Type\TaskType'
            )
            ->addTag('form.type', array(
                'alias' => 'task',
            ))
        ;

That's it! Now you can use your form type directly in a controller::

    // src/Acme/TaskBundle/Controller/DefaultController.php
    // ...

    public function newAction()
    {
        $task = ...;
        $form = $this->createForm('task', $task);

        // ...
    }

or even use from within the form type of another form::

    // src/Acme/TaskBundle/Form/Type/ListType.php
    // ...

    class ListType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            // ...

            $builder->add('someTask', 'task');
        }
    }

Read :ref:`form-cookbook-form-field-service` for more information.

.. index::
   pair: Forms; Doctrine

Forms and Doctrine
------------------

The goal of a form is to translate data from an object (e.g. ``Task``) to an
HTML form and then translate user-submitted data back to the original object. As
such, the topic of persisting the ``Task`` object to the database is entirely
unrelated to the topic of forms. But, if you've configured the ``Task`` class
to be persisted via Doctrine (i.e. you've added
:ref:`mapping metadata <book-doctrine-adding-mapping>` for it), then persisting
it after a form submission can be done when the form is valid::

    if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        return $this->redirect($this->generateUrl('task_success'));
    }

If, for some reason, you don't have access to your original ``$task`` object,
you can fetch it from the form::

    $task = $form->getData();

For more information, see the :doc:`Doctrine ORM chapter </book/doctrine>`.

The key thing to understand is that when the form is submitted, the submitted
data is transferred to the underlying object immediately. If you want to
persist that data, you simply need to persist the object itself (which already
contains the submitted data).

.. index::
   single: Forms; Embedded forms

Embedded Forms
--------------

Often, you'll want to build a form that will include fields from many different
objects. For example, a registration form may contain data belonging to
a ``User`` object as well as many ``Address`` objects. Fortunately, this
is easy and natural with the Form component.

Embedding a Single Object
~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose that each ``Task`` belongs to a simple ``Category`` object. Start,
of course, by creating the ``Category`` object::

    // src/Acme/TaskBundle/Entity/Category.php
    namespace Acme\TaskBundle\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Category
    {
        /**
         * @Assert\NotBlank()
         */
        public $name;
    }

Next, add a new ``category`` property to the ``Task`` class::

    // ...

    class Task
    {
        // ...

        /**
         * @Assert\Type(type="Acme\TaskBundle\Entity\Category")
         */
        protected $category;

        // ...

        public function getCategory()
        {
            return $this->category;
        }

        public function setCategory(Category $category = null)
        {
            $this->category = $category;
        }
    }

Now that your application has been updated to reflect the new requirements,
create a form class so that a ``Category`` object can be modified by the user::

    // src/Acme/TaskBundle/Form/Type/CategoryType.php
    namespace Acme\TaskBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class CategoryType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder->add('name');
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'Acme\TaskBundle\Entity\Category',
            ));
        }

        public function getName()
        {
            return 'category';
        }
    }

The end goal is to allow the ``Category`` of a ``Task`` to be modified right
inside the task form itself. To accomplish this, add a ``category`` field
to the ``TaskType`` object whose type is an instance of the new ``CategoryType``
class:

.. code-block:: php

    use Symfony\Component\Form\FormBuilderInterface;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('category', new CategoryType());
    }

The fields from ``CategoryType`` can now be rendered alongside those from
the ``TaskType`` class. To activate validation on CategoryType, add
the ``cascade_validation`` option to ``TaskType``::

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\TaskBundle\Entity\Task',
            'cascade_validation' => true,
        ));
    }

Render the ``Category`` fields in the same way as the original ``Task`` fields:

.. configuration-block::

    .. code-block:: html+jinja

        {# ... #}

        <h3>Category</h3>
        <div class="category">
            {{ form_row(form.category.name) }}
        </div>

        {# ... #}

    .. code-block:: html+php

        <!-- ... -->

        <h3>Category</h3>
        <div class="category">
            <?php echo $view['form']->row($form['category']['name']) ?>
        </div>

        <!-- ... -->

When the user submits the form, the submitted data for the ``Category`` fields
are used to construct an instance of ``Category``, which is then set on the
``category`` field of the ``Task`` instance.

The ``Category`` instance is accessible naturally via ``$task->getCategory()``
and can be persisted to the database or used however you need.

Embedding a Collection of Forms
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also embed a collection of forms into one form (imagine a ``Category``
form with many ``Product`` sub-forms). This is done by using the ``collection``
field type.

For more information see the ":doc:`/cookbook/form/form_collections`" cookbook
entry and the :doc:`collection </reference/forms/types/collection>` field type reference.

.. index::
   single: Forms; Theming
   single: Forms; Customizing fields

.. _form-theming:

Form Theming
------------

Every part of how a form is rendered can be customized. You're free to change
how each form "row" renders, change the markup used to render errors, or
even customize how a ``textarea`` tag should be rendered. Nothing is off-limits,
and different customizations can be used in different places.

Symfony uses templates to render each and every part of a form, such as
``label`` tags, ``input`` tags, error messages and everything else.

In Twig, each form "fragment" is represented by a Twig block. To customize
any part of how a form renders, you just need to override the appropriate block.

In PHP, each form "fragment" is rendered via an individual template file.
To customize any part of how a form renders, you just need to override the
existing template by creating a new one.

To understand how this works, customize the ``form_row`` fragment and
add a class attribute to the ``div`` element that surrounds each row. To
do this, create a new template file that will store the new markup:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Form/fields.html.twig #}
        {% block form_row %}
        {% spaceless %}
            <div class="form_row">
                {{ form_label(form) }}
                {{ form_errors(form) }}
                {{ form_widget(form) }}
            </div>
        {% endspaceless %}
        {% endblock form_row %}

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Form/form_row.html.php -->
        <div class="form_row">
            <?php echo $view['form']->label($form, $label) ?>
            <?php echo $view['form']->errors($form) ?>
            <?php echo $view['form']->widget($form, $parameters) ?>
        </div>

The ``form_row`` form fragment is used when rendering most fields via the
``form_row`` function. To tell the Form component to use your new ``form_row``
fragment defined above, add the following to the top of the template that
renders the form:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}
        {% form_theme form 'AcmeTaskBundle:Form:fields.html.twig' %}

        {% form_theme form 'AcmeTaskBundle:Form:fields.html.twig' 'AcmeTaskBundle:Form:fields2.html.twig' %}

        <!-- ... render the form -->

    .. code-block:: html+php

        <!-- src/Acme/TaskBundle/Resources/views/Default/new.html.php -->
        <?php $view['form']->setTheme($form, array('AcmeTaskBundle:Form')) ?>

        <?php $view['form']->setTheme($form, array('AcmeTaskBundle:Form', 'AcmeTaskBundle:Form')) ?>

        <!-- ... render the form -->

The ``form_theme`` tag (in Twig) "imports" the fragments defined in the given
template and uses them when rendering the form. In other words, when the
``form_row`` function is called later in this template, it will use the ``form_row``
block from your custom theme (instead of the default ``form_row`` block
that ships with Symfony).

Your custom theme does not have to override all the blocks. When rendering a block
which is not overridden in your custom theme, the theming engine will fall back
to the global theme (defined at the bundle level).

If several custom themes are provided they will be searched in the listed order
before falling back to the global theme.

To customize any portion of a form, you just need to override the appropriate
fragment. Knowing exactly which block or file to override is the subject of
the next section.

.. code-block:: html+jinja

    {# src/Acme/TaskBundle/Resources/views/Default/new.html.twig #}

    {% form_theme form with 'AcmeTaskBundle:Form:fields.html.twig' %}

    {% form_theme form with ['AcmeTaskBundle:Form:fields.html.twig', 'AcmeTaskBundle:Form:fields2.html.twig'] %}

For a more extensive discussion, see :doc:`/cookbook/form/form_customization`.

.. index::
   single: Forms; Template fragment naming

.. _form-template-blocks:

Form Fragment Naming
~~~~~~~~~~~~~~~~~~~~

In Symfony, every part of a form that is rendered - HTML form elements, errors,
labels, etc. - is defined in a base theme, which is a collection of blocks
in Twig and a collection of template files in PHP.

In Twig, every block needed is defined in a single template file (`form_div_layout.html.twig`_)
that lives inside the `Twig Bridge`_. Inside this file, you can see every block
needed to render a form and every default field type.

In PHP, the fragments are individual template files. By default they are located in
the `Resources/views/Form` directory of the framework bundle (`view on GitHub`_).

Each fragment name follows the same basic pattern and is broken up into two pieces,
separated by a single underscore character (``_``). A few examples are:

* ``form_row`` - used by ``form_row`` to render most fields;
* ``textarea_widget`` - used by ``form_widget`` to render a ``textarea`` field
  type;
* ``form_errors`` - used by ``form_errors`` to render errors for a field;

Each fragment follows the same basic pattern: ``type_part``. The ``type`` portion
corresponds to the field *type* being rendered (e.g. ``textarea``, ``checkbox``,
``date``, etc) whereas the ``part`` portion corresponds to *what* is being
rendered (e.g. ``label``, ``widget``, ``errors``, etc). By default, there
are 4 possible *parts* of a form that can be rendered:

+-------------+--------------------------+---------------------------------------------------------+
| ``label``   | (e.g. ``form_label``)    | renders the field's label                               |
+-------------+--------------------------+---------------------------------------------------------+
| ``widget``  | (e.g. ``form_widget``)   | renders the field's HTML representation                 |
+-------------+--------------------------+---------------------------------------------------------+
| ``errors``  | (e.g. ``form_errors``)   | renders the field's errors                              |
+-------------+--------------------------+---------------------------------------------------------+
| ``row``     | (e.g. ``form_row``)      | renders the field's entire row (label, widget & errors) |
+-------------+--------------------------+---------------------------------------------------------+

.. note::

    There are actually 2 other *parts*  - ``rows`` and ``rest`` -
    but you should rarely if ever need to worry about overriding them.

By knowing the field type (e.g. ``textarea``) and which part you want to
customize (e.g. ``widget``), you can construct the fragment name that needs
to be overridden (e.g. ``textarea_widget``).

.. index::
   single: Forms; Template fragment inheritance

Template Fragment Inheritance
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, the fragment you want to customize will appear to be missing.
For example, there is no ``textarea_errors`` fragment in the default themes
provided with Symfony. So how are the errors for a textarea field rendered?

The answer is: via the ``form_errors`` fragment. When Symfony renders the errors
for a textarea type, it looks first for a ``textarea_errors`` fragment before
falling back to the ``form_errors`` fragment. Each field type has a *parent*
type (the parent type of ``textarea`` is ``text``, its parent is ``form``),
and Symfony uses the fragment for the parent type if the base fragment doesn't
exist.

So, to override the errors for *only* ``textarea`` fields, copy the
``form_errors`` fragment, rename it to ``textarea_errors`` and customize it. To
override the default error rendering for *all* fields, copy and customize the
``form_errors`` fragment directly.

.. tip::

    The "parent" type of each field type is available in the
    :doc:`form type reference </reference/forms/types>` for each field type.

.. index::
   single: Forms; Global Theming

Global Form Theming
~~~~~~~~~~~~~~~~~~~

In the above example, you used the ``form_theme`` helper (in Twig) to "import"
the custom form fragments into *just* that form. You can also tell Symfony
to import form customizations across your entire project.

Twig
....

To automatically include the customized blocks from the ``fields.html.twig``
template created earlier in *all* templates, modify your application configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        twig:
            form:
                resources:
                    - 'AcmeTaskBundle:Form:fields.html.twig'
            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/twig http://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                    <twig:form>
                        <twig:resource>AcmeTaskBundle:Form:fields.html.twig</twig:resource>
                    </twig:form>
                    <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('twig', array(
            'form' => array(
                'resources' => array(
                    'AcmeTaskBundle:Form:fields.html.twig',
                ),
            ),
            // ...
        ));

Any blocks inside the ``fields.html.twig`` template are now used globally
to define form output.

.. sidebar::  Customizing Form Output all in a Single File with Twig

    In Twig, you can also customize a form block right inside the template
    where that customization is needed:

    .. code-block:: html+jinja

        {% extends '::base.html.twig' %}

        {# import "_self" as the form theme #}
        {% form_theme form _self %}

        {# make the form fragment customization #}
        {% block form_row %}
            {# custom field row output #}
        {% endblock form_row %}

        {% block content %}
            {# ... #}

            {{ form_row(form.task) }}
        {% endblock %}

    The ``{% form_theme form _self %}`` tag allows form blocks to be customized
    directly inside the template that will use those customizations. Use
    this method to quickly make form output customizations that will only
    ever be needed in a single template.

    .. caution::

        This ``{% form_theme form _self %}`` functionality will *only* work
        if your template extends another. If your template does not, you
        must point ``form_theme`` to a separate template.

PHP
...

To automatically include the customized templates from the ``Acme/TaskBundle/Resources/views/Form``
directory created earlier in *all* templates, modify your application configuration
file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            templating:
                form:
                    resources:
                        - 'AcmeTaskBundle:Form'
        # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ...>
                <framework:templating>
                    <framework:form>
                        <framework:resource>AcmeTaskBundle:Form</framework:resource>
                    </framework:form>
                </framework:templating>
                <!-- ... -->
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            'templating' => array(
                'form' => array(
                    'resources' => array(
                        'AcmeTaskBundle:Form',
                    ),
                ),
            )
            // ...
        ));

Any fragments inside the ``Acme/TaskBundle/Resources/views/Form`` directory
are now used globally to define form output.

.. index::
   single: Forms; CSRF protection

.. _forms-csrf:

CSRF Protection
---------------

CSRF - or `Cross-site request forgery`_ - is a method by which a malicious
user attempts to make your legitimate users unknowingly submit data that
they don't intend to submit. Fortunately, CSRF attacks can be prevented by
using a CSRF token inside your forms.

The good news is that, by default, Symfony embeds and validates CSRF tokens
automatically for you. This means that you can take advantage of the CSRF
protection without doing anything. In fact, every form in this chapter has
taken advantage of the CSRF protection!

CSRF protection works by adding a hidden field to your form - called ``_token``
by default - that contains a value that only you and your user knows. This
ensures that the user - not some other entity - is submitting the given data.
Symfony automatically validates the presence and accuracy of this token.

The ``_token`` field is a hidden field and will be automatically rendered
if you include the ``form_end()`` function in your template, which ensures
that all un-rendered fields are output.

The CSRF token can be customized on a form-by-form basis. For example::

    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class TaskType extends AbstractType
    {
        // ...

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'data_class'      => 'Acme\TaskBundle\Entity\Task',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention'       => 'task_item',
            ));
        }

        // ...
    }

To disable CSRF protection, set the ``csrf_protection`` option to false.
Customizations can also be made globally in your project. For more information,
see the :ref:`form configuration reference <reference-framework-form>`
section.

.. note::

    The ``intention`` option is optional but greatly enhances the security of
    the generated token by making it different for each form.

.. index::
   single: Forms; With no class

Using a Form without a Class
----------------------------

In most cases, a form is tied to an object, and the fields of the form get
and store their data on the properties of that object. This is exactly what
you've seen so far in this chapter with the `Task` class.

But sometimes, you may just want to use a form without a class, and get back
an array of the submitted data. This is actually really easy::

    // make sure you've imported the Request namespace above the class
    use Symfony\Component\HttpFoundation\Request;
    // ...

    public function contactAction(Request $request)
    {
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('message', 'textarea')
            ->add('send', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
        }

        // ... render the form
    }

By default, a form actually assumes that you want to work with arrays of
data, instead of an object. There are exactly two ways that you can change
this behavior and tie the form to an object instead:

#. Pass an object when creating the form (as the first argument to ``createFormBuilder``
   or the second argument to ``createForm``);

#. Declare the ``data_class`` option on your form.

If you *don't* do either of these, then the form will return the data as
an array. In this example, since ``$defaultData`` is not an object (and
no ``data_class`` option is set), ``$form->getData()`` ultimately returns
an array.

.. tip::

    You can also access POST values (in this case "name") directly through
    the request object, like so::

        $this->get('request')->request->get('name');

    Be advised, however, that in most cases using the ``getData()`` method is
    a better choice, since it returns the data (usually an object) after
    it's been transformed by the form framework.

Adding Validation
~~~~~~~~~~~~~~~~~

The only missing piece is validation. Usually, when you call ``$form->isValid()``,
the object is validated by reading the constraints that you applied to that
class. If your form is mapped to an object (i.e. you're using the ``data_class``
option or passing an object to your form), this is almost always the approach
you want to use. See :doc:`/book/validation` for more details.

.. _form-option-constraints:

But if the form is not mapped to an object and you instead want to retrieve a
simple array of your submitted data, how can you add constraints to the data of
your form?

The answer is to setup the constraints yourself, and attach them to the individual
fields. The overall approach is covered a bit more in the :ref:`validation chapter <book-validation-raw-values>`,
but here's a short example:

.. code-block:: php

    use Symfony\Component\Validator\Constraints\Length;
    use Symfony\Component\Validator\Constraints\NotBlank;

    $builder
       ->add('firstName', 'text', array(
           'constraints' => new Length(array('min' => 3)),
       ))
       ->add('lastName', 'text', array(
           'constraints' => array(
               new NotBlank(),
               new Length(array('min' => 3)),
           ),
       ))
    ;

.. tip::

    If you are using validation groups, you need to either reference the
    ``Default`` group when creating the form, or set the correct group on
    the constraint you are adding.

.. code-block:: php

    new NotBlank(array('groups' => array('create', 'update'))

Final Thoughts
--------------

You now know all of the building blocks necessary to build complex and
functional forms for your application. When building forms, keep in mind that
the first goal of a form is to translate data from an object (``Task``) to an
HTML form so that the user can modify that data. The second goal of a form is to
take the data submitted by the user and to re-apply it to the object.

There's still much more to learn about the powerful world of forms, such as
how to handle
:doc:`file uploads with Doctrine </cookbook/doctrine/file_uploads>` or how
to create a form where a dynamic number of sub-forms can be added (e.g. a
todo list where you can keep adding more fields via JavaScript before submitting).
See the cookbook for these topics. Also, be sure to lean on the
:doc:`field type reference documentation </reference/forms/types>`, which
includes examples of how to use each field type and its options.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/doctrine/file_uploads`
* :doc:`File Field Reference </reference/forms/types/file>`
* :doc:`Creating Custom Field Types </cookbook/form/create_custom_field_type>`
* :doc:`/cookbook/form/form_customization`
* :doc:`/cookbook/form/dynamic_form_modification`
* :doc:`/cookbook/form/data_transformers`

.. _`Symfony2 Form component`: https://github.com/symfony/Form
.. _`DateTime`: http://php.net/manual/en/class.datetime.php
.. _`Twig Bridge`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bridge/Twig
.. _`form_div_layout.html.twig`: https://github.com/symfony/symfony/blob/master/src/Symfony/Bridge/Twig/Resources/views/Form/form_div_layout.html.twig
.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
.. _`view on GitHub`: https://github.com/symfony/symfony/tree/master/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form
