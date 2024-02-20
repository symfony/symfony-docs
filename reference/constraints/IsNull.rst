IsNull
======

Validates that a value is exactly equal to ``null``. To force that a property
is blank (blank string or ``null``), see the  :doc:`/reference/constraints/Blank`
constraint. To ensure that a property is not null, see :doc:`/reference/constraints/NotNull`.

Also see :doc:`NotNull <NotNull>`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\IsNull`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IsNullValidator`
==========  ===================================================================

Basic Usage
-----------

If, for some reason, you wanted to ensure that the ``firstName`` property
of an ``Author`` class exactly equal to ``null``, you could do the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\IsNull]
            protected ?string $firstName = null;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                firstName:
                    - 'IsNull': ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="firstName">
                    <constraint name="IsNull"/>
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
                $metadata->addPropertyConstraint('firstName', Assert\IsNull());
            }
        }

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be null.``

This is the message that will be shown if the value is not ``null``.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
