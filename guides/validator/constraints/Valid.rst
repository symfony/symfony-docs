Valid
=====

Validates an associated object.

.. code-block:: yaml

    properties:
        address:
            - Valid: ~

Options
-------

  * ``class``: The expected class of the object
  * ``message``: The error message if the class doesn't match

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

    .. code-block:: php

        // Application/HelloBundle/Address.php
        class Author
        {
            /**
             * @validation:NotBlank()
             */
            protected $street;

            /**
             * @validation:NotBlank()
             * @validation:MaxLength(5)
             */
            protected $zipCode;
        }

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank()
             * @validation:MinLength(4)
             */
            protected $firstName;

            /**
             * @validation:NotBlank()
             */
            protected $lastName;
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

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Valid()
             */
            protected $address;
        }

We can even go one step further and validate the class of the related object
to be ``Address`` or one of its subclasses.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                address:
                    - Valid: { class: Application\ḨelloBundle\Address }

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="address">
                <constraint name="Valid">Application\HelloBundle\Address</constraint>
            </property>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Valid(class = "Application\HelloBundle\Address")
             */
            protected $address;
        }
