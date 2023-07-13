When
====

.. versionadded:: 6.2

    The ``When`` constraint was introduced in Symfony 6.2.

This constraint allows you to apply constraints validation only if the
provided expression returns true. See `Basic Usage`_ for an example.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
            or :ref:`property/method <validation-property-target>`
Options     - `expression`_
            - `constraints`_
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

A) If ``type`` is ``percent``, then ``value`` must be less than or equal 100;
B) If ``type`` is ``absolute``, then ``value`` can be anything;
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
            )]
            private ?int $value;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\Discount:
            properties:
                value:
                    - GreaterThan: 0
                    - When:
                        expression: "this.getType() == 'percent'"
                        constraints:
                            - LessThanOrEqual:
                                value: 100
                                message: "The value should be between 1 and 100!"

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">
            <class name="App\Model\Discount">
                <property name="value">
                    <constraint name="GreaterThan">0</constraint>
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
                $metadata->addPropertyConstraint('value', new Assert\When([
                    'expression' => 'this.getType() == "percent"',
                    'constraints' => [
                        new Assert\LessThanOrEqual([
                            'value' => 100,
                            'message' => 'The value should be between 1 and 100!',
                        ]),
                    ],
                ]));
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

**type**: ``string``

The condition written with the expression language syntax that will be evaluated.
If the expression evaluates to a falsey value (i.e. using ``==``, not ``===``),
validation of constraints won't be triggered.

To learn more about the expression language syntax, see
:doc:`/reference/formats/expression_language`.

Depending on how you use the constraint, you have access to 1 or 2 variables
in your expression:

``this``
    The object being validated (e.g. an instance of Discount).
``value``
    The value of the property being validated (only available when
    the constraint is applied to a property).

The ``value`` variable can be used when you want to execute more complex
validation based on its value:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/Discount.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        class Discount
        {
            #[Assert\When(
                expression: 'value == "percent"',
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
                            - Callback: doComplexValidation

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
                $metadata->addPropertyConstraint('type', new Assert\When([
                    'expression' => 'value == "percent"',
                    'constraints' => [
                        new Assert\Callback('doComplexValidation'),
                    ],
                ]));
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

.. versionadded:: 6.4

    Passing a single ``Constraint`` instead of an array was introduced in Symfony 6.4.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

``values``
~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The values of the custom variables used in the expression. Values can be of any
type (numeric, boolean, strings, null, etc.)
