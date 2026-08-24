Twig Constraint
===============

Validates that a given string contains valid :ref:`Twig syntax <twig-language>`.
This is particularly useful when template content is user-generated or
configurable, and you want to ensure it can be rendered by the Twig engine.

.. note::

    Using this constraint requires having the ``symfony/twig-bridge`` package
    installed in your application (e.g. by running ``composer require symfony/twig-bridge``).

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Bridge\\Twig\\Validator\\Constraints\\Twig`
Validator   :class:`Symfony\\Bridge\\Twig\\Validator\\Constraints\\TwigValidator`
==========  ===================================================================

Basic Usage
-----------

Apply the ``Twig`` constraint to validate the contents of any property or the
returned value of any method::

    use Symfony\Bridge\Twig\Validator\Constraints\Twig;

    class Template
    {
        #[Twig]
        private string $templateCode;
    }

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Page.php
        namespace App\Entity;

        use Symfony\Bridge\Twig\Validator\Constraints\Twig;

        class Page
        {
            #[Twig]
            private string $templateCode;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Page:
            properties:
                templateCode:
                    - Symfony\Bridge\Twig\Validator\Constraints\Twig: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Page">
                <property name="templateCode">
                    <constraint name="Symfony\Bridge\Twig\Validator\Constraints\Twig"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Page.php
        namespace App\Entity;

        use Symfony\Bridge\Twig\Validator\Constraints\Twig;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Page
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('templateCode', new Twig());
            }
        }

Constraint Options
------------------

``message``
~~~~~~~~~~~

**type**: ``message`` **default**: ``This value is not a valid Twig template.``

This is the message displayed when the given string does *not* contain valid Twig syntax::

    // ...

    class Page
    {
        #[Twig(message: 'Check this Twig code; it contains errors.')]
        private string $templateCode;
    }

This message has no parameters.

``skipDeprecations``
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If ``true``, Twig deprecation warnings are ignored during validation. Set it to
``false`` to trigger validation errors when the given Twig code contains any deprecations::

    // ...

    class Page
    {
        #[Twig(skipDeprecations: false)]
        private string $templateCode;
    }

This can be helpful when enforcing stricter template rules or preparing for major
Twig version upgrades.
