GreaterThanOrEqual
==================

.. versionadded:: 2.3
    The ``GreaterThanOrEqual`` constraint was introduced in Symfony 2.3.

Validates that a value is greater than or equal to another value, defined in
the options. To force that a value is greater than another value, see
:doc:`/reference/constraints/GreaterThan`.

+----------------+----------------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                            |
+----------------+----------------------------------------------------------------------------------+
| Options        | - `value`_                                                                       |
|                | - `message`_                                                                     |
+----------------+----------------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanOrEqual`          |
+----------------+----------------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanOrEqualValidator` |
+----------------+----------------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is greater than
or equal to ``18``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - GreaterThanOrEqual:
                        value: 18

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\GreaterThanOrEqual(
             *     value = 18
             * )
             */
            protected $age;
        }

    .. code-block:: xml

        <!-- src/Acme/SocialBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\SocialBundle\Entity\Person">
                <property name="age">
                    <constraint name="GreaterThanOrEqual">
                        <option name="value">18</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('age', new Assert\GreaterThanOrEqual(array(
                    'value' => 18,
                )));
            }
        }

Comparing Dates
---------------

.. versionadded:: 2.6
    The feature to compare dates was added in Symfony 2.6.

This constraint can be used to compare ``DateTime`` objects against any date
string `accepted by the DateTime constructor`_. For example, you could check
that a date must at least be the current day:

.. configuration-block::

    .. code-block:: yaml

        # src/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThanOrEqual: today

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThanOrEqual("today")
             */
            protected $deliveryDate;
        }

    .. code-block:: xml

        <!-- src/Acme/OrderBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\OrderBundle\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThanOrEqual">today</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThanOrEqual('today'));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: yaml

        # src/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThanOrEqual: today UTC

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThanOrEqual("today UTC")
             */
            protected $deliveryDate;
        }

    .. code-block:: xml

        <!-- src/Acme/OrderBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\OrderBundle\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThanOrEqual">today UTC</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThanOrEqual('today UTC'));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that the above delivery date starts at least five hours after the
current time:

.. configuration-block::

    .. code-block:: yaml

        # src/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThanOrEqual: +5 hours

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThanOrEqual("+5 hours")
             */
            protected $deliveryDate;
        }

    .. code-block:: xml

        <!-- src/Acme/OrderBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\OrderBundle\Entity\Order">
                <property name="deliveryDate">
                    <constraint name="GreaterThanOrEqual">+5 hours</constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThanOrEqual('+5 hours'));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be greater than or equal to {{ compared_value }}.``

This is the message that will be shown if the value is not greater than or equal
to the comparison value.

.. _`accepted by the DateTime constructor`: http://www.php.net/manual/en/datetime.formats.php
