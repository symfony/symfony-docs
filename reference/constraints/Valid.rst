Valid
=====

This constraint is used to enable validation on objects that are embedded
as properties on an object being validated. This allows you to validate
an object and all sub-objects associated with it.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `traverse`_                                                       |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Valid`          |
+----------------+---------------------------------------------------------------------+

.. include:: /reference/forms/types/options/_error_bubbling_hint.rst.inc

Basic Usage
-----------

In the following example, create two classes ``Author`` and ``Address``
that both have constraints on their properties. Furthermore, ``Author``
stores an ``Address`` instance in the ``$address`` property.

.. code-block:: php

    // src/AppBundle/Entity/Address.php
    namespace AppBundle\Entity;

    class Address
    {
        protected $street;
        protected $zipCode;
    }

.. code-block:: php

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

    class Author
    {
        protected $firstName;
        protected $lastName;
        protected $address;
    }

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Address.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Address
        {
            /**
             * @Assert\NotBlank()
             */
            protected $street;

            /**
             * @Assert\NotBlank
             * @Assert\Length(max = 5)
             */
            protected $zipCode;
        }

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank
             * @Assert\Length(min = 4)
             */
            protected $firstName;

            /**
             * @Assert\NotBlank
             */
            protected $lastName;

            protected $address;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Address:
            properties:
                street:
                    - NotBlank: ~
                zipCode:
                    - NotBlank: ~
                    - Length:
                        max: 5

        AppBundle\Entity\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - Length:
                        min: 4
                lastName:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Address">
                <property name="street">
                    <constraint name="NotBlank" />
                </property>
                <property name="zipCode">
                    <constraint name="NotBlank" />
                    <constraint name="Length">
                        <option name="max">5</option>
                    </constraint>
                </property>
            </class>

            <class name="AppBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="NotBlank" />
                    <constraint name="Length">
                        <option name="min">4</option>
                    </constraint>
                </property>
                <property name="lastName">
                    <constraint name="NotBlank" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Address.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Address
        {
            protected $street;
            protected $zipCode;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('street', new Assert\NotBlank());
                $metadata->addPropertyConstraint('zipCode', new Assert\NotBlank());
                $metadata->addPropertyConstraint('zipCode', new Assert\Length(array("max" => 5)));
            }
        }

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $firstName;
            protected $lastName;
            protected $address;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
                $metadata->addPropertyConstraint('firstName', new Assert\Length(array("min" => 4)));
                $metadata->addPropertyConstraint('lastName', new Assert\NotBlank());
            }
        }

With this mapping, it is possible to successfully validate an author with
an invalid address. To prevent that, add the ``Valid`` constraint to the
``$address`` property.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Valid
             */
            protected $address;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                address:
                    - Valid: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="address">
                    <constraint name="Valid" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $address;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('address', new Assert\Valid());
            }
        }

If you validate an author with an invalid address now, you can see that
the validation of the ``Address`` fields failed.

.. code-block:: text

    AppBundle\\Author.address.zipCode:
        This value is too long. It should have 5 characters or less.

Options
-------

traverse
~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If this constraint is applied to a property that holds an array of objects,
then each object in that array will be validated only if this option is
set to ``true``.

.. include:: /reference/constraints/_payload-option.rst.inc
