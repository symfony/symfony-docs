CssColor
========

Validates that a value is a valid CSS color. The underlying value is
casted to a string before being validated.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\CssColor`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CssColorValidator`
==========  ===================================================================

Basic Usage
-----------

In the following example, the ``$defaultColor`` value must be a CSS color
defined in any of the valid CSS formats (e.g. ``red``, ``#369``,
``hsla(0, 0%, 20%, 0.4)``); the ``$accentColor`` must be a CSS color defined in
hexadecimal format; and ``$currentColor`` must be a CSS color defined as any of
the named CSS colors:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Bulb.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Bulb
        {
            #[Assert\CssColor]
            protected string $defaultColor;

            #[Assert\CssColor(
                formats: Assert\CssColor::HEX_LONG,
                message: 'The accent color must be a 6-character hexadecimal color.',
            )]
            protected string $accentColor;

            #[Assert\CssColor(
                formats: [Assert\CssColor::BASIC_NAMED_COLORS, Assert\CssColor::EXTENDED_NAMED_COLORS],
                message: 'The color '{{ value }}' is not a valid CSS color name.',
            )]
            protected string $currentColor;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Bulb:
            properties:
                defaultColor:
                    - CssColor: ~
                accentColor:
                    - CssColor:
                        formats: !php/const Symfony\Component\Validator\Constraints\CssColor::HEX_LONG
                        message: The accent color must be a 6-character hexadecimal color.
                currentColor:
                    - CssColor:
                        formats:
                            - !php/const Symfony\Component\Validator\Constraints\CssColor::BASIC_NAMED_COLORS
                            - !php/const Symfony\Component\Validator\Constraints\CssColor::EXTENDED_NAMED_COLORS
                        message: The color "{{ value }}" is not a valid CSS color name.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Bulb">
                <property name="defaultColor">
                    <constraint name="CssColor"/>
                </property>
                <property name="accentColor">
                    <constraint name="CssColor">
                        <option name="formats">hex_long</option>
                        <option name="message">The accent color must be a 6-character hexadecimal color.</option>
                    </constraint>
                </property>
                <property name="currentColor">
                    <constraint name="CssColor">
                        <option name="formats">
                            <value>basic_named_colors</value>
                            <value>extended_named_colors</value>
                        </option>
                        <option name="message">The color "{{ value }}" is not a valid CSS color name.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Bulb.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Bulb
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('defaultColor', new Assert\CssColor());

                $metadata->addPropertyConstraint('accentColor', new Assert\CssColor([
                    'formats' => Assert\CssColor::HEX_LONG,
                    'message' => 'The accent color must be a 6-character hexadecimal color.',
                ]));

                $metadata->addPropertyConstraint('currentColor', new Assert\CssColor([
                    'formats' => [Assert\CssColor::BASIC_NAMED_COLORS, Assert\CssColor::EXTENDED_NAMED_COLORS],
                    'message' => 'The color "{{ value }}" is not a valid CSS color name.',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid CSS color.``

This message is shown if the underlying data is not a valid CSS color.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

formats
~~~~~~~

**type**: ``string`` | ``array``

By default, this constraint considers valid any of the many ways of defining
CSS colors. Use the ``formats`` option to restrict which CSS formats are allowed.
These are the available formats (which are also defined as PHP constants; e.g.
``Assert\CssColor::HEX_LONG``):

* ``hex_long``
* ``hex_long_with_alpha``
* ``hex_short``
* ``hex_short_with_alpha``
* ``basic_named_colors``
* ``extended_named_colors``
* ``system_colors``
* ``keywords``
* ``rgb``
* ``rgba``
* ``hsl``
* ``hsla``

hex_long
........

A regular expression. Allows all values which represent a CSS color of 6
characters (in addition of the leading ``#``) and contained in ranges: ``0`` to
``9`` and ``A`` to ``F`` (case insensitive).

Examples: ``#2F2F2F``, ``#2f2f2f``

hex_long_with_alpha
...................

A regular expression. Allows all values which represent a CSS color with alpha
part of 8 characters (in addition of the leading ``#``) and contained in
ranges: ``0`` to ``9`` and ``A`` to ``F`` (case insensitive).

Examples: ``#2F2F2F80``, ``#2f2f2f80``

hex_short
.........

A regular expression. Allows all values which represent a CSS color of strictly
3 characters (in addition of the leading ``#``) and contained in ranges: ``0``
to ``9`` and ``A`` to ``F`` (case insensitive).

Examples: ``#CCC``, ``#ccc``

hex_short_with_alpha
....................

A regular expression. Allows all values which represent a CSS color with alpha
part of strictly 4 characters (in addition of the leading ``#``) and contained
in ranges: ``0`` to ``9`` and ``A`` to ``F`` (case insensitive).

Examples: ``#CCC8``, ``#ccc8``

basic_named_colors
..................

Any of the valid color names defined in the `W3C list of basic named colors`_
(case insensitive).

Examples: ``black``, ``red``, ``green``

extended_named_colors
.....................

Any of the valid color names defined in the `W3C list of extended named colors`_
(case insensitive).

Examples: ``aqua``, ``brown``, ``chocolate``

system_colors
.............

Any of the valid color names defined in the `CSS WG list of system colors`_
(case insensitive).

Examples: ``LinkText``, ``VisitedText``, ``ActiveText``, ``ButtonFace``, ``ButtonText``

keywords
........

Any of the valid keywords defined in the `CSS WG list of keywords`_ (case insensitive).

Examples: ``transparent``, ``currentColor``

rgb
...

A regular expression. Allows all values which represent a CSS color following
the RGB notation, with or without space between values.

Examples: ``rgb(255, 255, 255)``, ``rgb(255,255,255)``

rgba
....

A regular expression. Allows all values which represent a CSS color with alpha
part following the RGB notation, with or without space between values.

Examples: ``rgba(255, 255, 255, 0.3)``, ``rgba(255,255,255,0.3)``

hsl
...

A regular expression. Allows all values which represent a CSS color following
the HSL notation, with or without space between values.

Examples: ``hsl(0, 0%, 20%)``, ``hsl(0,0%,20%)``

hsla
....

A regular expression. Allows all values which represent a CSS color with alpha
part following the HSLA notation, with or without space between values.

Examples: ``hsla(0, 0%, 20%, 0.4)``, ``hsla(0,0%,20%,0.4)``

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`W3C list of basic named colors`: https://www.w3.org/wiki/CSS/Properties/color/keywords#Basic_Colors
.. _`W3C list of extended named colors`: https://www.w3.org/wiki/CSS/Properties/color/keywords#Extended_colors
.. _`CSS WG list of system colors`: https://drafts.csswg.org/css-color/#css-system-colors
.. _`CSS WG list of keywords`: https://drafts.csswg.org/css-color/#transparent-color
