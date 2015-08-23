Language
========

Validates that a value is a valid language *Unicode language identifier*
(e.g. ``fr`` or ``zh-Hant``).

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`                 |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
|                | - `payload`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Language`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\LanguageValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            /**
             * @Assert\Language()
             */
             protected $preferredLanguage;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\User:
            properties:
                preferredLanguage:
                    - Language: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\User">
                <property name="preferredLanguage">
                    <constraint name="Language" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/User.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('preferredLanguage', new Assert\Language());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid language.``

This message is shown if the string is not a valid language code.

.. include:: /reference/constraints/_payload-option.rst.inc
