.. index::
   single: Form; The "inherit_data" option

How to Reduce Code Duplication with "inherit_data"
==================================================

.. versionadded:: 2.3
    This ``inherit_data`` option was introduced in Symfony 2.3. Before, it
    was known as ``virtual``.

The ``inherit_data`` form field option can be very useful when you have some
duplicated fields in different entities. For example, imagine you have two
entities, a ``Company`` and a ``Customer``::

    // src/AppBundle/Entity/Company.php
    namespace AppBundle\Entity;

    class Company
    {
        private $name;
        private $website;

        private $address;
        private $zipcode;
        private $city;
        private $country;
    }

.. code-block:: php

    // src/AppBundle/Entity/Customer.php
    namespace AppBundle\Entity;

    class Customer
    {
        private $firstName;
        private $lastName;

        private $address;
        private $zipcode;
        private $city;
        private $country;
    }

As you can see, each entity shares a few of the same fields: ``address``,
``zipcode``, ``city``, ``country``.

Start with building two forms for these entities, ``CompanyType`` and ``CustomerType``::

    // src/AppBundle/Form/Type/CompanyType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name', TextType::class)
                ->add('website', TextType::class);
        }
    }

.. code-block:: php

    // src/AppBundle/Form/Type/CustomerType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('firstName', TextType::class)
                ->add('lastName', TextType::class);
        }
    }

Instead of including the duplicated fields ``address``, ``zipcode``, ``city``
and ``country`` in both of these forms, create a third form called ``LocationType``
for that::

    // src/AppBundle/Form/Type/LocationType.php
    namespace AppBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;

    class LocationType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('address', TextareaType::class)
                ->add('zipcode', TextType::class)
                ->add('city', TextType::class)
                ->add('country', TextType::class);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'inherit_data' => true
            ));
        }
    }

The location form has an interesting option set, namely ``inherit_data``. This
option lets the form inherit its data from its parent form. If embedded in
the company form, the fields of the location form will access the properties of
the ``Company`` instance. If embedded in the customer form, the fields will
access the properties of the ``Customer`` instance instead. Easy, eh?

.. note::

    Instead of setting the ``inherit_data`` option inside ``LocationType``, you
    can also (just like with any option) pass it in the third argument of
    ``$builder->add()``.

Finally, make this work by adding the location form to your two original forms::

    // src/AppBundle/Form/Type/CompanyType.php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('foo', LocationType::class, array(
            'data_class' => 'AppBundle\Entity\Company'
        ));
    }

.. code-block:: php

    // src/AppBundle/Form/Type/CustomerType.php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('bar', LocationType::class, array(
            'data_class' => 'AppBundle\Entity\Customer'
        ));
    }

That's it! You have extracted duplicated field definitions to a separate
location form that you can reuse wherever you need it.

.. caution::

    Forms with the ``inherit_data`` option set cannot have ``*_SET_DATA`` event listeners.
