Count
=====

Validates that a given collection's (i.e. an array or an object that implements
Countable) element count is *between* some minimum and maximum value.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `min`_                                                            |
|                | - `max`_                                                            |
|                | - `minMessage`_                                                     |
|                | - `maxMessage`_                                                     |
|                | - `exactMessage`_                                                   |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Count`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CountValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

To verify that the ``emails`` array field contains between 1 and 5 elements
you might add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Participant.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            /**
             * @Assert\Count(
             *      min = "1",
             *      max = "5",
             *      minMessage = "You must specify at least one email",
             *      maxMessage = "You cannot specify more than {{ limit }} emails"
             * )
             */
             protected $emails = array();
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Participant:
            properties:
                emails:
                    - Count:
                        min: 1
                        max: 5
                        minMessage: 'You must specify at least one email'
                        maxMessage: 'You cannot specify more than {{ limit }} emails'

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Participant">
                <property name="emails">
                    <constraint name="Count">
                        <option name="min">1</option>
                        <option name="max">5</option>
                        <option name="minMessage">You must specify at least one email</option>
                        <option name="maxMessage">You cannot specify more than {{ limit }} emails</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Participant.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Participant
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('emails', new Assert\Count(array(
                    'min'        => 1,
                    'max'        => 5,
                    'minMessage' => 'You must specify at least one email',
                    'maxMessage' => 'You cannot specify more than {{ limit }} emails',
                )));
            }
        }

Options
-------

min
~~~

**type**: ``integer``

This required option is the "min" count value. Validation will fail if the
given collection elements count is **less** than this min value.

max
~~~

**type**: ``integer``

This required option is the "max" count value. Validation will fail if the
given collection elements count is **greater** than this max value.

minMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or more.``

The message that will be shown if the underlying collection elements count
is less than the `min`_ option.

maxMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain {{ limit }} elements or less.``

The message that will be shown if the underlying collection elements count
is more than the `max`_ option.

exactMessage
~~~~~~~~~~~~

**type**: ``string`` **default**: ``This collection should contain exactly {{ limit }} elements.``

The message that will be shown if min and max values are equal and the underlying
collection elements count is not exactly this value.

.. include:: /reference/constraints/_payload-option.rst.inc
