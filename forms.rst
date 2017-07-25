.. index::
   single: Forms

Forms
=====

Dealing with HTML forms is one of the most common - and challenging - tasks for
a web developer. Symfony integrates a Form component that makes dealing with
forms easy. In this article, you'll build a complex form from the ground up,
learning the most important features of the form library along the way.

.. note::

   The Symfony Form component is a standalone library that can be used outside
   of Symfony projects. For more information, see the
   :doc:`Form component documentation </components/form>` on
   GitHub.

.. index::
   single: Forms; Create a simple form

Creating a Simple Form
----------------------

Suppose you're building a simple todo list application that will need to
display "tasks". Because your users will need to edit and create tasks, you're
going to need to build a form. But before you begin, first focus on the generic
``Task`` class that represents and stores the data for a single task::

    // src/AppBundle/Entity/Task.php
    namespace AppBundle\Entity;

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

This class is a "plain-old-PHP-object" because, so far, it has nothing
to do with Symfony or any other library. It's quite simply a normal PHP object
that directly solves a problem inside *your* application (i.e. the need to
represent a task in your application). Of course, by the end of this article,
you'll be able to submit data to a ``Task`` instance (via an HTML form), validate
its data and persist it to the database.

.. index::
   single: Forms; Create a form in a controller

Building the Form
~~~~~~~~~~~~~~~~~

Now that you've created a ``Task`` class, the next step is to create and
render the actual HTML form. In Symfony, this is done by building a form
object and then rendering it in a template. For now, this can all be done
from inside a controller::

    // src/AppBundle/Controller/DefaultController.php
    namespace AppBundle\Controller;

    use AppBundle\Entity\Task;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
                ->add('save', 'submit', array('label' => 'Create Post'))
                ->getForm();

            return $this->render('default/new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

.. tip::

   This example shows you how to build your form directly in the controller.
   Later, in the ":ref:`form-creating-form-classes`" section, you'll learn
   how to build your form in a standalone class, which is recommended as
   your form becomes reusable.

Creating a form requires relatively little code because Symfony form objects
are built with a "form builder". The form builder's purpose is to allow you
to write simple form "recipes" and have it do all the heavy-lifting of actually
building the form.

In this example, you've added two fields to your form - ``task`` and ``dueDate`` -
corresponding to the ``task`` and ``dueDate`` properties of the ``Task`` class.
You've also assigned each a "type" (e.g. ``text``, ``date``), which, among
other things, determines which HTML form tag(s) is rendered for that field.

Finally, you added a submit button with a custom label for submitting the form to
the server.

.. versionadded:: 2.3
    Support for submit buttons was introduced in Symfony 2.3. Before that, you had
    to add buttons to the form's HTML manually.

Symfony comes with many built-in types that will be discussed shortly
(see :ref:`forms-type-reference`).

.. index::
  single: Forms; Basic template rendering

Rendering the Form
~~~~~~~~~~~~~~~~~~

Now that the form has been created, the next step is to render it. This is
done by passing a special form "view" object to your template (notice the
``$form->createView()`` in the controller above) and using a set of form
helper functions:

.. code-block:: html+twig

    {# app/Resources/views/default/new.html.twig #}
    {{ form_start(form) }}
    {{ form_widget(form) }}
    {{ form_end(form) }}

.. image:: /_images/form/simple-form.png
    :align: center

.. note::

    This example assumes that you submit the form in a "POST" request and to
    the same URL that it was displayed in. You will learn later how to
    change the request method and the target URL of the form.

That's it! Just three lines are needed to render the complete form:

``form_start(form)``
    Renders the start tag of the form, including the correct enctype attribute
    when using file uploads.

``form_widget(form)``
    Renders all the fields, which includes the field element itself, a label
    and any validation error messages for the field.

``form_end(form)``
    Renders the end tag of the form and any fields that have not
    yet been rendered, in case you rendered each field yourself. This is useful
    for rendering hidden fields and taking advantage of the automatic
    :doc:`CSRF Protection </form/csrf_protection>`.

.. seealso::

    As easy as this is, it's not very flexible (yet). Usually, you'll want to
    render each form field individually so you can control how the form looks.
    You'll learn how to do that in the ":doc:`/form/rendering`" section.

Before moving on, notice how the rendered ``task`` input field has the value
of the ``task`` property from the ``$task`` object (i.e. "Write a blog post").
This is the first job of a form: to take data from an object and translate
it into a format that's suitable for being rendered in an HTML form.

.. tip::

   The form system is smart enough to access the value of the protected
   ``task`` property via the ``getTask()`` and ``setTask()`` methods on the
   ``Task`` class. Unless a property is public, it *must* have a "getter" and
   "setter" method so that the Form component can get and put data onto the
   property. For a boolean property, you can use an "isser" or "hasser" method
   (e.g. ``isPublished()`` or ``hasReminder()``) instead of a getter (e.g.
   ``getPublished()`` or ``getReminder()``).

.. index::
  single: Forms; Handling form submissions

.. _form-handling-form-submissions:

Handling Form Submissions
~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the form will submit a POST request back to the same controller that
renders it.

Here, the second job of a form is to translate user-submitted data back to the
properties of an object. To make this happen, the submitted data from the
user must be written into the Form object. Add the following functionality to
your controller::

    // ...
    use Symfony\Component\HttpFoundation\Request;

    public function newAction(Request $request)
    {
        // just setup a fresh $task object (remove the dummy data)
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->add('save', 'submit', array('label' => 'Create Task'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $em = $this->getDoctrine()->getManager();
            // $em->persist($task);
            // $em->flush();

            return $this->redirectToRoute('task_success');
        }

        return $this->render('default/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

.. caution::

    Be aware that the ``createView()`` method should be called *after* ``handleRequest()``
    is called. Otherwise, changes done in the ``*_SUBMIT`` events aren't applied to the
    view (like validation errors).

.. versionadded:: 2.3
    The :method:`Symfony\\Component\\Form\\FormInterface::handleRequest` method
    was introduced in Symfony 2.3. Previously, the ``$request`` was passed
    to the ``submit()`` method - a strategy which is deprecated and will be
    removed in Symfony 3.0. For details on that method, see :ref:`form-submit-request`.

This controller follows a common pattern for handling forms and has three
possible paths:

#. When initially loading the page in a browser, the form is created and
   rendered. :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
   recognizes that the form was not submitted and does nothing.
   :method:`Symfony\\Component\\Form\\FormInterface::isSubmitted` returns ``false``
   if the form was not submitted.

#. When the user submits the form, :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
   recognizes this and immediately writes the submitted data back into the
   ``task`` and ``dueDate`` properties of the ``$task`` object. Then this object
   is validated. If it is invalid (validation is covered in the next section),
   :method:`Symfony\\Component\\Form\\FormInterface::isValid` returns
   ``false`` and the form is rendered again, but now with validation errors;

#. When the user submits the form with valid data, the submitted data is again
   written into the form, but this time :method:`Symfony\\Component\\Form\\FormInterface::isValid`
   returns ``true``. Now you have the opportunity to perform some actions using
   the ``$task`` object (e.g. persisting it to the database) before redirecting
   the user to some other page (e.g. a "thank you" or "success" page).

   .. note::

      Redirecting a user after a successful form submission prevents the user
      from being able to hit the "Refresh" button of their browser and re-post
      the data.

.. seealso::

    If you need more control over exactly when your form is submitted or which
    data is passed to it, you can use the :method:`Symfony\\Component\\Form\\FormInterface::submit`
    method. Read more about it :ref:`form-call-submit-directly`.

.. index::
   single: Forms; Validation

.. _forms-form-validation:

Form Validation
---------------

In the previous section, you learned how a form can be submitted with valid
or invalid data. In Symfony, validation is applied to the underlying object
(e.g. ``Task``). In other words, the question isn't whether the "form" is
valid, but whether or not the ``$task`` object is valid after the form has
applied the submitted data to it. Calling ``$form->isValid()`` is a shortcut
that asks the ``$task`` object whether or not it has valid data.

Validation is done by adding a set of rules (called constraints) to a class. To
see this in action, add validation constraints so that the ``task`` field cannot
be empty and the ``dueDate`` field cannot be empty and must be a valid \DateTime
object.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Task.php
        namespace AppBundle\Entity;

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

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Task:
            properties:
                task:
                    - NotBlank: ~
                dueDate:
                    - NotBlank: ~
                    - Type: \DateTime

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8"?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Task">
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

        // src/AppBundle/Entity/Task.php
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
                    new Type(\DateTime::class)
                );
            }
        }

That's it! If you re-submit the form with invalid data, you'll see the
corresponding errors printed out with the form.

Validation is a very powerful feature of Symfony and has its own
:doc:`dedicated article </validation>`.

.. _forms-html5-validation-disable:

.. sidebar:: HTML5 Validation

   Thanks to HTML5, many browsers can natively enforce certain validation constraints
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

.. code-block:: html+twig

    {# app/Resources/views/default/new.html.twig #}
    {{ form(form, {'attr': {'novalidate': 'novalidate'}}) }}

   single: Forms; Built-in field types

.. _forms-type-reference:

Built-in Field Types
--------------------

Symfony comes standard with a large group of field types that cover all of
the common form fields and data types you'll encounter:

.. include:: /reference/forms/types/map.rst.inc

You can also create your own custom field types. See
:doc:`/form/create_custom_field_type` for info.

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

.. image:: /_images/form/simple-form-2.png
    :align: center

Each field type has a number of different options that can be passed to it.
Many of these are specific to the field type and details can be found in
the documentation for each type.

.. sidebar:: The ``required`` Option

    The most common option is the ``required`` option, which can be applied to
    any field. By default, the ``required`` option is set to ``true``, meaning
    that HTML5-ready browsers will apply client-side validation if the field
    is left blank. If you don't want this behavior, either
    :ref:`disable HTML5 validation <forms-html5-validation-disable>`
    or set the ``required`` option on your field to ``false``::

        ->add('dueDate', 'date', array(
            'widget' => 'single_text',
            'required' => false
        ))

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

.. _forms-field-guessing:

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

``required``
    The ``required`` option can be guessed based on the validation rules (i.e. is
    the field ``NotBlank`` or ``NotNull``) or the Doctrine metadata (i.e. is the
    field ``nullable``). This is very useful, as your client-side validation will
    automatically match your validation rules.

``maxlength``
    If the field is some sort of text field, then the ``maxlength`` option attribute
    can be guessed from the validation constraints (if ``Length`` or ``Range`` is used)
    or from the Doctrine metadata (via the field's length).

.. caution::

  These field options are *only* guessed if you're using Symfony to guess
  the field type (i.e. omit or pass ``null`` as the second argument to ``add()``).

If you'd like to change one of the guessed values, you can override it by
passing the option in the options field array::

    ->add('task', null, array('attr' => array('maxlength' => 4)))

.. index::
   single: Forms; Creating form classes

.. _form-creating-form-classes:

Creating Form Classes
---------------------

As you've seen, a form can be created and used directly in a controller.
However, a better practice is to build the form in a separate, standalone PHP
class, which can then be reused anywhere in your application. Create a new class
that will house the logic for building the task form::

    // src/AppBundle/Form/TaskType.php
    namespace AppBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('task')
                ->add('dueDate', null, array('widget' => 'single_text'))
                ->add('save', 'submit')
            ;
        }

        public function getName()
        {
            return 'app_task';
        }
    }

.. caution::

    The ``getName()`` method returns the identifier of this form "type". These
    identifiers must be unique in the application. Unless you want to override
    a built-in type, they should be different from the default Symfony types
    and from any type defined by a third-party bundle installed in your application.
    Consider prefixing your types with ``app_`` to avoid identifier collisions.

This new class contains all the directions needed to create the task form. It can
be used to quickly build a form object in the controller::

    // src/AppBundle/Controller/DefaultController.php

    // add this new use statement at the top of the class
    use AppBundle\Form\TaskType;

    public function newAction()
    {
        $task = ...;
        $form = $this->createForm(new TaskType(), $task);

        // ...
    }

Placing the form logic into its own class means that the form can be easily
reused elsewhere in your project. This is the best way to create forms, but
the choice is ultimately up to you.

.. _form-data-class:

.. sidebar:: Setting the ``data_class``

    Every form needs to know the name of the class that holds the underlying
    data (e.g. ``AppBundle\Entity\Task``). Usually, this is just guessed
    based off of the object passed to the second argument to ``createForm()``
    (i.e. ``$task``). Later, when you begin embedding forms, this will no
    longer be sufficient. So, while not always necessary, it's generally a
    good idea to explicitly specify the ``data_class`` option by adding the
    following to your form type class::

        use AppBundle\Entity\Task;
        use Symfony\Component\OptionsResolver\OptionsResolver;

        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => Task::class,
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
                ->add('dueDate')
                ->add('agreeTerms', 'checkbox', array('mapped' => false))
                ->add('save', 'submit')
            ;
        }

    Additionally, if there are any fields on the form that aren't included in
    the submitted data, those fields will be explicitly set to ``null``.

    The field data can be accessed in a controller with::

        $form->get('agreeTerms')->getData();

    In addition, the data of an unmapped field can also be modified directly::

        $form->get('agreeTerms')->setData(true);

Final Thoughts
--------------

When building forms, keep in mind that the first goal of a form is to translate data
from an object (``Task``) to an HTML form so that the user can modify that data.
The second goal of a form is to take the data submitted by the user and to re-apply
it to the object.

There's a lot more to learn and a lot of *powerful* tricks in the form system.

Learn more
----------

.. toctree::
    :hidden:

    form/use_virtuals_forms

.. toctree::
    :maxdepth: 1
    :glob:

    form/*

* :doc:`/controller/upload_file`
* :doc:`/reference/forms/types`
* :doc:`/http_cache/form_csrf_caching`

.. _`Symfony Form component`: https://github.com/symfony/form
.. _`DateTime`: http://php.net/manual/en/class.datetime.php
