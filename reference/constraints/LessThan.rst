LessThan
========

Validates that a value is less than another value, defined in the options. To
force that a value is less than or equal to another value, see
:doc:`/reference/constraints/LessThanOrEqual`. To force a value is greater
than another value, see :doc:`/reference/constraints/GreaterThan`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
            - `propertyPath`_
            - `value`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\LessThan`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LessThanValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* the number of ``siblings`` of a ``Person`` is less than ``5``
* ``age`` is less than ``80``

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\LessThan(5)
             */
            protected $siblings;

            /**
             * @Assert\LessThan(
             *     value = 80
             * )
             */
            protected $age;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                siblings:
                    - LessThan: 5
                age:
                    - LessThan:
                        value: 80

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="siblings">
                    <constraint name="LessThan">
                        5
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="LessThan">
                        <option name="value">80</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('siblings', new Assert\LessThan(5));

                $metadata->addPropertyConstraint('age', new Assert\LessThan([
                    'value' => 80,
                ]));
            }
        }

Comparing Dates
---------------

This constraint can be used to compare ``DateTime`` objects against any date
string `accepted by the DateTime constructor`_. For example, you could check
that a date must be in the past like this:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\LessThan("today")
             */
            protected $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThan: today

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThan">today</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('dateOfBirth', new Assert\LessThan('today'));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\LessThan("today UTC")
             */
            protected $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThan: today UTC

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThan">today UTC</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\LessThan('today UTC'));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that a person must be at least 18 years old like this:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\LessThan("-18 years")
             */
            protected $dateOfBirth;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                dateOfBirth:
                    - LessThan: -18 years

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="dateOfBirth">
                    <constraint name="LessThan">-18 years</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('dateOfBirth', new Assert\LessThan('-18 years'));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be less than {{ compared_value }}.``

This is the message that will be shown if the value is not less than the
comparison value.

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

.. _`accepted by the DateTime constructor`: https://php.net/manual/en/datetime.formats.php
