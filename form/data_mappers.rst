When and How to Use Data Mappers
================================

When a form is compound, the initial data needs to be passed to children so each can display their
own input value. On submission, children values need to be written back into the form.

Data mappers are responsible for reading and writing data from and into parent forms.

The main built-in data mapper uses the :doc:`PropertyAccess component </components/property_access>`
and will fit most cases. However, you can create your own implementation that
could, for example, pass submitted data to immutable objects via their constructor.

The Difference between Data Transformers and Mappers
----------------------------------------------------

It is important to know the difference between
:doc:`data transformers </form/data_transformers>` and mappers.

* **Data transformers** change the representation of a single value, e.g. from
  ``"2016-08-12"`` to a ``DateTime`` instance;
* **Data mappers** map data (e.g. an object or array) to one or many form fields, and vice versa,
  e.g. using a single ``DateTime`` instance to populate the inner fields (e.g year, hour, etc.)
  of a compound date type.

Creating a Data Mapper
----------------------

Suppose that you want to save a set of colors to the database. For this, you're
using an immutable color object::

    // src/Painting/Color.php
    namespace App\Painting;

    final class Color
    {
        public function __construct(
            private int $red,
            private int $green,
            private int $blue,
        ) {
        }

        public function getRed(): int
        {
            return $this->red;
        }

        public function getGreen(): int
        {
            return $this->green;
        }

        public function getBlue(): int
        {
            return $this->blue;
        }
    }

The form type should be allowed to edit a color. But because you've decided to
make the ``Color`` object immutable, a new color object has to be created each time
one of the values is changed.

.. tip::

    If you're using a mutable object with constructor arguments, instead of
    using a data mapper, you should configure the ``empty_data`` option with a closure
    as described in
    :ref:`How to Configure empty Data for a Form Class <forms-empty-data-closure>`.

The red, green and blue form fields have to be mapped to the constructor
arguments and the ``Color`` instance has to be mapped to red, green and blue
form fields. Recognize a familiar pattern? It's time for a data mapper. The
easiest way to create one is by implementing :class:`Symfony\\Component\\Form\\DataMapperInterface`
in your form type::

    // src/Form/ColorType.php
    namespace App\Form;

    use App\Painting\Color;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\DataMapperInterface;
    use Symfony\Component\Form\Exception\UnexpectedTypeException;
    use Symfony\Component\Form\FormInterface;

    final class ColorType extends AbstractType implements DataMapperInterface
    {
        // ...

        /**
         * @param Color|null $viewData
         */
        public function mapDataToForms($viewData, \Traversable $forms): void
        {
            // there is no data yet, so nothing to prepopulate
            if (null === $viewData) {
                return;
            }

            // invalid data type
            if (!$viewData instanceof Color) {
                throw new UnexpectedTypeException($viewData, Color::class);
            }

            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            // initialize form field values
            $forms['red']->setData($viewData->getRed());
            $forms['green']->setData($viewData->getGreen());
            $forms['blue']->setData($viewData->getBlue());
        }

        public function mapFormsToData(\Traversable $forms, &$viewData): void
        {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            // as data is passed by reference, overriding it will change it in
            // the form object as well
            // beware of type inconsistency, see caution below
            $viewData = new Color(
                $forms['red']->getData(),
                $forms['green']->getData(),
                $forms['blue']->getData()
            );
        }
    }

.. caution::

    The data passed to the mapper is *not yet validated*. This means that your
    objects should allow being created in an invalid state in order to produce
    user-friendly errors in the form.

Using the Mapper
----------------

After creating the data mapper, you need to configure the form to use it. This is
achieved using the :method:`Symfony\\Component\\Form\\FormConfigBuilderInterface::setDataMapper`
method::

    // src/Form/Type/ColorType.php
    namespace App\Form\Type;

    // ...
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    final class ColorType extends AbstractType implements DataMapperInterface
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('red', IntegerType::class, [
                    // enforce the strictness of the type to ensure the constructor
                    // of the Color class doesn't break
                    'empty_data' => '0',
                ])
                ->add('green', IntegerType::class, [
                    'empty_data' => '0',
                ])
                ->add('blue', IntegerType::class, [
                    'empty_data' => '0',
                ])
                // configure the data mapper for this FormType
                ->setDataMapper($this)
            ;
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            // when creating a new color, the initial data should be null
            $resolver->setDefault('empty_data', null);
        }

        // ...
    }

Cool! When using the ``ColorType`` form, the custom data mapper methods will
create a new ``Color`` object now.

Mapping Form Fields Using Callbacks
-----------------------------------

Conveniently, you can also map data from and into a form field by using the
``getter`` and ``setter`` options. For example, suppose you have a form with some
fields and only one of them needs to be mapped in some special way or you only
need to change how it's written into the underlying object. In that case, register
a PHP callable that is able to write or read to/from that specific object::

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ...

        $builder->add('state', ChoiceType::class, [
            'choices' => [
                'active' => true,
                'paused' => false,
            ],
            'getter' => function (Task $task, FormInterface $form): bool {
                return !$task->isCancelled() && !$task->isPaused();
            },
            'setter' => function (Task &$task, bool $state, FormInterface $form): void {
                if ($state) {
                    $task->activate();
                } else {
                    $task->pause();
                }
            },
        ]);
    }

If available, these options have priority over the property path accessor and
the default data mapper will still use the :doc:`PropertyAccess component </components/property_access>`
for the other form fields.

.. caution::

    When a form has the ``inherit_data`` option set to ``true``, it does not use the data mapper and
    lets its parent map inner values.
