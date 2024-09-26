LessThanOrEqual
===============

Validates that a value is less than or equal to another value, defined in the
options. To force that a value is less than another value, see
:doc:`/reference/constraints/LessThan`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\LessThanOrEqual`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LessThanOrEqualValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* the number of ``siblings`` of a ``Person`` is less than or equal to ``5``
* the ``age`` is less than or equal to ``80``

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\LessThanOrEqual(5)]
            protected int $siblings;

            #[Assert\LessThanOrEqual(
                value: 80,
            )]
            protected int $age;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                siblings:
                    - LessThanOrEqual: 5
                age:
                    - LessThanOrEqual:
                        value: 80

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="siblings">
                    <constraint name="LessThanOrEqual">
                        5
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="LessThanOrEqual">
                        <option name="value">80</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('siblings', new Assert\LessThanOrEqual(5));

                $metadata->addPropertyConstraint('age', new Assert\LessThanOrEqual([
                    'value' => 80,
                ]));
            }
        }

Comparing Dates
---------------

This constraint can be used to compare ``DateTime`` objects against any date
string `accepted by the DateTime constructor`_. For example, you could check
that a date must be today or in the past like this:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\LessThanOrEqual('today')]
            protected \DateTimeInterface $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThanOrEqual: today

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThanOrEqual">today</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('dateOfBirth', new Assert\LessThanOrEqual('today'));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\LessThanOrEqual('today UTC')]
            protected \DateTimeInterface $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThanOrEqual: today UTC

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThanOrEqual">today UTC</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('dateOfBirth', new Assert\LessThanOrEqual('today UTC'));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that a person must be at least 18 years old like this:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            #[Assert\LessThanOrEqual('-18 years')]
            protected \DateTimeInterface $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThanOrEqual: -18 years

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThanOrEqual">-18 years</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Person
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('dateOfBirth', new Assert\LessThanOrEqual('-18 years'));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be less than or equal to {{ compared_value }}.``

This is the message that will be shown if the value is not less than or equal
to the comparison value.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       The upper limit
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. include:: /reference/constraints/_comparison-propertypath-option.rst.inc

.. include:: /reference/constraints/_comparison-value-option.rst.inc

.. _`accepted by the DateTime constructor`: https://www.php.net/manual/en/datetime.formats.php
