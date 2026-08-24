MacAddress
==========

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
can contain a MAC address:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Device.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Device
        {
            #[Assert\MacAddress]
            protected string $mac;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Device:
            properties:
                mac:
                    - MacAddress: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Device">
                <property name="max">
                    <constraint name="MacAddress"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Device.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Device
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

.. _reference-constraint-mac-address-type:

``type``
~~~~~~~~

**type**: ``string`` **default**: ``all``

This option defines the kind of MAC addresses that are allowed. There are a lot
of different possible values based on your needs:

================================  =========================================
Parameter                         Allowed MAC addresses
================================  =========================================
``all``                           All
``all_no_broadcast``              All except broadcast
``broadcast``                     Only broadcast
``local_all``                     Only local
``local_multicast_no_broadcast``  Only local and multicast except broadcast
``local_multicast``               Only local and multicast
``local_no_broadcast``            Only local except broadcast
``local_unicast``                 Only local and unicast
``multicast_all``                 Only multicast
``multicast_no_broadcast``        Only multicast except broadcast
``unicast_all``                   Only unicast
``universal_all``                 Only universal
``universal_unicast``             Only universal and unicast
``universal_multicast``           Only universal and multicast
================================  =========================================

.. _`MAC address`: https://en.wikipedia.org/wiki/MAC_address
