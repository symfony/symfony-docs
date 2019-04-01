GreaterThan
===========

Validates that a value is greater than another value, defined in the options. To
force that a value is greater than or equal to another value, see
:doc:`/reference/constraints/GreaterThanOrEqual`. To force a value is less
than another value, see :doc:`/reference/constraints/LessThan`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
            - `propertyPath`_
            - `value`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThan`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* the number of ``siblings`` of a ``Person`` is greater than ``5``
* the ``age`` of a ``Person`` class is greater than ``18``

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Person.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\GreaterThan(5)
             */
            protected $siblings;

            /**
             * @Assert\GreaterThan(
             *     value = 18
             * )
             */
            protected $age;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Person:
            properties:
                siblings:
                    - GreaterThan: 5
                age:
                    - GreaterThan:
                        value: 18

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Person">
                <property name="siblings">
                    <constraint name="GreaterThan">
                        5
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="GreaterThan">
                        <option name="value">18</option>
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
                $metadata->addPropertyConstraint('siblings', new Assert\GreaterThan(5));

                $metadata->addPropertyConstraint('age', new Assert\GreaterThan([
                    'value' => 18,
                ]));
            }
        }

Comparing Dates
---------------

This constraint can be used to compare ``DateTime`` objects against any date
string `accepted by the DateTime constructor`_. For example, you could check
that a date must at least be the next day:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("today")
             */
            protected $deliveryDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: today

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThan">today</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('today'));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("today UTC")
             */
            protected $deliveryDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: today UTC

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThan">today UTC</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('today UTC'));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that the above delivery date starts at least five hours after the
current time:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("+5 hours")
             */
            protected $deliveryDate;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: +5 hours

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThan">+5 hours</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('+5 hours'));
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be greater than {{ compared_value }}.``

This is the message that will be shown if the value is not greater than the
comparison value.

You can use the following parameters in this message:

=============================  ================================================
Parameter                      Description
=============================  ================================================
``{{ compared_value }}``       The lower limit
``{{ compared_value_type }}``  The expected value type
``{{ value }}``                The current (invalid) value
=============================  ================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. include:: /reference/constraints/_comparison-propertypath-option.rst.inc

.. include:: /reference/constraints/_comparison-value-option.rst.inc

.. _`accepted by the DateTime constructor`: https://php.net/manual/en/datetime.formats.php
