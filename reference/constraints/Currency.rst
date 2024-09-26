Currency
========

Validates that a value is a valid `3-letter ISO 4217`_ currency name.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Currency`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CurrencyValidator`
==========  ===================================================================

Basic Usage
-----------

If you want to ensure that the ``currency`` property of an ``Order`` is
a valid currency, you could do the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            #[Assert\Currency]
            protected string $currency;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                currency:
                    - Currency: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="currency">
                    <constraint name="Currency"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Order
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('currency', new Assert\Currency());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid currency.``

This is the message that will be shown if the value is not a valid currency.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`3-letter ISO 4217`: https://en.wikipedia.org/wiki/ISO_4217
