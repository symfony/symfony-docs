Null
====

Validates that a value is exactly equal to ``null``. To force that a property
is simply blank (blank string or ``null``), see the  :doc:`/reference/constraints/Blank`
constraint. To ensure that a property is not null, see :doc:`/reference/constraints/NotNull`.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Null`             |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\NullValidator`    |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If, for some reason, you wanted to ensure that the ``firstName`` property
of an ``Author`` class exactly equal to ``null``, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                firstName:
                    - 'Null': ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Null()
             */
            protected $firstName;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="Acme\BlogBundle\Entity\Author">
                <property name="firstName">
                    <constraint name="Null" />
                </property>
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
                $metadata->addPropertyConstraint('firstName', Assert\Null());
            }
        }

.. caution::

    When using YAML, be sure to surround ``Null`` with quotes (``'Null'``)
    or else YAML will convert this into a ``null`` value.

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be null.``

This is the message that will be shown if the value is not ``null``.
