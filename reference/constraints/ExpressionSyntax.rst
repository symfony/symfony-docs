ExpressionSyntax
================

This constraint checks that the value is valid as an `ExpressionLanguage`_
expression.

.. versionadded:: 6.1

    This constraint was introduced in Symfony 6.1 and deprecates the previous
    :doc:`ExpressionLanguageSyntax </reference/constraints/ExpressionLanguageSyntax>`
    constraint.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionSyntax`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionSyntaxValidator`
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
            #[Assert\ExpressionSyntax]
            protected $promotion;

            #[Assert\ExpressionSyntax(
                allowedVariables: ['user', 'shipping_centers'],
            )]
            protected $shippingOptions;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Order:
            properties:
                promotion:
                    - ExpressionSyntax: ~
                shippingOptions:
                    - ExpressionSyntax:
                        allowedVariables: ['user', 'shipping_centers']

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Order">
                <property name="promotion">
                    <constraint name="ExpressionSyntax"/>
                </property>
                <property name="shippingOptions">
                    <constraint name="ExpressionSyntax">
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('promotion', new Assert\ExpressionSyntax());

                $metadata->addPropertyConstraint('shippingOptions', new Assert\ExpressionSyntax([
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
