NotNull
=======

Validates that a value is not strictly equal to ``null``. To ensure that
a value is simply not blank (not a blank string), see the  :doc:`/reference/constraints/NotBlank`
constraint.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
|                | - `payload`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\NotNull`          |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NotNullValidator` |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If you wanted to ensure that the ``firstName`` property of an ``Author``
class were not strictly equal to ``null``, you would:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotNull()
             */
            protected $firstName;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                firstName:
                    - NotNull: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="NotNull" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('firstName', new Assert\NotNull());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should not be null.``

This is the message that will be shown if the value is ``null``.

.. include:: /reference/constraints/_payload-option.rst.inc
