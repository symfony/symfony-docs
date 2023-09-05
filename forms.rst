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
        protected $task;
        protected $dueDate;

        public function getTask(): string
        {
            return $this->task;
        }

        public function setTask(string $task): void
        {
            $this->task = $task;
        }

        public function getDueDate(): ?\DateTime
        {
            return $this->dueDate;
        }

        public function setDueDate(?\DateTime $dueDate): void
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

This may be confusing at first, but it will feel natural to you soon enough.
Besides, it simplifies code and makes "composing" and "embedding" form fields
much easier to implement.

There are tens of :doc:`form types provided by Symfony </reference/forms/types>`
and you can also :doc:`create your own form types </form/create_custom_field_type>`.

.. tip::

    You can use the ``debug:form`` to list all the available types, type
    extensions and type guessers in your application:

    .. code-block:: terminal

        $ php bin/console debug:form

        # pass the form type FQCN to only show the options for that type, its parents and extensions.
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
            $task->setDueDate(new \DateTime('tomorrow'));

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
            $task->setDueDate(new \DateTime('tomorrow'));

            $form = $this->createForm(TaskType::class, $task);

            // ...
        }
    }

.. _form-data-class:

Every form needs to know the name of the class that holds the underlying data
(e.g. ``App\Entity\Task``). Usually, this is just guessed based off of the
object passed to the second argument to ``createForm()`` (i.e. ``$task``).
Later, when you begin :doc:`embedding forms </form/embedded>`, this will no
longer be sufficient.

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

            return $this->renderForm('task/new.html.twig', [
                'form' => $form,
            ]);
        }
    }

In versions prior to Symfony 5.3, controllers used the method
``$this->render('...', ['form' => $form->createView()])`` to render the form.
The ``renderForm()`` method abstracts this logic and it also sets the 422 HTTP
status code in the response automatically when the submitted form is not valid.

.. versionadded:: 5.3

    The ``renderForm()`` method was introduced in Symfony 5.3.

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

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <twig:form-theme>bootstrap_5_layout.html.twig</twig:form-theme>
                <!-- ... -->
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig) {
            $twig->formThemes(['bootstrap_5_layout.html.twig']);

            // ...
        };

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
            // just set up a fresh $task object (remove the example data)
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

            return $this->renderForm('task/new.html.twig', [
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
   ``false`` and the form is rendered again, but now with validation errors;

#. When the user submits the form with valid data, the submitted data is again
   written into the form, but this time :method:`Symfony\\Component\\Form\\FormInterface::isValid`
   returns ``true``. Now you have the opportunity to perform some actions using
   the ``$task`` object (e.g. persisting it to the database) before redirecting
   the user to some other page (e.g. a "thank you" or "success" page);

.. note::

    Redirecting a user after a successful form submission is a best practice
    that prevents the user from being able to hit the "Refresh" button of
    their browser and re-post the data.

.. seealso::

    If you need more control over exactly when your form is submitted or which
    data is passed to it, you can
    :doc:`use the submit() method to handle form submissions </form/direct_submit>`.

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
and the ``dueDate`` field cannot be empty, and must be a valid ``DateTime``
object.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Task.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Task
        {
            /**
             * @Assert\NotBlank
             */
            public $task;

            /**
             * @Assert\NotBlank
             * @Assert\Type("\DateTime")
             */
            protected $dueDate;
        }

    .. code-block:: php-attributes

        // src/Entity/Task.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Task
        {
            #[Assert\NotBlank]
            public $task;

            #[Assert\NotBlank]
            #[Assert\Type(\DateTime::class)]
            protected $dueDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Task:
            properties:
                task:
                    - NotBlank: ~
                dueDate:
                    - NotBlank: ~
                    - Type: \DateTime

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
                    <constraint name="Type">\DateTime</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Task.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\Type;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Task
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
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

To see the second approach - adding constraints to the form - refer to
:ref:`this section <form-option-constraints>`. Both approaches can be used together.

Form Validation Messages
~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 5.2

    The ``legacy_error_messages`` option was introduced in Symfony 5.2

The form types have default error messages that are more clear and
user-friendly than the ones provided by the validation constraints. To enable
these new messages set the ``legacy_error_messages`` option to ``false``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            form:
                legacy_error_messages: false

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:form legacy-error-messages="false"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            $framework->form()->legacyErrorMessages(false);
        };

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
    ``DELETE`` request. The :ref:`configuration-framework-http_method_override`
    option must be enabled for this to work.

Changing the Form Name
~~~~~~~~~~~~~~~~~~~~~~

If you inspect the HTML contents of the rendered form, you'll see that the
``<form>`` name and the field names are generated from the type class name
(e.g. ``<form name="task" ...>`` and ``<select name="task[dueDate][date][month]" ...>``).

If you want to modify this, use the :method:`Symfony\\Component\\Form\\FormFactoryInterface::createNamed`
method::

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Form\TaskType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Form\FormFactoryInterface;
    // ...

    class TaskController extends AbstractController
    {
        public function new(FormFactoryInterface $formFactory): Response
        {
            $task = ...;
            $form = $formFactory->createNamed('my_name', TaskType::class, $task);

            // ...
        }
    }

You can even suppress the name completely by setting it to an empty string.

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

.. caution::

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

Learn more
----------

When building forms, keep in mind that the first goal of a form is to translate
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
    /form/form_dependencies
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
    /form/validation_group_service_resolver
    /form/button_based_validation
    /form/disabling_validation

Misc.:

.. toctree::
    :maxdepth: 1

    /form/direct_submit
    /form/embedded
    /form/form_collections
    /form/inherit_data_option
    /form/multiple_buttons
    /form/unit_testing
    /form/use_empty_data
    /form/without_class

.. _`Symfony Forms screencast series`: https://symfonycasts.com/screencast/symfony-forms
.. _`MakerBundle`: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
