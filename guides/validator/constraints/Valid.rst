Valid
=====

Marks an associated object to be validated itself.

.. code-block:: yaml

    properties:
        address:
            - Valid: ~

Example: Validate object graphs
-------------------------------

This constraint helps to validate whole object graphs. In the following example,
we create two classes ``Author`` and ``Address`` that both have constraints on
their properties. Furthermore, ``Author`` stores an ``Address`` instance in the
``$address`` property.

.. code-block:: php

    // Application/HelloBundle/Address.php
    class Address
    {
        protected $street;
        protected $zipCode;
    }

.. code-block:: php

    // Application/HelloBundle/Author.php
    class Author
    {
        protected $firstName;
        protected $lastName;
        protected $address;
    }

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Address:
            properties:
                street:
                    - NotBlank: ~
                zipCode:
                    - NotBlank: ~
                    - MaxLength: 5

        Application\HelloBundle\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - MinLength: 4
                lastName:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Address">
            <property name="street">
                <constraint name="NotBlank" />
            </property>
            <property name="zipCode">
                <constraint name="NotBlank" />
                <constraint name="MaxLength">5</constraint>
            </property>
        </class>

        <class name="Application\HelloBundle\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">4</constraint>
            </property>
            <property name="lastName">
                <constraint name="NotBlank" />
            </property>
        </class>

    .. code-block:: php-annotations

        // Application/HelloBundle/Address.php
        class Address
        {
            /**
             * @validation:NotBlank()
             */
            protected $street;

            /**
             * @validation:NotBlank
             * @validation:MaxLength(5)
             */
            protected $zipCode;
        }

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank
             * @validation:MinLength(4)
             */
            protected $firstName;

            /**
             * @validation:NotBlank
             */
            protected $lastName;
            
            protected $address;
        }

    .. code-block:: php

        // Application/HelloBundle/Address.php
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

        // Application/HelloBundle/Author.php
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

With this mapping it is possible to successfully validate an author with an
invalid address. To prevent that, we add the ``Valid`` constraint to the
``$address`` property.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                address:
                    - Valid: ~

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="address">
                <constraint name="Valid" />
            </property>
        </class>

    .. code-block:: php-annotations

        // Application/HelloBundle/Author.php
        class Author
        {
            /* ... */
            
            /**
             * @validation:Valid
             */
            protected $address;
        }

    .. code-block:: php

        // Application/HelloBundle/Author.php
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

    Application\HelloBundle\Author.address.zipCode:
        This value is too long. It should have 5 characters or less
