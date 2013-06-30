.. index::
   single: Form; The "inherit_data" option

How to Reduce Code Duplication with "inherit_data"
==================================================

.. versionadded:: 2.3
    This ``inherit_data`` option was known as ``virtual`` before Symfony 2.3.

The ``inherit_data`` form field option can be very useful when you have some
duplicated fields in different entities. For example, imagine you have two
entities, a ``Company`` and a ``Customer``::

    // src/Acme/HelloBundle/Entity/Company.php
    namespace Acme\HelloBundle\Entity;

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

    // src/Acme/HelloBundle/Entity/Customer.php
    namespace Acme\HelloBundle\Entity;

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

Let's build two forms for these entities, ``CompanyType`` and ``CustomerType``::

    // src/Acme/HelloBundle/Form/Type/CompanyType.php
    namespace Acme\HelloBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name', 'text')
                ->add('website', 'text');
        }
    }

.. code-block:: php

    // src/Acme/HelloBundle/Form/Type/CustomerType.php
    namespace Acme\HelloBundle\Form\Type;

    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\Form\AbstractType;

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('firstName', 'text')
                ->add('lastName', 'text');
        }
    }

Instead of including the duplicated fields ``address``, ``zipcode``, ``city``
and ``country`` in both of these forms, we will create a third form for that.
We will call this form simply ``LocationType``::

    // src/Acme/HelloBundle/Form/Type/LocationType.php
    namespace Acme\HelloBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class LocationType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('address', 'textarea')
                ->add('zipcode', 'text')
                ->add('city', 'text')
                ->add('country', 'text');
        }

        public function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            $resolver->setDefaults(array(
                'inherit_data' => true
            ));
        }

        public function getName()
        {
            return 'location';
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

Let's make this work by adding the location form to our two original forms::

    // src/Acme/HelloBundle/Form/Type/CompanyType.php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('foo', new LocationType(), array(
            'data_class' => 'Acme\HelloBundle\Entity\Company'
        ));
    }

.. code-block:: php

    // src/Acme/HelloBundle/Form/Type/CustomerType.php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...

        $builder->add('bar', new LocationType(), array(
            'data_class' => 'Acme\HelloBundle\Entity\Customer'
        ));
    }

That's it! You have extracted duplicated field definitions to a separate
location form that you can reuse wherever you need it.
