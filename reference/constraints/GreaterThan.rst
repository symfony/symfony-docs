GreaterThan
===========

.. versionadded:: 2.3
    The ``GreaterThan`` constraint was introduced in Symfony 2.3.

Validates that a value is greater than another value, defined in the options. To
force that a value is greater than or equal to another value, see
:doc:`/reference/constraints/GreaterThanOrEqual`. To force a value is less
than another value, see :doc:`/reference/constraints/LessThan`.

+----------------+---------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                     |
+----------------+---------------------------------------------------------------------------+
| Options        | - `value`_                                                                |
|                | - `message`_                                                              |
+----------------+---------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThan`          |
+----------------+---------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\GreaterThanValidator` |
+----------------+---------------------------------------------------------------------------+

Basic Usage
-----------

If you want to ensure that the ``age`` of a ``Person`` class is greater than
``18``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/SocialBundle/Resources/config/validation.yml
        Acme\SocialBundle\Entity\Person:
            properties:
                age:
                    - GreaterThan:
                        value: 18

    .. code-block:: php-annotations

        // src/Acme/SocialBundle/Entity/Person.php
        namespace Acme\SocialBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Person
        {
            /**
             * @Assert\GreaterThan(
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
                    <constraint name="GreaterThan">
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
                $metadata->addPropertyConstraint('age', new Assert\GreaterThan(array(
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
that a date must at least be the next day:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: today

    .. code-block:: php-annotations

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("today")
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
                    <constraint name="GreaterThan">today</constraint>
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
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('today'));
            }
        }

Be aware that PHP will use the server's configured timezone to interpret these
dates. If you want to fix the timezone, append it to the date string:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: today UTC

    .. code-block:: php-annotations

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("today UTC")
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
                    <constraint name="GreaterThan">today UTC</constraint>
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
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('today UTC'));
            }
        }

The ``DateTime`` class also accepts relative dates or times. For example, you
can check that the above delivery date starts at least five hours after the
current time:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/OrderBundle/Resources/config/validation.yml
        Acme\OrderBundle\Entity\Order:
            properties:
                deliveryDate:
                    - GreaterThan: +5 hours

    .. code-block:: php-annotations

        // src/Acme/OrderBundle/Entity/Order.php
        namespace Acme\OrderBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\GreaterThan("+5 hours")
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
                    <constraint name="GreaterThan">+5 hours</constraint>
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
                $metadata->addPropertyConstraint('deliveryDate', new Assert\GreaterThan('+5 hours'));
            }
        }

Options
-------

.. include:: /reference/constraints/_comparison-value-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be greater than {{ compared_value }}.``

This is the message that will be shown if the value is not greater than the
comparison value.

.. _`accepted by the DateTime constructor`: http://www.php.net/manual/en/datetime.formats.php
