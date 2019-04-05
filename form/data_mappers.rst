.. index::
    single: Form; Data mappers

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

* **Data transformers** change the representation of a value (e.g. from
  ``"2016-08-12"`` to a ``DateTime`` instance);
* **Data mappers** map data (e.g. an object or array) to form fields, and vice versa.

Changing a ``YYYY-mm-dd`` string value to a ``DateTime`` instance is done by a
data transformer. Populating inner fields (e.g year, hour, etc) of a compound date type using
a ``DateTime`` instance is done by the data mapper.

Creating a Data Mapper
----------------------

Suppose that you want to save a set of colors to the database. For this, you're
using an immutable color object::

    // src/Painting/Color.php
    namespace App\Painting;

    final class Color
    {
        private $red;
        private $green;
        private $blue;

        public function __construct(int $red, int $green, int $blue)
        {
            $this->red = $red;
            $this->green = $green;
            $this->blue = $blue;
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
form fields. Recognize a familiar pattern? It's time for a data mapper::

    // src/Form/DataMapper/ColorMapper.php
    namespace App\Form\DataMapper;

    use App\Painting\Color;
    use Symfony\Component\Form\DataMapperInterface;
    use Symfony\Component\Form\Exception\UnexpectedTypeException;
    use Symfony\Component\Form\FormInterface;

    final class ColorMapper implements DataMapperInterface
    {
        /**
         * @param Color|null $data
         */
        public function mapDataToForms($data, $forms)
        {
            // there is no data yet, so nothing to prepopulate
            if (null === $data) {
                return;
            }

            // invalid data type
            if (!$data instanceof Color) {
                throw new UnexpectedTypeException($data, Color::class);
            }

            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            // initialize form field values
            $forms['red']->setData($data->getRed());
            $forms['green']->setData($data->getGreen());
            $forms['blue']->setData($data->getBlue());
        }

        public function mapFormsToData($forms, &$data)
        {
            /** @var FormInterface[] $forms */
            $forms = iterator_to_array($forms);

            // as data is passed by reference, overriding it will change it in
            // the form object as well
            // beware of type inconsistency, see caution below
            $data = new Color(
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

You're ready to use the data mapper for the ``ColorType`` form. Use the
:method:`Symfony\\Component\\Form\\FormConfigBuilderInterface::setDataMapper`
method to configure the data mapper::

    // src/Form/Type/ColorType.php
    namespace App\Form\Type;

    use App\Form\DataMapper\ColorMapper;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\IntegerType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    final class ColorType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
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
                ->setDataMapper(new ColorMapper())
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            // when creating a new color, the initial data should be null
            $resolver->setDefault('empty_data', null);
        }
    }

Cool! When using the ``ColorType`` form, the custom ``ColorMapper`` will create
a new ``Color`` object now.

.. caution::

    When a form has the ``inherit_data`` option set to ``true``, it does not use the data mapper and
    lets its parent map inner values.

.. tip::

    You can also implement the ``DataMapperInterface`` in the ``ColorType`` and add
    the ``mapDataToForms()`` and ``mapFormsToData()`` in the form type directly
    to avoid creating a new class. You'll then have to call
    ``$builder->setDataMapper($this)``.
