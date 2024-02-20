NotNull
=======

Validates that a value is not strictly equal to ``null``. To ensure that
a value is not blank (not a blank string), see the  :doc:`/reference/constraints/NotBlank`
constraint.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\NotNull`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\NotNullValidator`
==========  ===================================================================

Basic Usage
-----------

If you wanted to ensure that the ``firstName`` property of an ``Author``
class were not strictly equal to ``null``, you would:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotNull]
            protected string $firstName;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                firstName:
                    - NotNull: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="firstName">
                    <constraint name="NotNull"/>
                </property>
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
                $metadata->addPropertyConstraint('firstName', new Assert\NotNull());
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should not be null.``

This is the message that will be shown if the value is ``null``.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
