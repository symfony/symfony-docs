CssColor
=========

Validates that a value is a valid CSS color. The underlying value is
casted to a string before being validated.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `mode`_
            - `normalizer`_
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
             *     message = "The color '{{ value }}' is not a valid hexadecimal color."
             * )
             */
            protected $currentColor;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Bulb:
            properties:
                currentColor:
                    - CssColor:
                        message: The color "{{ value }}" is not a valid hexadecimal color.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Bulb">
                <property name="currentColor">
                    <constraint name="CssColor">
                        <option name="message">The color "{{ value }}" is not a valid hexadecimal color.</option>
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
                $metadata->addPropertyConstraint('currentColor', new Assert\CssColor([
                    'message' => 'The color "{{ value }}" is not a valid hexadecimal color.',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid hexadecimal color.``

This message is shown if the underlying data is not a valid hexadecimal color.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
===============  ==============================================================

mode
~~~~

**type**: ``string`` **default**: ``loose``

This option is optional and defines the pattern the hexadecimal color is validated against.
Valid values are:

* ``long``
* ``short``
* ``named_colors``
* ``html5``

long
....

A regular expression. Allows all values which represent a hexadecimal color
of 8 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

short
.....

A simple regular expression. Allows all values which represent a hexadecimal color
of strictly 3 or 4 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

named_colors
............

Accordingly to the `W3C list of named colors`_, it allows to use color by their names.

html5
.....

As well as the HTML5 color input, this mode allows all values of strictly 6 characters (in addition of the leading ``#``) and contained in ranges: 0 to 9 and A to F (case insensitive).

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`W3C list of named colors`: https://www.w3.org/TR/css-color-4/#named-color
