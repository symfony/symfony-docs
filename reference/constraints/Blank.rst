Blank
=====

Validates that a value is blank, defined as equal to a blank string or equal
to ``null``. To force that a value strictly be equal to ``null``, see the
:doc:`/reference/constraints/IsNull` constraint. To force that a value is
*not* blank, see :doc:`/reference/constraints/NotBlank`.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Blank`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\BlankValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

If, for some reason, you wanted to ensure that the ``firstName`` property
of an ``Author`` class were blank, you could do the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Blank()
             */
            protected $firstName;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                firstName:
                    - Blank: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="Blank" />
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
                $metadata->addPropertyConstraint('firstName', new Assert\Blank());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be blank.``

This is the message that will be shown if the value is not blank.

.. include:: /reference/constraints/_payload-option.rst.inc
