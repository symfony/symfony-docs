CssColor
=========

Validates that a value is a valid CSS color. The underlying value is
casted to a string before being validated.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `formats`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\CssColor`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CssColorValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Bulb.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Bulb
        {
            /**
             * @Assert\CssColor(
             *     formats = { Assert\CssColor::HEX_LONG }
             *     message = "The color '{{ value }}' is not a valid CSS color."
             * )
             */
            protected $defaultColor;

            /**
             * @Assert\CssColor(
             *     formats = Assert\CssColor::BASIC_NAMED_COLORS
             *     message = "The color '{{ value }}' is not a valid CSS color."
             * )
             */
            protected $currentColor;
        }

    .. code-block:: php-attributes

        // src/Entity/Bulb.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Bulb
        {
            #[Assert\CssColor(
                formats: [Assert\CssColor::HEX_LONG]
                message: 'The color '{{ value }}' is not a valid CSS color.',
            )]
            protected $defaultColor;

            #[Assert\CssColor(
                formats: Assert\CssColor::BASIC_NAMED_COLORS
                message: 'The color '{{ value }}' is not a valid CSS color.',
            )]
            protected $currentColor;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Bulb:
            properties:
                defaultColor:
                    - CssColor:
                        formats: [ !php/const Symfony\Component\Validator\Constraints\CssColor::HEX_LONG ]
                        message: The color "{{ value }}" is not a valid CSS color.
                currentColor:
                    - CssColor:
                        formats: !php/const Symfony\Component\Validator\Constraints\CssColor::BASIC_NAMED_COLORS
                        message: The color "{{ value }}" is not a valid CSS color.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Bulb">
                <property name="currentColor">
                    <constraint name="CssColor">
                        <option name="formats">
                            <value>hex_long</value>
                        </option>
                        <option name="message">The color "{{ value }}" is not a valid CSS color.</option>
                    </constraint>
                </property>
                <property name="defaultColor">
                    <constraint name="CssColor">
                        <option name="formats">basic_named_colors</option>
                        <option name="message">The color "{{ value }}" is not a valid CSS color.</option>
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
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('defaultColor', new Assert\CssColor([
                    'formats' => [Assert\CssColor::HEX_LONG],
                    'message' => 'The color "{{ value }}" is not a valid CSS color.',
                ]));

                $metadata->addPropertyConstraint('currentColor', new Assert\CssColor([
                    'formats' => Assert\CssColor::BASIC_NAMED_COLORS,
                    'message' => 'The color "{{ value }}" is not a valid CSS color.',
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

This option is optional and defines the pattern the CSS color is validated against.
Valid values are:

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

A regular expression. Allows all values which represent a CSS color
of 6 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

Examples: ``#2F2F2F``, ``#2f2f2f``

hex_long_with_alpha
...................

A regular expression. Allows all values which represent a CSS color with alpha part
of 8 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

Examples: ``#2F2F2F80``, ``#2f2f2f80``

hex_short
.........

A regular expression. Allows all values which represent a CSS color
of strictly 3 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

Examples: ``#CCC``, ``#ccc``

hex_short_with_alpha
....................

A regular expression. Allows all values which represent a CSS color with alpha part
of strictly 4 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

Examples: ``#CCC8``, ``#ccc8``

basic_named_colors
..................

Accordingly to the `W3C list of basic named colors`_, it allows to use colors by their names (case insensitive).

Examples: ``black``, ``red``, ``green``

extended_named_colors
.....................

Accordingly to the `W3C list of extended named colors`_, it allows to use colors by their names (case insensitive).

Examples: ``aqua``, ``brown``, ``chocolate``

system_colors
.............

Accordingly to the `CSS WG list of system colors`_, it allows to use colors by their names (case insensitive).

Examples: ``LinkText``, ``VisitedText``, ``ActiveText``, ``ButtonFace``, ``ButtonText``

keywords
........

Accordingly to the `CSS WG list of keywords`_, it allows to use colors by their names (case insensitive).

Examples: ``transparent``, ``currentColor``

rgb
...

A regular expression. Allows all values which represent a CSS color following th RGB notation, with or without space between values.

Examples: ``rgb(255, 255, 255)``, ``rgb(255,255,255)``

rgba
....

A regular expression. Allows all values which represent a CSS color with alpha part following th RGB notation, with or without space between values.

Examples: ``rgba(255, 255, 255, 0.3)``, ``rgba(255,255,255,0.3)``

hsl
...

A regular expression. Allows all values which represent a CSS color following th HSL notation, with or without space between values.

Examples: ``hsl(0, 0%, 20%)``, ``hsl(0,0%,20%)``

hsla
....

A regular expression. Allows all values which represent a CSS color with alpha part following th HSLA notation, with or without space between values.

Examples: ``hsla(0, 0%, 20%, 0.4)``, ``hsla(0,0%,20%,0.4)``

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`W3C list of basic named colors`: https://www.w3.org/wiki/CSS/Properties/color/keywords#Basic_Colors
.. _`W3C list of extended named colors`: https://www.w3.org/wiki/CSS/Properties/color/keywords#Extended_colors
.. _`CSS WG list of system colors`: https://drafts.csswg.org/css-color/#css-system-colors
.. _`CSS WG list of keywords`: https://drafts.csswg.org/css-color/#transparent-color
