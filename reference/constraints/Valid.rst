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
                $metadata->addPropertyConstraint('zipCode', new Assert\Length(['max' => 5]));
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
                $metadata->addPropertyConstraint('firstName', new Assert\Length(['min' => 4]));
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

``traverse``
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If this constraint is applied to a ``\Traversable``, then all containing values
will be validated if this option is set to ``true``. This option is ignored on
arrays: Arrays are traversed in either case. Keys are not validated.
