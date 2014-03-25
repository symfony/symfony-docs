Callback
========

.. versionadded:: 2.4
    The ``Callback`` constraint was simplified in Symfony 2.4. For usage
    examples with older Symfony versions, see the corresponding versions of this
    documentation page.

The purpose of the Callback constraint is to create completely custom
validation rules and to assign any validation errors to specific fields on
your object. If you're using validation with forms, this means that you can
make these custom errors display next to a specific field, instead of simply
at the top of your form.

This process works by specifying one or more *callback* methods, each of
which will be called during the validation process. Each of those methods
can do anything, including creating and assigning validation errors.

.. note::

    A callback method itself doesn't *fail* or return any value. Instead,
    as you'll see in the example, a callback method has the ability to directly
    add validator "violations".

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`class <validation-class-target>`                                 |
+----------------+------------------------------------------------------------------------+
| Options        | - :ref:`callback <callback-option>`                                    |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Callback`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CallbackValidator` |
+----------------+------------------------------------------------------------------------+

Configuration
-------------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            constraints:
                - Callback: [validate]

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\ExecutionContextInterface;

        class Author
        {
            /**
             * @Assert\Callback
             */
            public function validate(ExecutionContextInterface $context)
            {
                // ...
            }
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <constraint name="Callback">validate</constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Callback('validate'));
            }
        }

The Callback Method
-------------------

The callback method is passed a special ``ExecutionContextInterface`` object. You
can set "violations" directly on this object and determine to which field
those errors should be attributed::

    // ...
    use Symfony\Component\Validator\ExecutionContextInterface;

    class Author
    {
        // ...
        private $firstName;

        public function validate(ExecutionContextInterface $context)
        {
            // somehow you have an array of "fake names"
            $fakeNames = array(/* ... */);

            // check if the name is actually a fake name
            if (in_array($this->getFirstName(), $fakeNames)) {
                $context->addViolationAt(
                    'firstName',
                    'This name sounds totally fake!',
                    array(),
                    null
                );
            }
        }
    }

Static Callbacks
----------------

You can also use the constraint with static methods. Since static methods don't
have access to the object instance, they receive the object as the first argument::

    public static function validate($object, ExecutionContextInterface $context)
    {
        // somehow you have an array of "fake names"
        $fakeNames = array(/* ... */);

        // check if the name is actually a fake name
        if (in_array($object->getFirstName(), $fakeNames)) {
            $context->addViolationAt(
                'firstName',
                'This name sounds totally fake!',
                array(),
                null
            );
        }
    }

External Callbacks and Closures
-------------------------------

If you want to execute a static callback method that is not located in the class
of the validated object, you can configure the constraint to invoke an array
callable as supported by PHP's :phpfunction:`call_user_func` function. Suppose
your validation function is ``Vendor\Package\Validator::validate()``::

    namespace Vendor\Package;

    use Symfony\Component\Validator\ExecutionContextInterface;

    class Validator
    {
        public static function validate($object, ExecutionContextInterface $context)
        {
            // ...
        }
    }

You can then use the following configuration to invoke this validator:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            constraints:
                - Callback: [Vendor\Package\Validator, validate]

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @Assert\Callback({"Vendor\Package\Validator", "validate"})
         */
        class Author
        {
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <constraint name="Callback">
                    <value>Vendor\Package\Validator</value>
                    <value>validate</value>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Callback(array(
                    'Vendor\Package\Validator',
                    'validate',
                )));
            }
        }

.. note::

    The Callback constraint does *not* support global callback functions nor
    is it possible to specify a global function or a :term:`service` method
    as callback. To validate using a service, you should
    :doc:`create a custom validation constraint </cookbook/validation/custom_constraint>`
    and add that new constraint to your class.

When configuring the constraint via PHP, you can also pass a closure to the
constructor of the Callback constraint::

    // src/Acme/BlogBundle/Entity/Author.php
    namespace Acme\BlogBundle\Entity;

    use Symfony\Component\Validator\Mapping\ClassMetadata;
    use Symfony\Component\Validator\Constraints as Assert;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $callback = function ($object, ExecutionContextInterface $context) {
                // ...
            };

            $metadata->addConstraint(new Assert\Callback($callback));
        }
    }

Options
-------

.. _callback-option:

callback
~~~~~~~~

**type**: ``string``, ``array`` or ``Closure`` [:ref:`default option <validation-default-option>`]

The callback option accepts three different formats for specifying the
callback method:

* A **string** containing the name of a concrete or static method;

* An array callable with the format ``array('<Class>', '<method>')``;

* A closure.

Concrete callbacks receive an :class:`Symfony\\Component\\Validator\\ExecutionContextInterface`
instance as only argument.

Static or closure callbacks receive the validated object as the first argument
and the :class:`Symfony\\Component\\Validator\\ExecutionContextInterface`
instance as the second argument.
