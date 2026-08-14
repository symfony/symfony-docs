Valid
=====

This constraint is used to enable validation on objects that are embedded
as properties on an object being validated. This allows you to validate
an object and all sub-objects associated with it.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Valid`
==========  ===================================================================

.. include:: /reference/forms/types/options/_error_bubbling_hint.rst.inc

Basic Usage
-----------

In the following example, create two classes ``Author`` and ``Address``
that both have constraints on their properties. Furthermore, ``Author``
stores an ``Address`` instance in the ``$address`` property::

    // src/Entity/Address.php
    namespace App\Entity;

    class Address
    {
        protected string $street;

        protected string $zipCode;
    }

.. code-block:: php

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        protected string $firstName;

        protected string $lastName;

        protected Address $address;
    }

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Address.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Address
        {
            #[Assert\NotBlank]
            protected string $street;

            #[Assert\NotBlank]
            #[Assert\Length(max: 5)]
            protected string $zipCode;
        }

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotBlank]
            #[Assert\Length(min: 4)]
            protected string $firstName;

            #[Assert\NotBlank]
            protected string $lastName;

            protected Address $address;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Address:
            properties:
                street:
                    - NotBlank: ~
                zipCode:
                    - NotBlank: ~
                    - Length:
                        max: 5

        App\Entity\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - Length:
                        min: 4
                lastName:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Address">
                <property name="street">
                    <constraint name="NotBlank"/>
                </property>
                <property name="zipCode">
                    <constraint name="NotBlank"/>
                    <constraint name="Length">
                        <option name="max">5</option>
                    </constraint>
                </property>
            </class>

            <class name="App\Entity\Author">
                <property name="firstName">
                    <constraint name="NotBlank"/>
                    <constraint name="Length">
                        <option name="min">4</option>
                    </constraint>
                </property>
                <property name="lastName">
                    <constraint name="NotBlank"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Address.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Address
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('street', new Assert\NotBlank());
                $metadata->addPropertyConstraint('zipCode', new Assert\NotBlank());
                $metadata->addPropertyConstraint('zipCode', new Assert\Length(max: 5));
            }
        }

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
                $metadata->addPropertyConstraint('firstName', new Assert\Length(min: 4));
                $metadata->addPropertyConstraint('lastName', new Assert\NotBlank());
            }
        }

With this mapping, it is possible to successfully validate an author with
an invalid address. To prevent that, add the ``Valid`` constraint to the
``$address`` property.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Valid]
            protected Address $address;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                address:
                    - Valid: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="address">
                    <constraint name="Valid"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('address', new Assert\Valid());
            }
        }

If you validate an author with an invalid address now, you can see that
the validation of the ``Address`` fields failed.

.. code-block:: text

    App\Entity\Author.address.zipCode:
        This value is too long. It should have 5 characters or less.

.. tip::

    If you also want to validate that the ``address`` property is an instance of
    the ``App\Entity\Address`` class, add the :doc:`Type constraint </reference/constraints/Type>`.

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

.. note::

    Unlike other constraints, the ``Valid`` constraint does not use the ``Default``
    group. This means that it will always be applied by default, **even** if you
    specify a group when calling the validator. If you want to restrict the
    constraint to a subset of groups, you have to define the ``groups`` option.

.. include:: /reference/constraints/_payload-option.rst.inc

``restrictGroups``
~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

Consider the following class::

    // src/Entity/Address.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Address
    {
        #[Assert\NotBlank]
        public string $street;

        #[Assert\NotBlank(groups: ['shipping'])]
        public string $phoneNumber;

        #[Assert\NotBlank(groups: ['billing'])]
        public string $email;
    }

The same class is used for the two addresses of an order. The street is always
required. The phone number is only required for the shipping address, to
coordinate the delivery. The email is only required for the billing address,
because the invoice is sent by email::

    // src/Entity/Order.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Order
    {
        #[Assert\Valid(groups: ['billing'])]
        public Address $billingAddress;

        #[Assert\Valid(groups: ['shipping'])]
        public Address $shippingAddress;
    }

During the checkout, the application validates the ``Default`` group and the
group named after each step (``billing`` or ``shipping``). When validating the
billing step (the ``Default`` and ``billing`` groups), the ``$billingAddress``
is validated only against ``billing``: the email is checked, but the ``NotBlank``
constraint of ``$street`` is **silently skipped**, even though you asked for the
``Default`` group too.

This happens because the ``groups`` option defines the full list of groups used
to validate the nested object, and the ``Default`` group is not added in this
example. Set ``restrictGroups`` to ``false`` to disable that behavior and
validate each nested object against the same groups as its parent object::

    #[Assert\Valid(groups: ['billing'], restrictGroups: false)]
    public Address $billingAddress;

    #[Assert\Valid(groups: ['shipping'], restrictGroups: false)]
    public Address $shippingAddress;

Even when setting the ``restrictGroups`` option to ``false``, the ``groups``
option still decides when the cascade validation happens: when validating the
billing step, the ``$shippingAddress`` is never validated, and vice versa.

.. versionadded:: 8.2

    The ``restrictGroups`` option was introduced in Symfony 8.2.

``traverse``
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If this constraint is applied to a ``\Traversable``, then all containing values
will be validated if this option is set to ``true``. This option is ignored on
arrays: Arrays are traversed in either case. Keys are not validated.
