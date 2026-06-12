Forms
=====

.. admonition:: Screencast
    :class: screencast

    Do you prefer video tutorials? Check out the `Symfony Forms screencast series`_.

Creating and processing HTML forms is hard and repetitive. You need to deal with
rendering HTML form fields, validating submitted data, mapping the form data
into objects and a lot more. Symfony includes a powerful form feature that
provides all these features and many more for truly complex scenarios.

Installation
------------

In applications using :ref:`Symfony Flex <symfony-flex>`, run this command to
install the form feature before using it:

.. code-block:: terminal

    $ composer require symfony/form

Understanding How Forms Work
----------------------------

Before diving into the code, it's helpful to understand the mental model behind
Symfony forms. Think of a form as a **bidirectional mapping layer** between
your PHP objects (or arrays) and HTML forms.

This mapping works in two directions:

#. **Object to HTML**: When rendering a form, Symfony reads data from your
   object and turns it into HTML fields that users can edit;

#. **HTML to Object**: When processing a submission, Symfony takes the raw
   values from the HTTP request (typically strings) and converts them back into
   the appropriate PHP types on your object.

This flow is the core of the Form component. From simple text fields to complex
nested collections, everything follows the same pattern.

.. _form-data-lifecycle:

The Data Transformation Lifecycle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Data in a form goes through three representations, often called **data layers**:

**Model Data**
    The data in the format your application uses. For example, a ``DateTime``
    object, a Doctrine entity, or a custom value object. This is what you pass
    to ``createForm()`` and what you get back after a successful submission via
    ``getData()``.

**Normalized Data**
    An intermediate representation that normalizes the model data. For most
    field types, this is identical to the model data. However, for some types
    it's different. For example, ``DateType`` is normalized as an array with
    ``year``, ``month``, and ``day`` keys.

**View Data**
    The format used to populate HTML form fields and received from user
    submissions. In most cases, this is string-based (or arrays of strings),
    because browsers submit text. Some fields may use other representations or
    remain empty for security reasons (for example, file inputs).

High-level flow:

**Form Rendering**

#. Start with model data from your object.
#. Model transformers convert it to normalized data.
#. View transformers convert it to view data (typically strings).
#. Symfony renders the corresponding HTML widgets.

**Form Submission**

#. Symfony reads raw values from the HTTP request (typically strings).
#. View transformers reverse the data into normalized data.
#. Model transformers reverse the data into model data.
#. The data is written back to the underlying object or array.

For a ``DateType`` field configured to render as three ``<select>`` elements:

* **Model data**: a ``DateTime`` object;
* **Norm data**: an array like ``['year' => 2026, 'month' => 10, 'day' => 18]``;
  (values are integers)
* **View data**: an array like ``['year' => '2026', 'month' => '10', 'day' => '18']``
  (values are strings, as submitted by the browser).

Most of the time you don't need to think about these layers. They become
relevant when debugging why a field doesn't display or submit correctly, or
when creating custom :doc:`data transformers </form/data_transformers>`.

Usage
-----

The recommended workflow when working with Symfony forms is the following:

#. **Build the form** in a Symfony controller or using a dedicated form class;
#. **Render the form** in a template so the user can edit and submit it;
#. **Process the form** to validate the submitted data, transform it into PHP
   data and do something with it (e.g. persist it in a database).

Each of these steps is explained in detail in the next sections. To make
examples easier to follow, all of them assume that you're building a small Todo
list application that displays "tasks".

Users create and edit tasks using Symfony forms. Each task is an instance of the
following ``Task`` class::

    // src/Entity/Task.php
    namespace App\Entity;

    class Task
    {
        protected string $task;

        protected ?\DateTimeInterface $dueDate;

        public function getTask(): string
        {
            return $this->task;
        }

        public function setTask(string $task): void
        {
            $this->task = $task;
        }

        public function getDueDate(): ?\DateTimeInterface
        {
            return $this->dueDate;
        }

        public function setDueDate(?\DateTimeInterface $dueDate): void
        {
            $this->dueDate = $dueDate;
        }
    }

This class is a "plain-old-PHP-object" because, so far, it has nothing to do
with Symfony or any other library. It's a normal PHP object that directly solves
a problem inside *your* application (i.e. the need to represent a task in your
application). But you can also edit :doc:`Doctrine entities </doctrine>` in the
same way.

.. _form-types:

Form Types
~~~~~~~~~~

Before creating your first Symfony form, it's important to understand the
concept of "form type". In other projects, it's common to differentiate between
"forms" and "form fields". In Symfony, all of them are "form types":

* a single ``<input type="text">`` form field is a "form type" (e.g. ``TextType``);
* a group of several HTML fields used to input a postal address is a "form type"
  (e.g. ``PostalAddressType``);
* an entire ``<form>`` with multiple fields to edit a user profile is a
  "form type" (e.g. ``UserProfileType``).

This unified concept makes the Form component more **flexible**. You can compose
complex forms from simpler types, embed forms within forms, and reuse the same
type definition across your application.

**The Form Type Hierarchy**

Every form type has a parent type. The parent determines the base behavior,
options, and rendering that your type inherits. Here's a simplified view:

.. code-block:: text

    FormType        (the root parent of all types)
    ├─ TextType    (renders a text input)
    │  ├─ EmailType
    │  ├─ PasswordType
    │  ├─ ...
    │  └─ UrlType
    ├─ ChoiceType  (renders select, radio, or checkboxes)
    │  ├─ CountryType
    │  ├─ EntityType
    │  └─ ...
    ├─ DateType    (renders single or multiple fields for date input)
    │  └─ ...
    └─ ...

When you create a custom form type and specify a parent (via ``getParent()``),
your type inherits options, template blocks, and behavior from that parent. This
is why ``EmailType`` reuses the rendering and options from ``TextType``.

There are tens of :doc:`form types provided by Symfony </reference/forms/types>`
and you can also :doc:`create your own form types </form/create_custom_field_type>`.

.. tip::

    You can use the ``debug:form`` to list all the available types, type
    extensions and type guessers in your application:

    .. code-block:: terminal

        $ php bin/console debug:form

        # pass the form type FQCN to only show the options for that type, its parents and extensions
        # For built-in types, you can pass the short classname instead of the FQCN
        $ php bin/console debug:form BirthdayType

        # pass also an option name to only display the full definition of that option
        $ php bin/console debug:form BirthdayType label_attr

Building Forms
--------------

Symfony provides a "form builder" object which allows you to describe the form
fields using a fluent interface. Later, this builder creates the actual form
object used to render and process contents.

.. _creating-forms-in-controllers:

Creating Forms in Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your controller extends from the :ref:`AbstractController <the-base-controller-class-services>`,
use the ``createFormBuilder()`` helper::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Entity\Task;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class TaskController extends AbstractController
    {
        public function new(Request $request): Response
        {
            // creates a task object and initializes some data for this example
            $task = new Task();
            $task->setTask('Write a blog post');
            $task->setDueDate(new \DateTimeImmutable('tomorrow'));

            $form = $this->createFormBuilder($task)
                ->add('task', TextType::class)
                ->add('dueDate', DateType::class)
                ->add('save', SubmitType::class, ['label' => 'Create Task'])
                ->getForm();

            // ...
        }
    }

If your controller does not extend from ``AbstractController``, you'll need to
:ref:`fetch services in your controller <controller-accessing-services>` and
use the ``createBuilder()`` method of the ``form.factory`` service.

In this example, you've added two fields to your form - ``task`` and ``dueDate``
- corresponding to the ``task`` and ``dueDate`` properties of the ``Task``
class. You've also assigned each a :ref:`form type <form-types>` (e.g. ``TextType``
and ``DateType``), represented by its fully qualified class name. Finally, you
added a submit button with a custom label for submitting the form to the server.

.. _creating-forms-in-classes:

Creating Form Classes
~~~~~~~~~~~~~~~~~~~~~

Symfony recommends putting as little logic as possible in controllers. That's why
it's better to move complex forms to dedicated classes instead of defining them
in controller actions. Besides, forms defined in classes can be reused in
multiple actions and services.

Form classes are :ref:`form types <form-types>` that implement
:class:`Symfony\\Component\\Form\\FormTypeInterface`. However, it's better to
extend from :class:`Symfony\\Component\\Form\\AbstractType`, which already
implements the interface and provides some utilities::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('task', TextType::class)
                ->add('dueDate', DateType::class)
                ->add('save', SubmitType::class)
            ;
        }
    }

.. tip::

    Install the `MakerBundle`_ in your project to generate form classes using
    the ``make:form`` and ``make:registration-form`` commands.

The form class contains all the directions needed to create the task form. In
controllers extending from the :ref:`AbstractController <the-base-controller-class-services>`,
use the ``createForm()`` helper (otherwise, use the ``create()`` method of the
``form.factory`` service)::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Form\Type\TaskType;
    // ...

    class TaskController extends AbstractController
    {
        public function new(): Response
        {
            // creates a task object and initializes some data for this example
            $task = new Task();
            $task->setTask('Write a blog post');
            $task->setDueDate(new \DateTimeImmutable('tomorrow'));

            $form = $this->createForm(TaskType::class, $task);

            // ...
        }
    }

.. _form-data-class:

Every form needs to know the name of the class that holds the underlying data
(e.g. ``App\Entity\Task``). Usually, this is guessed based on the object passed
to the second argument to ``createForm()`` (i.e. ``$task``). Later, when you
begin :doc:`embedding forms </form/embedded>`, this will no longer be sufficient.

So, while not always necessary, it's generally a good idea to explicitly specify
the ``data_class`` option by adding the following to your form type class::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use App\Entity\Task;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    // ...

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Task::class,
            ]);
        }
    }

.. _form-property-path:

Mapping Fields to Object Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, a form field named ``dueDate`` reads and writes the ``dueDate``
property on your object. This uses the :doc:`PropertyAccess component </components/property_access>`,
which can work with public properties and common accessor names (``get*()``,
``is*()``, ``has*()``, ``set*()``).

The ``property_path`` option lets you customize this mapping.

**Mapping to a Different Property**

If your form field name doesn't match the object property::

    $builder->add('deadline', DateType::class, [
        // this field will read/write the 'dueDate' property
        'property_path' => 'dueDate',
    ]);

**Mapping to Nested Properties**

You can access nested object properties using dot notation::

    // assuming Task::getCategory() returns a Category object with getName()/setName()
    $builder->add('categoryName', TextType::class, [
        'property_path' => 'category.name',
    ]);

For fields that shouldn't be written back to the underlying data, use
:ref:`unmapped fields <form-unmapped-fields>`.

.. _form-injecting-services:

Injecting Services in Form Classes
..................................

Form classes are regular services, which means you can inject other services
using :doc:`autowiring </service_container/autowiring>`::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use App\Repository\CategoryRepository;
    use Symfony\Component\Form\AbstractType;
    // ...

    class TaskType extends AbstractType
    {
        public function __construct(
            private CategoryRepository $categoryRepository,
        ) {
        }

        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            // use $this->categoryRepository to access the repository
        }
    }

If you're using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
this works automatically. See :doc:`/form/create_custom_field_type` for more
information about injecting services in custom form types.

.. _rendering-forms:

Rendering Forms
---------------

Now that the form has been created, the next step is to render it::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Entity\Task;
    use App\Form\Type\TaskType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    class TaskController extends AbstractController
    {
        public function new(Request $request): Response
        {
            $task = new Task();
            // ...

            $form = $this->createForm(TaskType::class, $task);

            return $this->render('task/new.html.twig', [
                'form' => $form,
            ]);
        }
    }

Internally, the ``render()`` method calls ``$form->createView()`` to
transform the form into a *form view* instance.

Then, use some :ref:`form helper functions <reference-form-twig-functions>` to
render the form contents:

.. code-block:: twig

    {# templates/task/new.html.twig #}
    {{ form(form) }}

That's it! The :ref:`form() function <reference-forms-twig-form>` renders all
fields *and* the ``<form>`` start and end tags. By default, the form method is
``POST`` and the target URL is the same that displayed the form, but
:ref:`you can change both <forms-change-action-method>`.

Notice how the rendered ``task`` input field has the value of the ``task``
property from the ``$task`` object (i.e. "Write a blog post"). This is the first
job of a form: to take data from an object and translate it into a format that's
suitable for being rendered in an HTML form.

.. tip::

    The form system is smart enough to access the value of the protected
    ``task`` property via the ``getTask()`` and ``setTask()`` methods on the
    ``Task`` class. Unless a property is public, it *must* have a "getter" and
    "setter" method so that Symfony can get and put data onto the property. For
    a boolean property, you can use an "isser" or "hasser" method (e.g.
    ``isPublished()`` or ``hasReminder()``) instead of a getter (e.g.
    ``getPublished()`` or ``getReminder()``).

As short as this rendering is, it's not very flexible. Usually, you'll need more
control about how the entire form or some of its fields look. For example, thanks
to the :doc:`Bootstrap 5 integration with Symfony forms </form/bootstrap5>` you
can set this option to generate forms compatible with the Bootstrap 5 CSS framework:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            form_themes: ['bootstrap_5_layout.html.twig']

    .. code-block:: php

        // config/packages/twig.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'twig' => [
                'form_themes' => ['bootstrap_5_layout.html.twig'],
            ],
        ]);

The :ref:`built-in Symfony form themes <symfony-builtin-forms>` include
Bootstrap 3, 4 and 5, Foundation 5 and 6, as well as Tailwind 2. You can also
:ref:`create your own Symfony form theme <create-your-own-form-theme>`.

In addition to form themes, Symfony allows you to
:doc:`customize the way fields are rendered </form/form_customization>` with
multiple functions to render each field part separately (widgets, labels,
errors, help messages, etc.)

.. _processing-forms:

Processing Forms
----------------

The :ref:`recommended way of processing forms <best-practice-handle-form>` is to
use a single action for both rendering the form and handling the form submit.
You can use separate actions, but using one action simplifies everything while
keeping the code concise and maintainable.

Processing a form means to translate user-submitted data back to the properties
of an object. To make this happen, the submitted data from the user must be
written into the form object::

    // src/Controller/TaskController.php

    // ...
    use Symfony\Component\HttpFoundation\Request;

    class TaskController extends AbstractController
    {
        public function new(Request $request): Response
        {
            // set up a fresh $task object (remove the example data)
            $task = new Task();

            $form = $this->createForm(TaskType::class, $task);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                // but, the original `$task` variable has also been updated
                $task = $form->getData();

                // ... perform some action, such as saving the task to the database

                return $this->redirectToRoute('task_success');
            }

            return $this->render('task/new.html.twig', [
                'form' => $form,
            ]);
        }
    }

This controller follows a common pattern for handling forms and has three
possible paths:

#. When initially loading the page in a browser, the form hasn't been submitted
   yet and ``$form->isSubmitted()`` returns ``false``. So, the form is created
   and rendered;

#. When the user submits the form, :method:`Symfony\\Component\\Form\\FormInterface::handleRequest`
   recognizes this and immediately writes the submitted data back into the
   ``task`` and ``dueDate`` properties of the ``$task`` object. Then this object
   is validated (validation is explained in the next section). If it is invalid,
   :method:`Symfony\\Component\\Form\\FormInterface::isValid` returns
   ``false`` and the form is rendered again, but now with validation errors.

   By passing ``$form`` to the ``render()`` method (instead of
   ``$form->createView()``), the response code is automatically set to
   `HTTP 422 Unprocessable Content`_. This ensures compatibility with tools
   relying on the HTTP specification, like `Symfony UX Turbo`_;

#. When the user submits the form with valid data, the submitted data is again
   written into the form, but this time :method:`Symfony\\Component\\Form\\FormInterface::isValid`
   returns ``true``. Now you have the opportunity to perform some actions using
   the ``$task`` object (e.g. persisting it to the database) before redirecting
   the user to some other page (e.g. a "thank you" or "success" page);

.. note::

    Redirecting a user after a successful form submission is a best practice
    that prevents the user from being able to hit the "Refresh" button of
    their browser and re-post the data.

Accessing Form Data
~~~~~~~~~~~~~~~~~~~

You'll use the ``getData()`` method most often to access the form's data,
but Symfony forms also provide methods to access data at :ref:`each layer <form-data-lifecycle>`:

``getData()``
    Returns the **model data**. This is the method you'll use most often.
    After submission, it returns the populated object (or array) with all the
    submitted values transformed into their proper PHP types.

``getNormData()``
    Returns the **normalized data**. Useful when debugging transformer issues
    or when you need the intermediate representation.

``getViewData()``
    Returns the **view data**. This is what gets rendered into HTML fields and
    what comes back from user submissions (before transformation).

.. seealso::

    When adding :ref:`extra fields <form-extra-fields>` to the form, you can also
    use the ``getExtraData()`` method to get any submitted data that doesn't
    correspond to a form field.

Example showing these methods in action::

    // after form submission
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // the populated Task object
        $task = $form->getData();

        // for a DateType field, this might be ['year' => 2024, 'month' => 6, ...]
        $normData = $form->get('dueDate')->getNormData();

        // the raw submitted values (usually strings): ['year' => '2024', 'month' => '6', ...]
        $viewData = $form->get('dueDate')->getViewData();
    }

If a transformer fails, the form (or the affected field) may be marked as not
synchronized. Check ``isSynchronized()`` and inspect field errors to understand
what went wrong.

.. _processing-forms-submit-method:

Using the submit() Method
~~~~~~~~~~~~~~~~~~~~~~~~~

The :method:`Symfony\\Component\\Form\\FormInterface::handleRequest` method is
the recommended way to process forms. However, you can also use the
:method:`Symfony\\Component\\Form\\FormInterface::submit` method for finer
control over when exactly your form is submitted and what data is passed to it::

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        if ($request->isMethod('POST')) {
            $form->submit($request->getPayload()->get($form->getName()));

            if ($form->isSubmitted() && $form->isValid()) {
                // perform some action...

                return $this->redirectToRoute('task_success');
            }
        }

        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }

The list of fields submitted with the ``submit()`` method must be the same as
the fields defined by the form class. Otherwise, you'll see a form validation error::

    public function new(Request $request): Response
    {
        // ...

        if ($request->isMethod('POST')) {
            // '$json' represents payload data sent by React/Angular/Vue
            // the merge of parameters is needed to submit all form fields
            $form->submit(array_merge($json, $request->getPayload()->all()));

            // ...
        }

        // ...
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

.. warning::

    When the second parameter ``$clearMissing`` is ``false``, like with the
    "PATCH" method, the validation will only apply to the submitted fields. If
    you need to validate all the underlying data, add the required fields
    manually so that they are validated::

        // 'email' and 'username' are added manually to force their validation
        $form->submit(array_merge(
            ['email' => null, 'username' => null],
            $request->getPayload()->all()
        ), false);

.. _processing-forms-multiple-buttons:

Handling Multiple Submit Buttons
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When your form contains more than one submit button, you'll want to check
which of the buttons was clicked to adapt the program flow in your controller.
For example, if you add a second button with the caption "Save and Add" to your form::

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

Alternatively you can use the :method:`Symfony\\Component\\Form\\Form::getClickedButton`
method to get the clicked button's name::

    if ($form->getClickedButton() && 'saveAndAdd' === $form->getClickedButton()->getName()) {
        // ...
    }

    // when using nested forms, two or more buttons can have the same name;
    // in those cases, compare the button objects instead of the button names
    if ($form->getClickedButton() === $form->get('saveAndAdd')) {
        // ...
    }

.. _validating-forms:

Validating Forms
----------------

In the previous section, you learned how a form can be submitted with valid
or invalid data. In Symfony, the question isn't whether the "form" is valid, but
whether or not the underlying object (``$task`` in this example) is valid after
the form has applied the submitted data to it. Calling ``$form->isValid()`` is a
shortcut that asks the ``$task`` object whether or not it has valid data.

Before using validation, add support for it in your application:

.. code-block:: terminal

    $ composer require symfony/validator

Validation is done by adding a set of rules, called (validation) constraints,
to a class. You can add them either to the entity class or by using the
:ref:`constraints option <reference-form-option-constraints>` of form types.

To see the first approach - adding constraints to the entity - in action,
add the validation constraints, so that the ``task`` field cannot be empty,
and the ``dueDate`` field cannot be empty, and must be a valid ``DateTimeImmutable``
object.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Task.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Task
        {
            #[Assert\NotBlank]
            public string $task;

            #[Assert\NotBlank]
            #[Assert\Type(\DateTimeInterface::class)]
            protected \DateTimeInterface $dueDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Task:
            properties:
                task:
                    - NotBlank: ~
                dueDate:
                    - NotBlank: ~
                    - Type:
                        type: \DateTimeInterface

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Task">
                <property name="task">
                    <constraint name="NotBlank"/>
                </property>
                <property name="dueDate">
                    <constraint name="NotBlank"/>
                    <constraint name="Type">
                        <option name="type">\DateTimeInterface</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Task.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Task
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('task', new Assert\NotBlank());

                $metadata->addPropertyConstraint('dueDate', new Assert\NotBlank());
                $metadata->addPropertyConstraint(
                    'dueDate',
                    new Assert\Type(\DateTimeInterface::class)
                );
            }
        }

That's it! If you re-submit the form with invalid data, you'll see the
corresponding errors printed out with the form.

To see the second approach - adding constraints to the form - refer to
:ref:`this section <form-option-constraints>`. Both approaches can be used together.

.. _form-disabling-validation:

Disabling Validation
~~~~~~~~~~~~~~~~~~~~

Sometimes it's useful to suppress the validation of a form altogether. For
these cases, set the ``validation_groups`` option to ``false``::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }

Note that when you do that, the form will still run basic integrity checks,
for example whether an uploaded file was too large or whether non-existing
fields were submitted.

The submission of extra form fields can be controlled with the
:ref:`allow_extra_fields config option <form-option-allow-extra-fields>` and
the maximum upload file size should be handled via your PHP and web server
configuration.

You can also disable validation for specific submit buttons using
``'validation_groups' => false``. This is useful in multi-step forms when you
want a "Previous" button to save data without running validation::

    $form = $this->createFormBuilder($task)
        // ...
        ->add('nextStep', SubmitType::class)
        ->add('previousStep', SubmitType::class, [
            'validation_groups' => false,
        ])
        ->getForm();

The form will still validate basic integrity constraints even when clicking
"previousStep".

Other Common Form Features
--------------------------

Passing Options to Forms
~~~~~~~~~~~~~~~~~~~~~~~~

If you :ref:`create forms in classes <creating-forms-in-classes>`, when building
the form in the controller you can pass custom options to it as the third optional
argument of ``createForm()``::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Form\Type\TaskType;
    // ...

    class TaskController extends AbstractController
    {
        public function new(): Response
        {
            $task = new Task();
            // use some PHP logic to decide if this form field is required or not
            $dueDateIsRequired = ...;

            $form = $this->createForm(TaskType::class, $task, [
                'require_due_date' => $dueDateIsRequired,
            ]);

            // ...
        }
    }

If you try to use the form now, you'll see an error message: *The option
"require_due_date" does not exist.* That's because forms must declare all the
options they accept using the ``configureOptions()`` method::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use Symfony\Component\OptionsResolver\OptionsResolver;
    // ...

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                // ...,
                'require_due_date' => false,
            ]);

            // you can also define the allowed types, allowed values and
            // any other feature supported by the OptionsResolver component
            $resolver->setAllowedTypes('require_due_date', 'bool');
        }
    }

Now you can use this new form option inside the ``buildForm()`` method::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // ...
                ->add('dueDate', DateType::class, [
                    'required' => $options['require_due_date'],
                ])
            ;
        }

        // ...
    }

Form Type Options
~~~~~~~~~~~~~~~~~

Each :ref:`form type <form-types>` has a number of options to configure it, as
explained in the :doc:`Symfony form types reference </reference/forms/types>`.
Two commonly used options are ``required`` and ``label``.

The ``required`` Option
.......................

The most common option is the ``required`` option, which can be applied to any
field. By default, this option is set to ``true``, meaning that HTML5-ready
browsers will require you to fill in all fields before submitting the form.

If you don't want this behavior, either
:ref:`disable client-side validation <forms-html5-validation-disable>` for the
entire form or set the ``required`` option to ``false`` on one or more fields::

    ->add('dueDate', DateType::class, [
        'required' => false,
    ])

The ``required`` option does not perform any server-side validation. If a user
submits a blank value for the field (either with an old browser or a web
service, for example), it will be accepted as a valid value unless you also use
Symfony's ``NotBlank`` or ``NotNull`` validation constraints.

The ``label`` Option
....................

By default, the label of form fields are the *humanized* version of the
property name (``user`` -> ``User``; ``postalAddress`` -> ``Postal Address``).
Set the ``label`` option on fields to define their labels explicitly::

    ->add('dueDate', DateType::class, [
        // set it to FALSE to not display the label for this field
        'label' => 'To Be Completed Before',
    ])

.. tip::

    By default, ``<label>`` tags of required fields are rendered with a
    ``required`` CSS class, so you can display an asterisk by applying a CSS style:

    .. code-block:: css

        label.required:before {
            content: "*";
        }

.. _forms-change-action-method:

Changing the Action and HTTP Method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, the ``<form>`` tag is rendered with a ``method="post"`` attribute,
and no ``action`` attribute. This means that the form is submitted via an HTTP
POST request to the same URL under which it was rendered. When building the form,
use the ``setAction()`` and ``setMethod()`` methods to change this::

    // src/Controller/TaskController.php
    namespace App\Controller;

    // ...
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class TaskController extends AbstractController
    {
        public function new(): Response
        {
            // ...

            $form = $this->createFormBuilder($task)
                ->setAction($this->generateUrl('target_route'))
                ->setMethod('GET')
                // ...
                ->getForm();

            // ...
        }
    }

When building the form in a class, pass the action and method as form options::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Form\TaskType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    // ...

    class TaskController extends AbstractController
    {
        public function new(): Response
        {
            // ...

            $form = $this->createForm(TaskType::class, $task, [
                'action' => $this->generateUrl('target_route'),
                'method' => 'GET',
            ]);

            // ...
        }
    }

Finally, you can override the action and method in the template by passing them
to the ``form()`` or the ``form_start()`` helper functions:

.. code-block:: twig

    {# templates/task/new.html.twig #}
    {{ form_start(form, {'action': path('target_route'), 'method': 'GET'}) }}

.. note::

    If the form's method is not ``GET`` or ``POST``, but ``PUT``, ``PATCH`` or
    ``DELETE``, Symfony will insert a hidden field with the name ``_method``
    that stores this method. The form will be submitted in a normal ``POST``
    request, but :doc:`Symfony's routing </routing>` is capable of detecting the
    ``_method`` parameter and will interpret it as a ``PUT``, ``PATCH`` or
    ``DELETE`` request. The :ref:`http_method_override <configuration-framework-http_method_override>`
    option must be enabled for this to work.

    For security, you can restrict which HTTP methods can be overridden using the
    :ref:`allowed_http_method_override <configuration-framework-allowed_http_method_override>`
    option.

.. _changing-the-form-name:

Changing the Form Field Names and Ids
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When Symfony renders a form, it generates HTML ``name`` and ``id`` attributes
for each field following specific conventions. Understanding these conventions
helps when writing JavaScript, CSS selectors, or custom form themes.

In Twig templates, prefer ``form.vars.full_name`` and ``form.vars.id`` as the
source of truth, instead of reconstructing names manually.

**The ``name`` Attribute**

Field names follow the pattern: ``formName[fieldName]``. For nested forms, names
are further nested: ``formName[childForm][fieldName]``.

Given a ``TaskType`` form with a ``dueDate`` field::

    $form = $this->createForm(TaskType::class, $task);

The rendered HTML will have:

.. code-block:: html

    <input name="task[dueDate]" ...>

For a ``DateType`` field that renders as three separate ``<select>`` elements:

.. code-block:: html

    <select name="task[dueDate][month]">...</select>
    <select name="task[dueDate][day]">...</select>
    <select name="task[dueDate][year]">...</select>

**The ``id`` Attribute**

The ``id`` attribute follows a similar pattern but uses underscores instead of
brackets: ``formName_fieldName``. For the examples above:

.. code-block:: html

    <input id="task_dueDate" ...>

    <!-- or for DateType with multiple fields: -->
    <select id="task_dueDate_month">...</select>
    <select id="task_dueDate_day">...</select>
    <select id="task_dueDate_year">...</select>

**Customizing the Form Name**

The default form name is derived from the form type class (for example,
``TaskType`` becomes ``task`` and ``FooBarType`` becomes ``foo_bar``). You can
customize this by returning a different value from the ``getBlockPrefix()`` method
of your form type class.

You can also customize this by creating the form with the
:method:`Symfony\\Component\\Form\\FormFactoryInterface::createNamed` method::

    // using FormFactory
    $form = $formFactory->createNamed('my_task', TaskType::class, $task);

    // this generates: <input name="my_task[dueDate]" id="my_task_dueDate">

To create a form without any name prefix (fields named directly like ``dueDate``
instead of ``task[dueDate]``)::

    $form = $formFactory->createNamed('', TaskType::class, $task);

.. _forms-html5-validation-disable:

Client-Side HTML Validation
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Thanks to HTML5, many browsers can natively enforce certain validation
constraints on the client side. The most common validation is activated by
adding a ``required`` attribute on fields that are required. For browsers
that support HTML5, this will result in a native browser message being displayed
if the user tries to submit the form with that field blank.

Generated forms take full advantage of this new feature by adding sensible HTML
attributes that trigger the validation. The client-side validation, however, can
be disabled by adding the ``novalidate`` attribute to the ``<form>`` tag or
``formnovalidate`` to the submit tag. This is especially useful when you want to
test your server-side validation constraints, but are being prevented by your
browser from, for example, submitting blank fields.

.. code-block:: twig

    {# templates/task/new.html.twig #}
    {{ form_start(form, {'attr': {'novalidate': 'novalidate'}}) }}
        {{ form_widget(form) }}
    {{ form_end(form) }}

HTML Validation for date/time inputs
....................................

When using ``DateType`` or ``DateTimeType``, you can rely on ``attr`` option to
enable client-side validation::

    $builder->add('dueDate', DateTimeType::class, [
        'years' => range(2020, 2030),
        'attr' => ['min' => '2020-01-01T00:00', 'max' => '2030-12-31T23:59']
    ]);

.. _form-type-guessing:

Form Type Guessing
~~~~~~~~~~~~~~~~~~

If the object handled by the form includes validation constraints, Symfony can
introspect that metadata to guess the type of your field.
In the above example, Symfony can guess from the validation rules that the
``task`` field is a normal ``TextType`` field and the ``dueDate`` field is a
``DateType`` field.

To enable Symfony's "guessing mechanism", omit the second argument to the ``add()`` method, or
pass ``null`` to it::

    // src/Form/Type/TaskType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\DateType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // if you don't define field options, you can omit the second argument
                ->add('task')
                // if you define field options, pass NULL as second argument
                ->add('dueDate', null, ['required' => false])
                ->add('save', SubmitType::class)
            ;
        }
    }

.. warning::

    When using a specific :doc:`form validation group </form/validation_groups>`,
    the field type guesser will still consider *all* validation constraints when
    guessing your field types (including constraints that are not part of the
    validation group(s) being used).

Form Type Options Guessing
..........................

When the guessing mechanism is enabled for some field, in addition to its form type,
the following options will be guessed too:

``required``
    The ``required`` option is guessed based on the validation rules (i.e. is
    the field ``NotBlank`` or ``NotNull``) or the Doctrine metadata (i.e. is the
    field ``nullable``). This is very useful, as your client-side validation will
    automatically match your validation rules.

``maxlength``
    If the field is some sort of text field, then the ``maxlength`` option attribute
    is guessed from the validation constraints (if ``Length`` or ``Range`` is used)
    or from the :doc:`Doctrine </doctrine>` metadata (via the field's length).

If you'd like to change one of the guessed values, override it in the options field array::

    ->add('task', null, ['attr' => ['maxlength' => 4]])

.. seealso::

    Besides guessing the form type, Symfony also guesses :ref:`validation constraints <validating-forms>`
    if you're using a Doctrine entity. Read :ref:`automatic_object_validation`
    guide for more information.

.. _form-unmapped-fields:

Unmapped Fields
~~~~~~~~~~~~~~~

When editing an object via a form, all form fields are considered properties of
the object. Any fields on the form that do not exist on the object will cause an
exception to be thrown.

If you need extra fields in the form that won't be stored in the object (for
example to add an *"I agree with these terms"* checkbox), set the ``mapped``
option to ``false`` in those fields::

    // ...
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\FormBuilderInterface;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('task')
                ->add('dueDate')
                ->add('agreeTerms', CheckboxType::class, ['mapped' => false])
                ->add('save', SubmitType::class)
            ;
        }
    }

These "unmapped fields" can be set and accessed in a controller with::

    $form->get('agreeTerms')->getData();
    $form->get('agreeTerms')->setData(true);

Additionally, if there are any fields on the form that aren't included in
the submitted data, those fields will be explicitly set to ``null``.

.. _form-extra-fields:

Extra fields
~~~~~~~~~~~~

By default, Symfony expects every submitted field to be defined in the form. Any
additional submitted fields are treated as "extra fields". You can access them via the
:method:`FormInterface::getExtraData() <Symfony\\Component\\Form\\FormInterface::getExtraData>` method.

For example, consider a user creation form::

    // ...
    use App\User;
    use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
    use Symfony\Component\Form\Extension\Core\Type\SubmitType;
    use Symfony\Component\Form\FormBuilderInterface;

    class UserCreateType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('username', TextType::class)
                ->add('email', EmailType::class)
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => User::class,
            ]);
        }
    }

You can render an additional input in the template without adding it to the form
definition:

.. code-block:: html+twig

    {# templates/user/create.html.twig #}
    {{ form_start(form) }}
        {{ form_row(form.username) }}
        {{ form_row(form.email) }}

        {# hidden field to send an additional referral code #}
        <input type="hidden" name="{{ form.vars.full_name ~ '[referralCode]' }}" value="{{ referralCode }}"/>

        <button type="submit">Submit</button>
    {{ form_end(form) }}

In this example, ``referralCode`` is submitted as an extra field and you can
read it like this::

    $extraData = $form->getExtraData();
    $referralCode = $extraData['referralCode'] ?? null;

.. note::

    To accept extra fields, set the :ref:`allow_extra_fields <form-option-allow-extra-fields>`
    option to ``true``. Otherwise, the form will be invalid.

.. _forms-without-class:

Using a Form without a Data Class
---------------------------------

In most applications, a form is tied to an object, and the fields of the form get
and store their data on the properties of that object. This is what you've seen
so far in this article with the ``Task`` class.

However, by default, a form actually assumes that you want to work with arrays
of data, instead of an object. There are exactly two ways that you can change
this behavior and tie the form to an object instead:

#. Pass an object when creating the form (as the first argument to ``createFormBuilder()``
   or the second argument to ``createForm()``);

#. Declare the ``data_class`` option on your form.

If you *don't* do either of these, then the form will return the data as an array.
In this example, since ``$defaultData`` is not an object (and no ``data_class``
option is set), ``$form->getData()`` ultimately returns an array::

    // src/Controller/ContactController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    class ContactController extends AbstractController
    {
        public function contact(Request $request): Response
        {
            $defaultData = ['message' => 'Type your message here'];
            $form = $this->createFormBuilder($defaultData)
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
                ->add('message', TextareaType::class)
                ->add('send', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // data is an array with "name", "email", and "message" keys
                $data = $form->getData();
            }

            // ... render the form
        }
    }

.. tip::

    You can also access POST values (in this case "name") directly through
    the request object, like so::

        $request->getPayload()->get('name');

    Be advised, however, that in most cases using the ``getData()`` method is
    a better choice, since it returns the data (usually an object) after
    it's been transformed by the Form component.

.. _form-without-class-validation:

Adding Validation
~~~~~~~~~~~~~~~~~

Usually, when you call ``$form->handleRequest($request)``, the object is validated
by reading the constraints that you applied to that class. If your form is mapped
to an object, this is almost always the approach you want to use. See
:doc:`/validation` for more details.

.. _form-option-constraints:

But if the form is not mapped to an object and you instead want to retrieve an
array of your submitted data, there are two ways to add constraints to the form data.

Constraints At Field Level
..........................

You can attach constraints to the individual fields. The overall approach is
covered a bit more in :doc:`this validation article </validation/raw_values>`,
but here's a short example::

    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Validator\Constraints as Assert;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => new Assert\Length(min: 3),
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3),
                ],
            ])
        ;
    }

.. tip::

    If you are using validation groups, you need to either reference the
    ``Default`` group when creating the form, or set the correct group on
    the constraint you are adding::

        new NotBlank(groups: ['create', 'update']);

.. tip::

    If the form is not mapped to an object, every object in your array of
    submitted data is validated using the ``Symfony\Component\Validator\Constraints\Valid``
    constraint, unless you :ref:`disable validation <disabling-validation>`.

.. warning::

    When a form is only partially submitted (for example, in an HTTP PATCH
    request), only the constraints from the submitted form fields will be
    evaluated.

Constraints At Class Level
..........................

You can also add the constraints at the class level. This can be done by setting
the ``constraints`` option in the ``configureOptions()`` method::

    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints as Assert;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'constraints' => new Assert\Collection(
                firstName: new Assert\Length(min: 3),
                lastName: [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3),
                ],
            ),
        ]);
    }

This means you can also do this when using the ``createFormBuilder()`` method
in your controller::

    use Symfony\Component\Validator\Constraints as Assert;

    $form = $this->createFormBuilder($defaultData, [
            'constraints' => [
                'firstName' => new Assert\Length(min: 3),
                'lastName' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 3),
                ],
            ],
        ])
        ->add('firstName', TextType::class)
        ->add('lastName', TextType::class)
        ->getForm();

Conditional Constraints
.......................

It's possible to define field constraints that depend on the value of other
fields (e.g. a field must not be blank when another field has a certain value).
To achieve this, use the ``expression`` option of the
:doc:`When constraint </reference/constraints/When>` to reference the other field::

    use Symfony\Component\Validator\Constraints as Assert;

    $builder
        ->add('how_did_you_hear', ChoiceType::class, [
            'required' => true,
            'label' => 'How did you hear about us?',
            'choices' => [
                'Search engine' => 'search_engine',
                'Friends' => 'friends',
                'Other' => 'other',
            ],
            'expanded' => true,
            'constraints' => [
                new Assert\NotBlank(),
            ]
        ])

        // this field is only required when 'how_did_you_hear' is 'other'
        ->add('other_text', TextType::class, [
            'required' => false,
            'label' => 'Please specify',
            'constraints' => [
                new Assert\When(
                    expression: 'this.getParent().get("how_did_you_hear").getData() == "other"',
                    constraints: [
                        new Assert\NotBlank(),
                    ],
                )
            ],
        ])
    ;

.. seealso::

    Another way to implement conditional constraints is to configure the
    ``validation_groups`` form option with a callable. See
    :doc:`/form/validation_groups`.

Troubleshooting
---------------

Why Doesn't My Field Value Display?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Problem**: The form renders, but a field is empty even though the underlying
data has a value.

**Common causes**:

#. The property is not readable (missing accessor, wrong name, or not public).
   For booleans, Symfony also looks for ``is*()`` and ``has*()`` accessors.

#. The field name doesn't match the property name. Use :ref:`property_path <form-property-path>`
   if you need to map to a different property.

#. The data is set after creating the form. Populate your object *before*
   passing it to ``createForm()``::

       // wrong: object populated after form creation
       $form = $this->createForm(TaskType::class, $task);
       $task->setTask('My task');

       // correct: object populated before form creation
       $task->setTask('My task');
       $form = $this->createForm(TaskType::class, $task);

Why Doesn't My Submitted Data Save to the Object?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Problem**: The form submits, but the object properties remain unchanged.

**Common causes**:

#. The property is not writable (missing setter, wrong name, or not public).

#. It is an :ref:`unmapped field <form-unmapped-fields>`.

#. The form is not synchronized due to a transformation failure. Check
   ``isSynchronized()`` and inspect field errors.

Why Does ``getData()`` Return ``null`` After Submission?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Problem**: ``$form->getData()`` is ``null`` after handling the request.

**Common causes**:

#. No initial object (or default data) was provided and the form doesn't create
   one. Review the form's ``data_class`` and ``empty_data`` options.

#. A transformation failed and the form is not synchronized. Check
   ``isSynchronized()`` and field errors.

#. The form is not submitted or is invalid. Check ``isSubmitted()`` and
   ``isValid()`` before using the data.

Learn more
----------

When building forms, remember that the first goal of a form is to translate
data from an object (``Task``) to an HTML form so that the user can modify that
data. The second goal of a form is to take the data submitted by the user and to
re-apply it to the object.

There's a lot more to learn and a lot of *powerful* tricks in the Symfony forms:

Reference:

.. toctree::
    :maxdepth: 1

    /reference/forms/types

Advanced Features:

.. toctree::
    :maxdepth: 1

    /controller/upload_file
    /security/csrf
    /form/create_custom_field_type
    /form/data_transformers
    /form/data_mappers
    /form/create_form_type_extension
    /form/type_guesser

Form Themes and Customization:

.. toctree::
    :maxdepth: 1

    /form/bootstrap4
    /form/bootstrap5
    /form/tailwindcss
    /form/form_customization
    /form/form_themes

Events:

.. toctree::
    :maxdepth: 1

    /form/events
    /form/dynamic_form_modification

Validation:

.. toctree::
    :maxdepth: 1

    /form/validation_groups

Misc.:

.. toctree::
    :maxdepth: 1

    /form/embedded
    /form/form_collections
    /form/form_flow
    /form/inherit_data_option
    /form/unit_testing
    /form/use_empty_data

.. _`Symfony Forms screencast series`: https://symfonycasts.com/screencast/symfony-forms
.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
.. _`HTTP 422 Unprocessable Content`: https://www.rfc-editor.org/rfc/rfc9110.html#name-422-unprocessable-content
.. _`Symfony UX Turbo`: https://ux.symfony.com/turbo
