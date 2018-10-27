.. index::
   single: Form; Data Transfer Objects

How to use Data Transfer Objects (DTOs)
=======================================

Data Transfer Objects can be used by forms to separate entities from the
validation logic of forms.
Entities should always have a valid state.
When entities are used as data classes for a form, the data is injected into
the entity and validated.
When the validation fails, the invalid data is still left in the entity.
This can lead to invalid data being saved in the database.

You will use the Maker bundle to highlight the differences between using DTOs
and entities.

.. index::
   single: Installation

Installation
~~~~~~~~~~~~

In applications using :doc:`Symfony Flex </setup/flex>`, run this command to
install the Maker bundle before using it:

.. code-block:: terminal

    $ composer require maker --dev

You will also need these packages in order to proceed with creating a CRUD
example:

.. code-block:: terminal

    $ composer require form validator twig-bundle orm-pack security-csrf annotations

Use the example ``Task`` entity from :doc:`the main forms tutorial </forms>`,
make it a doctrine entity (this requires adding a primary key ``id``), make the
setters fluent and add a validation by adding annotations.

.. code-block:: diff

    // src/Entity/Task.php
    namespace App\Entity;

    + use Symfony\Component\Validator\Constraints as Assert;
    + use Doctrine\ORM\Mapping as ORM;

    + /**
    + * @ORM\Entity
    + */
    class Task
    {
    +   /**
    +    * @ORM\Id()
    +    * @ORM\GeneratedValue()
    +    * @ORM\Column(type="integer")
    +    */
    +   protected $id;

    +   /**
    +    * @ORM\Column(type="string", length=255)
    +    * @Assert\NotBlank()
    +    */
        protected $task;

    +   /**
    +   * @ORM\Column(type="datetime", nullable=true)
    +   * @Assert\DateTime()
    +   */
        protected $dueDate;

    +   public function getId()
    +   {
    +       return $this->id;
    +   }
    +
        public function getTask()
        {
            return $this->task;
        }

        public function setTask($task)
        {
            $this->task = $task;
    +
    +       return $this;
        }

        public function getDueDate()
        {
            return $this->dueDate;
        }

        public function setDueDate(\DateTime $dueDate = null)
        {
            $this->dueDate = $dueDate;
    +
    +       return $this;
        }
    }

.. index::
   single: Creating a Data Transfer Object

Creating a Data Transfer Object
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now, create a Data Transfer Object for the ``Task`` entity using the maker:

.. code-block:: terminal

    $ php bin/console make:dto TaskData

    The name of Entity that the DTO will be bound to:
    > Task

    Add helper extract/fill methods? (yes/no) [yes]:
    >

    Omit generation of getters/setters? (yes/no) [yes]:
    >

    Omit Id field in DTO? (yes/no) [yes]:
    >

.. tip::

    Ignore the next steps suggested by the command for now, you will generate a
    complete CRUD with a different maker instead of a form in the next step.

If you used the defaults during the dialogue, you will end up with the
following ``TaskData`` class:

.. code-block:: php

    // src/Form/Data/TaskData.php
    namespace App\Form\Data;

    use App\Entity\Task;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
    * Data transfer object for Task.
    * Add your constraints as annotations to the properties.
    */
    class TaskData
    {
        /**
        * @Assert\NotBlank(message="This value should not be blank.", payload=null)
        */
        public $task;

        /**
        * @Assert\DateTime(format="Y-m-d H:i:s", message="This value is not a valid datetime.", payload=null)
        */
        public $dueDate;

        /**
        * Create DTO, optionally extracting data from a model.
        *
        * @param Task|null $task
        */
        public function __construct(? Task $task = null)
        {
            if ($task instanceof Task) {
                $this->extract($task);
            }
        }

        /**
        * Fill entity with data from the DTO.
        *
        * @param Task $task
        */
        public function fill(Task $task): Task
        {
            $task
                ->setTask($this->task)
                ->setDueDate($this->dueDate)
            ;

            return $task;
        }

        /**
        * Extract data from entity into the DTO.
        *
        * @param Task $task
        */
        public function extract(Task $task): self
        {
            $this->task = $task->getTask();
            $this->dueDate = $task->getDueDate();

            return $this;
        }
    }

Notice the assert annotations? These were copied from the Task entity.
The ``extract`` and ``fill`` methods can be used to populate the DTO with data
from the entity and vice versa.

.. caution::

    During the generation of a DTO, validation annotations are copied from the
    Entity.
    You must ensure that changes to the validations are added in both places
    when the entity is used with forms in other places (like
    ``SonataAdminBundle`` or ``EasyAdminBundle``).
    If the entity is not used at all, it is recommended to move all validations
    into the DTO, removing them from the entity class.

.. index::
   single: Using the DTO in the Form

Using the DTO in the Form
~~~~~~~~~~~~~~~~~~~~~~~~~

Use the maker to create a simple CRUD application.

.. code-block:: terminal

    $ php bin/console make:crud Task

This will generate a bunch of templates, a controller and a form.
First, take a look at the generated ``TaskType`` form.

Notice that it uses the ``Task`` entity by default.
This means that the form data is injected into the ``Task`` entity directly and validated with the annotations.

Replace this with ``TaskData`` to prevent the aforementioned problems with an invalid entity.

.. code-block:: diff

    // src/Form/TaskType.php
    namespace App\Form;

    - use App\Entity\Task;
    + use App\Form\Data\TaskData;
    + use Symfony\Component\Form\Extension\Core\Type\DateType;
    // ...

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('task')
    -           ->add('dueDate')
    +           ->add('dueDate', DateType::class)
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
    -           'data_class' => Task::class,
    +           'data_class' => TaskData::class,
            ]);
        }
    }

For this specific example, we also need to explicitly set the ``DateType`` for
the ``dueDate`` field, as the form component can not guess it from the entity.

.. index::
   single: Using the DTO in the Controller

Using the DTO in the Controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now, look at the ``App\Controller\TaskController`` class, that was generated by ``make:crud`` earlier.
It also uses the ``Task`` entity directly.
This is fine for the ``index()`` and ``show()`` methods, as no data is written there.

Replace the ``Task`` entity with ``TaskData`` in the ``new()`` and ``edit()`` methods, using the ``fill()`` helper.

.. code-block:: diff

    // src/Controller/TaskController.php
    namespace App\Controller;

    use App\Entity\Task;
    + use App\Form\Data\TaskData;

    // ...

    /**
    * @Route("/task")
    */
    class TaskController extends AbstractController
    {

    // ...

      /**
      * @Route("/new", name="task_new", methods="GET|POST")
      */
      public function new(Request $request): Response
      {
    -     $task = new Task();
    -     $form = $this->createForm(TaskType::class, $task);
    +     $taskData = new TaskData();
    +     $form = $this->createForm(TaskType::class, $taskData);
          $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {
    +         $task = $taskData->fill(new Task());
              $em = $this->getDoctrine()->getManager();
              $em->persist($task);
              $em->flush();

              return $this->redirectToRoute('task_index');
          }

          return $this->render('task/new.html.twig', [
    -         'task' => $task,
    +         'task' => $taskData,
              'form' => $form->createView(),
          ]);
      }

The form handles the data using ``TaskData``, the ``Task`` entity now is only created after validation.

In ``edit()``, the ``Task`` entity is injected by Symfony's ``ParamConverter``.
Create a new ``TaskData`` object and pass it the ``Task`` entity (internally, the ``extract()`` helper will populate the DTO).
Replace the ``$task`` argument with ``$taskData`` in the ``createForm()`` call, so that the form uses the DTO.

.. code-block:: diff

    /**
     * @Route("/{id}/edit", name="task_edit", methods="GET|POST")
     */
    public function edit(Request $request, Task $task): Response
    {
    -   $form = $this->createForm(TaskType::class, $task);
    +   $taskData = new TaskData($task);
    +   $form = $this->createForm(TaskType::class, $taskData);
    +
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
    +       $task = $taskData->fill($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_edit', ['id' => $task->getId()]);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

Now, when the user submits data, it is first validated using ``TaskData`` and only after successfull validation passed onto the ``Task`` entity.
``Task`` entites will always be valid.
