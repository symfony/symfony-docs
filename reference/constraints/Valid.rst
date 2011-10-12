Valid
=====

This constraint is used to enable validation on objects that are embedded
as properties on an object being validated. This allows you to validate an
object and all sub-objects associated with it.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - `traverse`_                                                       |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Type`           |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

In the following example, we create two classes ``Author`` and ``Address``
that both have constraints on their properties. Furthermore, ``Author`` stores
an ``Address`` instance in the ``$address`` property.

.. code-block:: php

    // src/Acme/HelloBundle/Address.php
    class Address
    {
        protected $street;
        protected $zipCode;
    }

.. code-block:: php

    // src/Acme/HelloBundle/Author.php
    class Author
    {
        protected $firstName;
        protected $lastName;
        protected $address;
    }

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Address:
            properties:
                street:
                    - NotBlank: ~
                zipCode:
                    - NotBlank: ~
                    - MaxLength: 5

        Acme\HelloBundle\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - MinLength: 4
                lastName:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Address">
            <property name="street">
                <constraint name="NotBlank" />
            </property>
            <property name="zipCode">
                <constraint name="NotBlank" />
                <constraint name="MaxLength">5</constraint>
            </property>
        </class>

        <class name="Acme\HelloBundle\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">4</constraint>
            </property>
            <property name="lastName">
                <constraint name="NotBlank" />
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Address.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Address
        {
            /**
             * @Assert\NotBlank()
             */
            protected $street;

            /**
             * @Assert\NotBlank
             * @Assert\MaxLength(5)
             */
            protected $zipCode;
        }

        // src/Acme/HelloBundle/Author.php
        class Author
        {
            /**
             * @Assert\NotBlank
             * @Assert\MinLength(4)
             */
            protected $firstName;

            /**
             * @Assert\NotBlank
             */
            protected $lastName;
            
            protected $address;
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Address.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\MaxLength;
        
        class Address
        {
            protected $street;

            protected $zipCode;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('street', new NotBlank());
                $metadata->addPropertyConstraint('zipCode', new NotBlank());
                $metadata->addPropertyConstraint('zipCode', new MaxLength(5));
            }
        }

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\MinLength;
        
        class Author
        {
            protected $firstName;

            protected $lastName;
            
            protected $address;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new NotBlank());
                $metadata->addPropertyConstraint('firstName', new MinLength(4));
                $metadata->addPropertyConstraint('lastName', new NotBlank());
            }
        }

With this mapping, it is possible to successfully validate an author with an
invalid address. To prevent that, add the ``Valid`` constraint to the ``$address``
property.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                address:
                    - Valid: ~

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <property name="address">
                <constraint name="Valid" />
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /* ... */
            
            /**
             * @Assert\Valid
             */
            protected $address;
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\Valid;
        
        class Author
        {
            protected $address;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('address', new Valid());
            }
        }

If you validate an author with an invalid address now, you can see that the
validation of the ``Address`` fields failed.

    Acme\HelloBundle\Author.address.zipCode:
    This value is too long. It should have 5 characters or less

Options
-------

traverse
~~~~~~~~

**type**: ``string`` **default**: ``true``

If this constraint is applied to a property that holds an array of objects,
then each object in that array will be validated only if this option is set
to ``true``.
