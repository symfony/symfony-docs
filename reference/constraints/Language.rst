Language
========

Validates that a value is a valid language *Unicode language identifier*
(e.g. ``fr`` or ``zh-Hant``).

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `alpha3`_
            - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Language`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\LanguageValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\Language
             */
            protected $preferredLanguage;
        }

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\Language]
            protected $preferredLanguage;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                preferredLanguage:
                    - Language: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="preferredLanguage">
                    <constraint name="Language"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('preferredLanguage', new Assert\Language());
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

alpha3
~~~~~~

.. versionadded:: 5.1

    The ``alpha3`` option was introduced in Symfony 5.1.

**type**: ``boolean`` **default**: ``false``

If this option is ``true``, the constraint checks that the value is a
`ISO 639-2`_ three-letter code (e.g. French = ``fra``) instead of the default
`ISO 639-1`_ two-letter code (e.g. French = ``fr``).

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid language.``

This message is shown if the string is not a valid language code.

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

.. _`ISO 639-1`: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
.. _`ISO 639-2`: https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes
