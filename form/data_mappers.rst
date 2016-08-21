.. index::
    single: Form; Data mappers

How to Use Data Mappers
=======================

Data mappers are the layer between your form data (e.g. the bound object) and
the form. They are responsible for mapping the data to the fields and back. The
built-in data mapper uses the :doc:`PropertyAccess component </components/property_access>`
and will fit most cases. However, you can create your own data mapper that
could, for example, pass data to immutable objects via their constructor.

The Difference between Data Transformers and Mappers
----------------------------------------------------

It is important to know the difference between
:doc:`data transformers </form/data_transformers>` and mappers.

* **Data transformers** change the representation of a value. E.g. from
  ``"2016-08-12"`` to a ``DateTime`` instance;
* **Data mappers** map data (e.g. an object) to form fields, and vice versa.

Changing a ``YYYY-mm-dd`` string value to a ``DateTime`` instance is done by a
data transformer. Mapping this ``DateTime`` instance to a property on your
object (e.g. by calling a setter or some other method) is done by a data
mapper.

Creating a Data Mapper
----------------------

Suppose that you want to save a set of colors to the database. For this, you're
using an immutable color object::

    // src/AppBundle/Colors/Color.php
    namespace AppBundle\Colors;

    class Color
    {
        private $red;
        private $green;
        private $blue;

        public function __construct($red, $green, $blue)
        {
            $this->red = $red;
            $this->green = $green;
            $this->blue = $blue;
        }

        public function getRed()
        {
            return $this->red;
        }

        public function getGreen()
        {
            return $this->green;
        }

        public function getBlue()
        {
            return $this->blue;
        }
    }

The form type should be allowed to edit a color. But because you've decided to
make the ``Color`` object immutable, a new color object has to be created each time
one of the values is changed.

.. tip::

    If you're using a mutable object with constructor arguments, instead of
    using a data mapper, you should configure the ``empty_data`` with a closure
    as described in
    :ref:`How to Configure empty Data for a Form Class <forms-empty-data-closure>`.

The red, green and blue form fields have to be mapped to the constructor
arguments and the ``Color`` instance has to be mapped to red, green and blue
form fields. Recognize a familiar pattern? It's time for a data mapper!

.. code-block:: php

    // src/AppBundle/Form/DataMapper/ColorMapper.php
    namespace AppBundle\Form\DataMapper;

    use AppBundle\Colors\Color;
    use Symfony\Component\Form\DataMapperInterface;
    use Symfony\Component\Form\Exception\UnexpectedTypeException;

    class ColorMapper implements DataMapperInterface
    {
        public function mapDataToForms($data, $forms)
        {
            // there is no data yet, a new color will be created
            if (null === $data) {
                return;
            }

            // invalid data type, this message will not be shown to the user (see below)
            if (!$data instanceof Color) {
                throw new UnexpectedTypeException($data, Color::class);
            }

            $forms = iterator_to_array($forms);

            // set form field values
            $forms['red']->setData($data->getRed());
            $forms['green']->setData($data->getGreen());
            $forms['blue']->setData($data->getBlue());
        }

        public function mapFormsToData($forms, &$data)
        {
            $forms = iterator_to_array($forms);

            // get form field values
            $red = $forms['red']->getData();
            $green = $forms['green']->getData();
            $blue = $forms['blue']->getData();

            // as data is passed by reference, overriding it will change it in
            // the form object as well
            $data = new Color($red, $green, $blue);
        }
    }

.. caution::

    The data passed to the mapper is *not yet validated*. This means that your
    objects should allow being created in an invalid state in order to produce
    user-friendly errors in the form.

Using the Mapper
----------------

You're ready to use the data mapper for the ``ColorType`` form. Use the
:method:`Symfony\\Component\\Form\\FormBuilderInterface::setDataMapper`
method to configure the data mapper::

    // src/AppBundle/Form/ColorType.php
    namespace AppBundle\Form;

    use AppBundle\Form\DataMapper\ColorMapper;

    // ...
    class ColorType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('red', 'integer')
                ->add('green', 'integer')
                ->add('blue', 'integer')

                ->setDataMapper(new ColorMapper())
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                // when creating a new color, the initial data should be null
                'empty_data' => null,
            ));
        }
    }

Cool! When using the ``ColorType`` form, the custom ``ColorMapper`` will create
the ``Color`` object now.

.. caution::

    When a form field has the ``inherit_data`` option set, data mappers won't
    be applied to that field.

.. tip::

    You can also implement ``DataMapperInterface`` in the ``ColorType`` and add
    the ``mapDataToForms()`` and ``mapFormsToData()`` in the form type directly
    to avoid creating a new class. You'll then have to call
    ``$builder->setDataMapper($this)``.
