Hostname
========

This constraint ensures that the given value is a valid host name (internally it
uses the ``FILTER_VALIDATE_DOMAIN`` option of the :phpfunction:`filter_var` PHP
function).

.. versionadded:: 5.1

    The ``Hostname`` constraint was introduced in Symfony 5.1.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
            - `requireTld`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Hostname`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\HostnameValidator`
==========  ===================================================================

Basic Usage
-----------

To use the Hostname validator, apply it to a property on an object that
will contain a host name.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/ServerSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class ServerSettings
        {
            /**
             * @Assert\Hostname(message="The server name must be a valid hostname.")
             */
            protected $name;
        }

    .. code-block:: php-attributes

        // src/Entity/ServerSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class ServerSettings
        {
            #[Assert\Hostname(message: 'The server name must be a valid hostname.')]
            protected $name;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\ServerSettings:
            properties:
                name:
                    - Hostname:
                        message: The server name must be a valid hostname.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\ServerSettings">
                <property name="name">
                    <constraint name="Hostname">
                        <option name="message">The server name must be a valid hostname.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/ServerSettings.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class ServerSettings
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new Assert\Hostname([
                    'message' => 'The server name must be a valid hostname.',
                ]));
            }
        }

The following top-level domains (TLD) are reserved according to `RFC 2606`_ and
that's why hostnames containing them are not considered valid: ``.example``,
``.invalid``, ``.localhost``, and ``.test``.

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid hostname.``

The default message supplied when the value is not a valid hostname.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. versionadded:: 5.2

    The ``{{ label }}`` parameter was introduced in Symfony 5.2.

.. include:: /reference/constraints/_payload-option.rst.inc

``requireTld``
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

By default, hostnames are considered valid only when they are fully qualified
and include their TLDs (top-level domain names). For instance, ``example.com``
is valid but ``example`` is not.

Set this option to ``false`` to not require any TLD in the hostnames.

.. note::

    This constraint does not validate that the given TLD value is included in
    the `list of official top-level domains`_ (because that list is growing
    continuously and it's hard to keep track of it).

.. _`RFC 2606`: https://tools.ietf.org/html/rfc2606
.. _`list of official top-level domains`: https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
