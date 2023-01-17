Callback
========

The purpose of the Callback constraint is to create completely custom
validation rules and to assign any validation errors to specific fields
on your object. If you are using validation with forms, this means that
instead of displaying custom errors at the top of the form, you can
display them next to the field they apply to.

This process works by specifying one or more *callback* methods, each of
which will be called during the validation process. Each of those methods
can do anything, including creating and assigning validation errors.

.. note::

    A callback method itself doesn't *fail* or return any value. Instead,
    as you will see in the example, a callback method has the ability to directly
    add validator "violations".

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>` or :ref:`property/method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Callback`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CallbackValidator`
==========  ===================================================================

Configuration
-------------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        class Author
        {
            /**
             * @Assert\Callback
             */
            public function validate(ExecutionContextInterface $context, $payload)
            {
                // ...
            }
        }

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Context\ExecutionContextInterface;

        class Author
        {
            #[Assert\Callback]
            public function validate(ExecutionContextInterface $context, $payload)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            constraints:
                - Callback: validate

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <constraint name="Callback">validate</constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Callback('validate'));
            }

            public function validate(ExecutionContextInterface $context, $payload)
            {
                // ...
            }
        }

The Callback Method
-------------------

The callback method is passed a special ``ExecutionContextInterface`` object.
You can set "violations" directly on this object and determine to which
field those errors should be attributed::

    // ...
    use Symfony\Component\Validator\Context\ExecutionContextInterface;

    class Author
    {
        // ...
        private $firstName;

        public function validate(ExecutionContextInterface $context, $payload)
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

    public static function validate($object, ExecutionContextInterface $context, $payload)
    {
        // somehow you have an array of "fake names"
        $fakeNames = [/* ... */];

        // check if the name is actually a fake name
        if (in_array($object->getFirstName(), $fakeNames)) {
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
        public static function validate($object, ExecutionContextInterface $context, $payload)
        {
            // ...
        }
    }

You can then use the following configuration to invoke this validator:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @Assert\Callback({"Acme\Validator", "validate"})
         */
        class Author
        {
        }

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
                - Callback: [Acme\Validator, validate]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <constraint name="Callback">
                    <value>Acme\Validator</value>
                    <value>validate</value>
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Callback([
                    Validator::class,
                    'validate',
                ]));
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
        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $callback = function ($object, ExecutionContextInterface $context, $payload) {
                // ...
            };

            $metadata->addConstraint(new Assert\Callback($callback));
        }
    }

.. warning::

    Using a ``Closure`` together with annotation configuration will disable the
    annotation cache for that class/property/method because ``Closure`` cannot
    be cached. For best performance, it is recommended to use a static callback method.

Options
-------

.. _callback-option:

``callback``
~~~~~~~~~~~~

**type**: ``string``, ``array`` or ``Closure`` [:ref:`default option <validation-default-option>`]

The callback option accepts three different formats for specifying the
callback method:

* A **string** containing the name of a concrete or static method;

* An array callable with the format ``['<Class>', '<method>']``;

* A closure.

Concrete callbacks receive an :class:`Symfony\\Component\\Validator\\Context\\ExecutionContextInterface`
instance as only argument.

Static or closure callbacks receive the validated object as the first argument
and the :class:`Symfony\\Component\\Validator\\Context\\ExecutionContextInterface`
instance as the second argument.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
