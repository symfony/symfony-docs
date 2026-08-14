Callback
========

The purpose of the Callback constraint is to create completely custom
validation rules using your own PHP code. You can define one or more
callbacks as closures directly in the properties to validate, or as methods
of the class (or external callables) that add the validation errors themselves.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>` or :ref:`property/method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Callback`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CallbackValidator`
==========  ===================================================================

.. _callback-in-properties:

Callbacks in Properties
-----------------------

The simplest way to use this constraint is to define the callback as a closure
in the attribute of the property to validate (closures can't be defined in YAML
or XML configuration). The closure receives the value of the property and
must return ``true`` or ``false``. When it returns ``false``, Symfony adds a
violation to that property with the text of :ref:`the message option <callback-constraint-message-option>`::

    // src/Entity/Order.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Order
    {
        #[Assert\Callback(
            static function (\DateTimeImmutable $value) {
                return $value > new \DateTimeImmutable('today');
            },
            message: 'The delivery date must be in the future.',
        )]
        public \DateTimeImmutable $deliveryDate;

        // ...
    }

.. versionadded:: 8.2

    The ``message`` option was introduced in Symfony 8.2.

.. note::

    Defining closures inside attributes requires PHP 8.5 or higher. In earlier
    PHP versions, define the callback as a ``public static`` method of the class
    and reference it with an array callable::

        #[Assert\Callback(
            [self::class, 'isFutureDate'],
            message: 'The delivery date must be in the future.',
        )]
        public \DateTimeImmutable $deliveryDate;

If the validation rule involves other properties, the closure can get the object
being validated from the execution context, which is passed as the second
argument::

    // src/Entity/Order.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Context\ExecutionContextInterface;

    class Order
    {
        // ...

        #[Assert\Callback(
            static function (int $value, ExecutionContextInterface $context) {
                return $value >= $context->getObject()->subtotal;
            },
            message: 'The total must be equal to or greater than the subtotal.',
        )]
        public int $total;
    }

The closure can also add its own violations to the execution context, as
explained in the next section. Those violations are independent from the one
added automatically when the closure returns ``false``.

Callbacks in Class Methods
--------------------------

Instead of a closure, you can define the callback as a method of the class,
which is called during the validation process. The method receives an
``ExecutionContextInterface`` object that lets you add violations and
determine to which property they should be attributed:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        class Author
        {
            #[Assert\Callback]
            public function validate(ExecutionContextInterface $context, mixed $payload): void
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            constraints:
                - Callback:
                    callback: validate

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <constraint name="Callback">
                    <option name="callback">validate</option>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new Assert\Callback('validate'));
            }

            public function validate(ExecutionContextInterface $context, mixed $payload): void
            {
                // ...
            }
        }

The callback method itself doesn't *fail* or return any value. Instead, it adds
"violations" to the execution context::

    // ...
    use Symfony\Component\Validator\Context\ExecutionContextInterface;

    class Author
    {
        // ...
        private string $firstName;

        public function validate(ExecutionContextInterface $context, mixed $payload): void
        {
            // somehow you have an array of "fake names"
            $fakeNames = [/* ... */];

            // check if the name is actually a fake name
            if (in_array($this->getFirstName(), $fakeNames)) {
                $context->buildViolation('This name sounds totally fake!')
                    ->atPath('firstName')
                    ->addViolation();
            }
        }
    }

Static Callbacks
----------------

You can also use the constraint with static methods. Since static methods don't
have access to the object instance, they receive the object as the first argument::

    public static function validate(mixed $value, ExecutionContextInterface $context, mixed $payload): void
    {
        // somehow you have an array of "fake names"
        $fakeNames = [/* ... */];

        // check if the name is actually a fake name
        if (in_array($value->getFirstName(), $fakeNames)) {
            $context->buildViolation('This name sounds totally fake!')
                ->atPath('firstName')
                ->addViolation()
            ;
        }
    }

External Callbacks and Closures
-------------------------------

If you want to execute a static callback method that is not located in the
class of the validated object, you can configure the constraint to invoke
an array callable as supported by PHP's :phpfunction:`call_user_func` function.
Suppose your validation function is ``Acme\Validator::validate()``::

    namespace Acme;

    use Symfony\Component\Validator\Context\ExecutionContextInterface;

    class Validator
    {
        public static function validate(mixed $value, ExecutionContextInterface $context, mixed $payload): void
        {
            // ...
        }
    }

You can then use the following configuration to invoke this validator:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Acme\Validator;
        use Symfony\Component\Validator\Constraints as Assert;

        #[Assert\Callback([Validator::class, 'validate'])]
        class Author
        {
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            constraints:
                - Callback:
                    callback: [Acme\Validator, validate]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <constraint name="Callback">
                    <option name="callback">
                        <value>Acme\Validator</value>
                        <value>validate</value>
                    </option>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Acme\Validator;
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addConstraint(new Assert\Callback(
                    callback: [Validator::class, 'validate'],
                ));
            }
        }

.. note::

    The Callback constraint does *not* support global callback functions
    nor is it possible to specify a global function or a service method
    as a callback. To validate using a service, you should
    :doc:`create a custom validation constraint </validation/custom_constraint>`
    and add that new constraint to your class.

When configuring the constraint via PHP, you can also pass a closure to the
constructor of the Callback constraint::

    // src/Entity/Author.php
    namespace App\Entity;

    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\Validator\Context\ExecutionContextInterface;
    use Symfony\Component\Validator\Mapping\ClassMetadata;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata): void
        {
            $callback = function (mixed $value, ExecutionContextInterface $context, mixed $payload): void {
                // ...
            };

            $metadata->addConstraint(new Assert\Callback($callback));
        }
    }

.. warning::

    Using a ``Closure`` together with attribute configuration will disable the
    attribute cache for that class/property/method because ``Closure`` cannot
    be cached. For best performance, it's recommended to use a static callback method.

Options
-------

.. _callback-option:

``callback``
~~~~~~~~~~~~

**type**: ``string``, ``array`` or ``Closure``

The callback option accepts three different formats for specifying the
callback method:

* A **string** containing the name of a concrete or static method;

* An array callable with the format ``['<Class>', '<method>']``;

* A closure.

Concrete callbacks receive an :class:`Symfony\\Component\\Validator\\Context\\ExecutionContextInterface`
instance as the first argument and the :ref:`payload option <reference-constraints-callback-payload>`
as the second argument.

Static or closure callbacks receive the validated object or property value
as the first argument,
the :class:`Symfony\\Component\\Validator\\Context\\ExecutionContextInterface`
instance as the second argument and the :ref:`payload option <reference-constraints-callback-payload>`
as the third argument.

.. include:: /reference/constraints/_groups-option.rst.inc

.. _callback-constraint-message-option:

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

By default, the return value of the callback is ignored. When this option is
set, the callback must return a boolean and a violation with this message is
added when it returns ``false``. This is what allows defining
:ref:`callbacks directly in class properties <callback-in-properties>`.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

.. _reference-constraints-callback-payload:

.. include:: /reference/constraints/_payload-option.rst.inc
