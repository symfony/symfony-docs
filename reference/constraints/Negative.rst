Negative
========

.. versionadded:: 4.3

    The ``Negative`` constraint was introduced in Symfony 4.3.

Validates that a value is a negative number. To force that a value is a negative
number or equal to zero, see :doc:`/reference/constraints/NegativeOrZero`.
To force a value is positive, see :doc:`/reference/constraints/Positive`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Negative`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LesserThanValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraint ensure that:

* the ``withdraw`` of a  bankaccount ``TransferItem`` is a negative number (lesser than zero)

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/TransferItem.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class TransferItem
        {
            /**
             * @Assert\Negative
             */
            protected $withdraw;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\TransferItem:
            properties:
                withdraw:
                    - Negative

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\TransferItem">
                <property name="withdraw">
                    <constraint name="Negative"></constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/TransferItem.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class TransferItem
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('withdraw', new Assert\Negative();
            }
        }
