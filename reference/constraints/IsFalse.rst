IsFalse
=======

Validates that a value is ``false``. Specifically, this checks to see if
the value is exactly ``false``, exactly the integer ``0``, or exactly the
string ``'0'``.

Also see :doc:`IsTrue <IsTrue>`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\IsFalse`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IsFalseValidator`
==========  ===================================================================

Basic Usage
-----------

The ``IsFalse`` constraint can be applied to a property or a "getter" method,
but is most commonly useful in the latter case. For example, suppose that
you want to guarantee that some ``state`` property is *not* in a dynamic
``invalidStates`` array. First, you'd create a "getter" method::

    protected string $state;

    protected array $invalidStates = [];

    public function isStateInvalid(): bool
    {
        return in_array($this->state, $this->invalidStates);
    }

In this case, the underlying object is only valid if the ``isStateInvalid()``
method returns **false**:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\IsFalse(
                message: "You've entered an invalid state."
            )]
            public function isStateInvalid(): bool
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            getters:
                stateInvalid:
                    - 'IsFalse':
                        message: You've entered an invalid state.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <getter property="stateInvalid">
                    <constraint name="IsFalse">
                        <option name="message">You've entered an invalid state.</option>
                    </constraint>
                </getter>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addGetterConstraint('stateInvalid', new Assert\IsFalse([
                    'message' => "You've entered an invalid state.",
                ]));
            }

            public function isStateInvalid(): bool
            {
                // ...
            }
        }

.. include:: /reference/constraints/_null-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be false.``

This message is shown if the underlying data is not false.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
