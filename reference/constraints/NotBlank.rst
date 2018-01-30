NotBlank
========

Validates that a value is not blank - meaning not equal to a blank string,
a blank array or ``null``::

    if (false === $value || (empty($value) && '0' != $value)) {
        // validation will fail
    }

To force that a value is simply not equal to ``null``, see the
:doc:`/reference/constraints/NotNull` constraint.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotBlank`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotBlankValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``firstName`` property of an ``Author``
class were not blank, you could do the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank()
             */
            protected $firstName;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                firstName:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="firstName">
                    <constraint name="NotBlank" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotBlank());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be blank.``

This is the message that will be shown if the value is blank.

.. include:: /reference/constraints/_payload-option.rst.inc
