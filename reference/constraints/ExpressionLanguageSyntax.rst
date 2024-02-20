ExpressionLanguageSyntax
========================

.. deprecated:: 6.1

    This constraint is deprecated since Symfony 6.1. Instead, use the
    :doc:`ExpressionSyntax </reference/constraints/ExpressionSyntax>` constraint.

This constraint checks that the value is valid as an `ExpressionLanguage`_
expression.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntax`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntaxValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* the ``promotion`` property stores a value which is valid as an
  ExpressionLanguage expression;
* the ``shippingOptions`` property also ensures that the expression only uses
  certain variables.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Order.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Order
        {
            #[Assert\ExpressionLanguageSyntax]
            protected string  $promotion;

            #[Assert\ExpressionLanguageSyntax(
                allowedVariables: ['user', 'shipping_centers'],
            )]
            protected string $shippingOptions;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                promotion:
                    - ExpressionLanguageSyntax: ~
                shippingOptions:
                    - ExpressionLanguageSyntax:
                        allowedVariables: ['user', 'shipping_centers']

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="promotion">
                    <constraint name="ExpressionLanguageSyntax"/>
                </property>
                <property name="shippingOptions">
                    <constraint name="ExpressionLanguageSyntax">
                        <option name="allowedVariables">
                            <value>user</value>
                            <value>shipping_centers</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Student.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Order
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('promotion', new Assert\ExpressionLanguageSyntax());

                $metadata->addPropertyConstraint('shippingOptions', new Assert\ExpressionLanguageSyntax([
                    'allowedVariables' => ['user', 'shipping_centers'],
                ]));
            }
        }

Options
-------

allowedVariables
~~~~~~~~~~~~~~~~

**type**: ``array`` or ``null`` **default**: ``null``

If this option is defined, the expression can only use the variables whose names
are included in this option. Unset this option or set its value to ``null`` to
allow any variables.

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be a valid expression.``

This is the message displayed when the validation fails.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`ExpressionLanguage`: https://symfony.com/components/ExpressionLanguage
