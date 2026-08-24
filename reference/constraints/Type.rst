Type
====

Validates that a value is of a specific data type. For example, if a variable
should be an array, you can use this constraint with the ``array`` type
option to validate this.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Type`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\TypeValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint should be applied to untyped variables/properties. If a property
or variable is typed and you pass a value of a different type, PHP will throw an
exception before this constraint is checked.

The following example checks if ``emailAddress`` is an instance of ``Symfony\Component\Mime\Address``,
``firstName`` is of type ``string`` (using :phpfunction:`is_string` PHP function),
``age`` is an ``integer`` (using :phpfunction:`is_int` PHP function) and
``accessCode`` contains either only letters or only digits (using
:phpfunction:`ctype_alpha` and :phpfunction:`ctype_digit` PHP functions).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Mime\Address;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Type(Address::class)]
            protected $emailAddress;

            #[Assert\Type('string')]
            protected $firstName;

            #[Assert\Type(
                type: 'integer',
                message: 'The value {{ value }} is not a valid {{ type }}.',
            )]
            protected $age;

            #[Assert\Type(type: ['alpha', 'digit'])]
            protected $accessCode;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                emailAddress:
                    - Type:
                        type: Symfony\Component\Mime\Address

                firstName:
                    - Type:
                        type: string

                age:
                    - Type:
                        type: integer
                        message: The value {{ value }} is not a valid {{ type }}.

                accessCode:
                    - Type:
                        type: [alpha, digit]

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="emailAddress">
                    <constraint name="Type">
                        <option name="type">Symfony\Component\Mime\Address</option>
                    </constraint>
                </property>
                <property name="firstName">
                    <constraint name="Type">
                        <option name="type">string</option>
                    </constraint>
                </property>
                <property name="age">
                    <constraint name="Type">
                        <option name="type">integer</option>
                        <option name="message">The value {{ value }} is not a valid {{ type }}.</option>
                    </constraint>
                </property>
                <property name="accessCode">
                    <constraint name="Type">
                        <option name="type">
                            <value>alpha</value>
                            <value>digit</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Mime\Address;
        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('emailAddress', new Assert\Type(Address::class));

                $metadata->addPropertyConstraint('firstName', new Assert\Type('string'));

                $metadata->addPropertyConstraint('age', new Assert\Type(
                    type: 'integer',
                    message: 'The value {{ value }} is not a valid {{ type }}.',
                ));

                $metadata->addPropertyConstraint('accessCode', new Assert\Type(
                    type: ['alpha', 'digit'],
                ));
            }
        }

.. include:: /reference/constraints/_null-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be of type {{ type }}.``

The message if the underlying data is not of the given type.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ type }}``   The expected type
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

.. _reference-constraint-type-type:

``type``
~~~~~~~~

**type**: ``string`` or ``array``

This required option defines the type or collection of types allowed for the
given value. Each type is either the FQCN (fully qualified class name) of some
PHP class/interface or a valid PHP datatype (checked by PHP's ``is_()`` functions):

* :phpfunction:`bool <is_bool>`
* :phpfunction:`boolean <is_bool>`
* :phpfunction:`int <is_int>`
* :phpfunction:`integer <is_int>`
* :phpfunction:`long <is_int>`
* :phpfunction:`float <is_float>`
* :phpfunction:`double <is_float>`
* :phpfunction:`real <is_float>`
* :phpfunction:`numeric <is_numeric>`
* :phpfunction:`string <is_string>`
* :phpfunction:`scalar <is_scalar>`
* :phpfunction:`array <is_array>`
* :phpfunction:`iterable <is_iterable>`
* :phpfunction:`countable <is_countable>`
* :phpfunction:`callable <is_callable>`
* :phpfunction:`object <is_object>`
* :phpfunction:`resource <is_resource>`
* :phpfunction:`null <is_null>`

If you're dealing with arrays, you can use the following types in the constraint:

* ``list`` which uses :phpfunction:`array_is_list <array_is_list>` internally
* ``associative_array`` which is true for any **non-empty** array that is not a list

Also, you can use ``ctype_*()`` functions from corresponding
`built-in PHP extension`_. Consider `a list of ctype functions`_:

* :phpfunction:`alnum <ctype_alnum>`
* :phpfunction:`alpha <ctype_alpha>`
* :phpfunction:`cntrl <ctype_cntrl>`
* :phpfunction:`digit <ctype_digit>`
* :phpfunction:`graph <ctype_graph>`
* :phpfunction:`lower <ctype_lower>`
* :phpfunction:`print <ctype_print>`
* :phpfunction:`punct <ctype_punct>`
* :phpfunction:`space <ctype_space>`
* :phpfunction:`upper <ctype_upper>`
* :phpfunction:`xdigit <ctype_xdigit>`

Make sure that the proper :phpfunction:`locale <setlocale>` is set before
using one of these.

Finally, you can use aggregated functions:

* ``number``: ``is_int || is_float && !is_nan``
* ``finite-float``: ``is_float && is_finite``
* ``finite-number``: ``is_int || is_float && is_finite``

.. _built-in PHP extension: https://www.php.net/book.ctype
.. _a list of ctype functions: https://www.php.net/ref.ctype
