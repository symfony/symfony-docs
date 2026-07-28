When
====

This constraint allows you to apply constraints validation only if the
provided expression returns true. See `Basic Usage`_ for an example.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
            or :ref:`property/method <validation-property-target>`
Options     - `expression`_
            - `constraints`_
            _ `otherwise`_
            - `groups`_
            - `payload`_
            - `values`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\When`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\WhenValidator`
==========  ===================================================================

Basic Usage
-----------

Imagine you have a class ``Discount`` with ``type`` and ``value``
properties::

    // src/Model/Discount.php
    namespace App\Model;

    class Discount
    {
        private ?string $type;

        private ?int $value;

        // ...

        public function getType(): ?string
        {
            return $this->type;
        }

        public function getValue(): ?int
        {
            return $this->value;
        }
    }

To validate the object, you have some requirements:

A) If ``type`` is ``percent``, then ``value`` must be less than or equal to 100;
B) If ``type`` is not ``percent``, then ``value`` must be less than 9999;
C) No matter the value of ``type``, the ``value`` must be greater than 0.

One way to accomplish this is with the When constraint:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/Discount.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;

        class Discount
        {
            #[Assert\GreaterThan(0)]
            #[Assert\When(
                expression: 'this.getType() == "percent"',
                constraints: [
                    new Assert\LessThanOrEqual(100, message: 'The value should be between 1 and 100!')
                ],
                otherwise: [
                    new Assert\LessThan(9999, message: 'The value should be less than 9999!')
                ],
            )]
            private ?int $value;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\Discount:
            properties:
                value:
                    - GreaterThan:
                        value: 0
                    - When:
                        expression: "this.getType() == 'percent'"
                        constraints:
                            - LessThanOrEqual:
                                value: 100
                                message: "The value should be between 1 and 100!"
                        otherwise:
                            - LessThan:
                                value: 9999
                                message: "The value should be less than 9999!"

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">
            <class name="App\Model\Discount">
                <property name="value">
                    <constraint name="GreaterThan">
                        <option name="value">0</option>
                    </constraint>
                    <constraint name="When">
                        <option name="expression">
                            this.getType() == 'percent'
                        </option>
                        <option name="constraints">
                            <constraint name="LessThanOrEqual">
                                <option name="value">100</option>
                                <option name="message">The value should be between 1 and 100!</option>
                            </constraint>
                        </option>
                        <option name="otherwise">
                            <constraint name="LessThan">
                                <option name="value">9999</option>
                                <option name="message">The value should be less than 9999!</option>
                            </constraint>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Model/Discount.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Discount
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('value', new Assert\GreaterThan(0));
                $metadata->addPropertyConstraint('value', new Assert\When(
                    expression: 'this.getType() == "percent"',
                    constraints: [
                        new Assert\LessThanOrEqual(
                            value: 100,
                            message: 'The value should be between 1 and 100!',
                        ),
                    ],
                    otherwise: [
                        new Assert\LessThan(
                            value: 9999,
                            message: 'The value should be less than 9999!',
                        ),
                    ],
                ));
            }

            // ...
        }

The `expression`_ option is the expression that must return true in order
to trigger the validation of the attached constraints. To learn more about
the expression language syntax, see :doc:`/reference/formats/expression_language`.

For more information about the expression and what variables are available
to you, see the `expression`_ option details below.

Options
-------

``expression``
~~~~~~~~~~~~~~

**type**: ``string|Closure``

The condition evaluated to decide if the constraint is applied or not. It can be
defined as a closure or a string using the :doc:`expression language syntax </reference/formats/expression_language>`.
If the result is a falsey value (``false``, ``null``, ``0``, an empty string or
an empty array) the constraints defined in the ``constraints`` option won't be
applied but the constraints defined in ``otherwise`` option (if provided) will be applied.

**When using an expression**, you have access to the following variables:

``this``
    The object being validated (e.g. an instance of Discount).
``value``
    Either the object being validated (when the constraint is applied to a class),
    the value of the property being validated (when applied to a property),
    or the :doc:`raw value </validation/raw_values>`.
``context``
    The :class:`Symfony\\Component\\Validator\\Context\\ExecutionContextInterface`
    object that provides information such as the currently validated class, the
    name of the currently validated property, the list of violations, etc.

**When using a closure**, the first argument is the object being validated.

.. note::

    The support for closures in the ``expression`` option requires PHP 8.5.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/Discount.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        class Discount
        {
            // either using an expression...
            #[Assert\When(
                expression: 'value == "percent"',
                constraints: [new Assert\Callback('doComplexValidation')],
            )]

            // ... or using a closure
            #[Assert\When(
                expression: static function (Discount $discount) {
                    return $discount->getType() === 'percent';
                },
                constraints: [new Assert\Callback('doComplexValidation')],
            )]
            private ?string $type;

            // ...

            public function doComplexValidation(ExecutionContextInterface $context, $payload): void
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\Discount:
            properties:
                type:
                    - When:
                        expression: "value == 'percent'"
                        constraints:
                            - Callback:
                                callback: doComplexValidation

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">
            <class name="App\Model\Discount">
                <property name="type">
                    <constraint name="When">
                        <option name="expression">
                            value == 'percent'
                        </option>
                        <option name="constraints">
                            <constraint name="Callback">
                                <option name="callback">doComplexValidation</option>
                            </constraint>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Model/Discount.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Discount
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('type', new Assert\When(
                    expression: 'value == "percent"',
                    constraints: [
                        new Assert\Callback('doComplexValidation'),
                    ],
                ));
            }

            public function doComplexValidation(ExecutionContextInterface $context, $payload): void
            {
                // ...
            }
        }

You can also pass custom variables using the `values`_ option.

``constraints``
~~~~~~~~~~~~~~~

**type**: ``array|Constraint``

One or multiple constraints that are applied if the expression returns true.

``otherwise``
~~~~~~~~~~~~~

**type**: ``array|Constraint``

One or multiple constraints that are applied if the expression returns false.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

``values``
~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The values of the custom variables used in the expression. Values can be of any
type (numeric, boolean, strings, null, etc.)
