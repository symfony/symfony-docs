.. index::
   single: Form; Use virtuals forms

How to use the virtual form field option
========================================

The ``virtual`` form field option can be very useful when you have some
duplicated fields in different Entity.

For exemaple, let's imagine we have two entities. A Company and a Customer:


.. code-block:: php

    class Company
    {
        private $name;
        private $website;

        private $address;
        private $zipcode;
        private $city;
        private $country;
    }

    class Company
    {
        private $firstName;
        private $lastName;

        private $address;
        private $zipcode;
        private $city;
        private $country;
    }

Like you can see, both of our entities have these fields: address, zipcode, city, country.

Now, we want to build two forms. One for create/update a Company and the second to create/update a Customer.

First, we create very simple CompanyType and CustomerType:

.. code-block:: php

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('name', 'text')
                ->add('website', 'text')
            ;
        }
    }

.. code-block:: php

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('firstName', 'text')
                ->add('lastName', 'text')
            ;
        }
    }

Now, we have to deal with our four duplicated fields... Here is a (simple) location FormType:

.. code-block:: php

    class LocationType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('address', 'textarea')
                ->add('zipcode', 'string')
                ->add('city', 'string')
                ->add('country', 'text')
            ;
        }

        public function getName()
        {
            return 'location';
        }
    }

We don't have a location field in our entity so we can't directly link our LocationType.
Of course, we absolutely want to have a dedicated FormType to deal with location (remember, DRY!)

The ``virtual`` form field option is the solution.

We can set the option ``'virtual' => true`` in the getDefaultOptions method of our LocationType and directly start use it in our 2 first types.

Look at the result:

.. code-block:: php

    // CompanyType
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('foo', new LocationType());
    }

.. code-block:: php

    // CustomerType
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('bar', new LocationType());
    }

With the virtual option set to false (default behavior), the Form Component expect a Foo (or Bar) object or array which contains our four location fields. Of course, we don't have this object/array in our entities and we don't want it!

With the virtual option set to true, the Form Component skip our Foo (or Bar) object or array. So, it directly access to our 4 location fields which are in the parent entity!
