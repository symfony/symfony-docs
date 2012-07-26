.. index::
   single: Form; Virtual forms

How to use the Virtual Form Field Option
========================================

The ``virtual`` form field option can be very useful when you have some
duplicated fields in different entities.

For example, imagine you have two entities, a ``Company`` and a ``Customer``::

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

Like you can see, each entity shares a few of the same fields: ``address``,
``zipcode``, ``city``, ``country``.

Now, you want to build two forms: one for a ``Company`` and the second for
a ``Customer``.

Start by creating a very simple ``CompanyType`` and ``CustomerType``::

    // src/Acme/HelloBundle/Form/Type/CompanyType.php
    namespace Acme\HelloBundle\Form\Type;

    class CompanyType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('name', 'text')
                ->add('website', 'text');
        }
    }

.. code-block:: php

    // src/Acme/HelloBundle/Form/Type/CustomerType.php
    namespace Acme\HelloBundle\Form\Type;

    class CustomerType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('firstName', 'text')
                ->add('lastName', 'text');
        }
    }

Now, we have to deal with the four duplicated fields. Here is a (simple)
location form type::

    // src/Acme/HelloBundle/Form/Type/LocationType.php
    namespace Acme\HelloBundle\Form\Type;

    class LocationType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->add('address', 'textarea')
                ->add('zipcode', 'text')
                ->add('city', 'text')
                ->add('country', 'text');
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'virtual' => true,
            );
        }

        public function getName()
        {
            return 'location';
        }
    }

We don't *actually* have a location field in each of our entities, so we
can't directly link our ``LocationType`` to our ``CompanyType`` or ``CustomerType``.
But we absolutely want to have a dedicated form type to deal with location (remember, DRY!).

The ``virtual`` form field option is the solution.

We can set the option ``'virtual' => true`` in the ``getDefaultOptions`` method
of ``LocationType`` and directly start using it in the two original form types.

Look at the result::

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

With the virtual option set to false (default behavior), the Form Component
expects each underlying object to have a ``foo`` (or ``bar``) property that
is either some object or array which contains the four location fields.
Of course, we don't have this object/array in our entities and we don't want it!

With the virtual option set to true, the Form component skips the ``foo`` (or ``bar``)
property, and instead "gets" and "sets" the 4 location fields directly
on the underlying object!

.. note::

    Instead of setting the ``virtual`` option inside ``LocationType``, you
    can (just like with any options) also pass it in as an array option to
    the third argument of ``$builder->add()``.
