How to Reduce Code Duplication with "inherit_data"
==================================================

The ``inherit_data`` form field option can be very useful when you have some
duplicated fields in different entities. For example, imagine you have two
entities, a ``Company`` and a ``Customer``::

    // src/Entity/Company.php
    namespace App\Entity;

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

    // src/Entity/Customer.php
    namespace App\Entity;

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

    // src/Form/Type/CompanyType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('name', TextType::class)
                ->add('website', TextType::class);
        }
    }

.. code-block:: php

    // src/Form/Type/CustomerType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('firstName', TextType::class)
                ->add('lastName', TextType::class);
        }
    }

Instead of including the duplicated fields ``address``, ``zipcode``, ``city``
and ``country`` in both of these forms, create a third form called ``LocationType``
for that::

    // src/Form/Type/LocationType.php
    namespace App\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\TextareaType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class LocationType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('address', TextareaType::class)
                ->add('zipcode', TextType::class)
                ->add('city', TextType::class)
                ->add('country', TextType::class);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'inherit_data' => true,
            ]);
        }
    }

The location form has an interesting option set, namely ``inherit_data``. This
option lets the form inherit its data from its parent form. If embedded in
the company form, the fields of the location form will access the properties of
the ``Company`` instance. If embedded in the customer form, the fields will
access the properties of the ``Customer`` instance instead. Convenient, eh?

.. note::

    Instead of setting the ``inherit_data`` option inside ``LocationType``, you
    can also (just like with any option) pass it in the third argument of
    ``$builder->add()``.

Finally, make this work by adding the location form to your two original forms::

    // src/Form/Type/CompanyType.php
    namespace App\Form\Type;

    use App\Entity\Company;
    use Symfony\Component\Form\AbstractType;

    // ...

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            // ...

            $builder->add('foo', LocationType::class, [
                'data_class' => Company::class,
            ]);
        }
    }

.. code-block:: php

    // src/Form/Type/CustomerType.php
    namespace App\Form\Type;

    use App\Entity\Customer;
    use Symfony\Component\Form\AbstractType;

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            // ...

            $builder->add('bar', LocationType::class, [
                'data_class' => Customer::class,
            ]);
        }
    }

That's it! You have extracted duplicated field definitions to a separate
location form that you can reuse wherever you need it.

.. caution::

    Forms with the ``inherit_data`` option set cannot have ``*_SET_DATA`` event listeners.
