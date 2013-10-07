Range
=====

Validates that a given DateTime is *between* some minimum and maximum DateTime.

+----------------+-----------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                       |
+----------------+-----------------------------------------------------------------------------+
| Options        | - `min`_                                                                    |
|                | - `max`_                                                                    |
|                | - `minMessage`_                                                             |
|                | - `maxMessage`_                                                             |
|                | - `invalidMessage`_                                                         |
|                | - `timezone` _                                                              |
+----------------+-----------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\DateTimeRange`          |
+----------------+-----------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateTimeRangeValidator` |
+----------------+-----------------------------------------------------------------------------+

Basic Usage
-----------

To verify that the "date" field of a class is between October 1st 2013 and November 1st 2013, 
you might add the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/EcomBundle/Resources/config/validation.yml
        Acme\EcomBundle\Entity\Order:
            properties:
                date:
                    - DateTimeRange:
                        min: 2013-10-01T00:00:00Z
                        max: 2013-11-01T00:00:00Z
                        minMessage: "Your order date must be on or after October 1st, 2013"
                        maxMessage: "Your order date must be before or on November 1st, 2013"

    .. code-block:: php-annotations

        // src/Acme/EcomBundle/Entity/Order.php
        namespace Acme\EcomBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            /**
             * @Assert\DateTimeRange(
             *      min = "2013-10-01T00:00:00Z",
             *      max = "2013-11-01T00:00:00Z",
             *      minMessage = "Your order date must be on or after October 1st, 2013",
             *      maxMessage = "Your order date must be before or on November 1st, 2013"
             * )
             */
             protected $date;
        }

    .. code-block:: xml

        <!-- src/Acme/EcomBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\EcomBundle\Entity\Order">
                <property name="date">
                    <constraint name="DateTimeRange">
                        <option name="min">2013-10-01T00:00:00Z</option>
                        <option name="max">2013-11-01T00:00:00Z</option>
                        <option name="minMessage">Your order date must be on or after October 1st, 2013</option>
                        <option name="maxMessage">Your order date must be before or on November 1st, 2013</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/EcomBundle/Entity/Order.php
        namespace Acme\EcomBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('height', new Assert\DateTimeRange(array(
                    'min'        => new DateTime('2013-10-01T00:00:00Z'),
                    'max'        => new DateTime('2013-11-01T00:00:00Z'),
                    'minMessage' => 'Your order date must be on or after October 1st, 2013',
                    'maxMessage' => 'Your order date must be before or on November 1st, 2013',
                )));
            }
        }

Options
-------

min
~~~

**type**: ``DateTime|string`` [:ref:`default option<validation-default-option>`]

This required option is the "min" value. Validation will fail if the given
value is **less** than this min value. You may specify this option as a ``DateTime``
or any ``string`` in a format supported by the ``DateTime`` constructor, 
including relative formats.

max
~~~

**type**: ``DateTime|string`` [:ref:`default option<validation-default-option>`]

This required option is the "max" value. Validation will fail if the given
value is **greater** than this max value. You may specify this option as a
``DateTime`` or any ``string`` in a `format supported by the ``DateTime`` constructor`_, 
including relative formats.

timezone
~~~~~~~~

**type**: ``DateTimeZone|string`` **default**: ``UTC``

If the ``timezone`` value is specified, this timezone is used when transforming 
``string`` values and `min`_ `max`_ options to ``DateTime``.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or more.``

The message that will be shown if the underlying value is less than the `min`_
option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be {{ limit }} or less.``

The message that will be shown if the underlying value is more than the `max`_
option.

invalidMessage
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid date.``

The message that will be shown if the underlying value is not a date.

.. _`format supported by the ``DateTime`` constructor`: http://www.php.net/manual/en/datetime.formats.php
