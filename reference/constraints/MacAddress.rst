MacAddress
==========

.. versionadded:: 7.1

    The ``MacAddress`` constraint was introduced in Symfony 7.1.

This constraint ensures that the given value is a valid `MAC address`_ (internally it
uses the ``FILTER_VALIDATE_MAC`` option of the :phpfunction:`filter_var` PHP
function).

==========  =====================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\MacAddress`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\MacAddressValidator`
==========  =====================================================================

Basic Usage
-----------

To use the MacAddress validator, apply it to a property on an object that
will contain a host name.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\MacAddress]
            protected string $mac;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                mac:
                    - MacAddress: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="max">
                    <constraint name="MacAddress"/>
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
                $metadata->addPropertyConstraint('mac', new Assert\MacAddress());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid MAC address.``

This is the message that will be shown if the value is not a valid MAC address.

You can use the following parameters in this message:

===============  ==============================================================
Parameter            Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`MAC address`: https://en.wikipedia.org/wiki/MAC_address
