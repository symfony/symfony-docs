Json
====

Validates that a value has valid `JSON`_ syntax.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Json`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\JsonValidator`
==========  ===================================================================

Basic Usage
-----------

The ``Json`` constraint can be applied to a property or a "getter" method:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Book.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            /**
             * @Assert\Json(
             *     message = "You have entered an invalid Json."
             * )
             */
            private $chapters;
        }

    .. code-block:: php-attributes

        // src/Entity/Book.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            #[Assert\Json(
                message: "You have entered an invalid Json."
            )]
            private $chapters;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Book:
            properties:
                chapters:
                    - Json:
                        message: You have entered an invalid Json.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Book">
                <property name="chapters">
                    <constraint name="Json">
                        <option name="message">You have entered an invalid Json.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Book.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Book
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('chapters', new Assert\Json([
                    'message' => 'You\'ve entered an invalid Json.',
                ]));
            }
        }

Options
-------

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be valid JSON.``

This message is shown if the underlying data is not a valid JSON value.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`JSON`: https://en.wikipedia.org/wiki/JSON
