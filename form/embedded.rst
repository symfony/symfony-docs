How to Embed Forms
==================

Often, you'll want to build a form that will include fields from many different
objects. For example, a registration form may contain data belonging to
a ``User`` object as well as many ``Address`` objects. Fortunately this can
be achieved by the Form component.

.. _forms-embedding-single-object:

Embedding a Single Object
-------------------------

Suppose that each ``Task`` belongs to a ``Category`` object. Start by
creating the ``Category`` class::

    // src/Entity/Category.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Category
    {
        #[Assert\NotBlank]
        public string $name;
    }

Next, add a new ``category`` property to the ``Task`` class::

    // ...

    class Task
    {
        // ...

        #[Assert\Type(type: Category::class)]
        #[Assert\Valid]
        protected ?Category $category = null;

        // ...

        public function getCategory(): ?Category
        {
            return $this->category;
        }

        public function setCategory(?Category $category): void
        {
            $this->category = $category;
        }
    }

.. tip::

    The ``Valid`` Constraint has been added to the property ``category``. This
    cascades the validation to the corresponding entity. If you omit this constraint,
    the child entity would not be validated.

Now that your application has been updated to reflect the new requirements,
create a form class so that a ``Category`` object can be modified by the user::

    // src/Form/CategoryType.php
    namespace App\Form;

    use App\Entity\Category;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class CategoryType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder->add('name');
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Category::class,
            ]);
        }
    }

The end goal is to allow the ``Category`` of a ``Task`` to be modified right
inside the task form itself. To accomplish this, add a ``category`` field
to the ``TaskType`` object whose type is an instance of the new ``CategoryType``
class::

    use App\Form\CategoryType;
    use Symfony\Component\Form\FormBuilderInterface;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('category', CategoryType::class);
    }

The fields from ``CategoryType`` can now be rendered alongside those from
the ``TaskType`` class.

Render the ``Category`` fields in the same way as the original ``Task`` fields:

.. code-block:: html+twig

    {# ... #}

    <h3>Category</h3>
    <div class="category">
        {{ form_row(form.category.name) }}
    </div>

    {# ... #}

When the user submits the form, the submitted data for the ``Category`` fields
are used to construct an instance of ``Category``, which is then set on the
``category`` field of the ``Task`` instance.

The ``Category`` instance is accessible naturally via ``$task->getCategory()``
and can be persisted to the database or used however you need.

Embedding a Collection of Forms
-------------------------------

You can also embed a collection of forms into one form (imagine a ``Category``
form with many ``Product`` sub-forms). This is done by using the ``collection``
field type.

For more information see the :doc:`/form/form_collections` article and the
:doc:`CollectionType </reference/forms/types/collection>` reference.
