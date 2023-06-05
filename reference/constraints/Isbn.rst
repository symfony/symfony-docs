Isbn
====

This constraint validates that an `International Standard Book Number (ISBN)`_
is either a valid ISBN-10 or a valid ISBN-13.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Isbn`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IsbnValidator`
==========  ===================================================================

Basic Usage
-----------

To use the ``Isbn`` validator, apply it to a property or method
on an object that will contain an ISBN.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Book.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            #[Assert\Isbn(
                type: Assert\Isbn::ISBN_10,
                message: 'This value is not valid.',
            )]
            protected string $isbn;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Book:
            properties:
                isbn:
                    - Isbn:
                        type: isbn10
                        message: This value is not valid.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Book">
                <property name="isbn">
                    <constraint name="Isbn">
                        <option name="type">isbn10</option>
                        <option name="message">This value is not valid.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Book.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Book
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('isbn', new Assert\Isbn([
                    'type' => Assert\Isbn::ISBN_10,
                    'message' => 'This value is not valid.',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Available Options
-----------------

``bothIsbnMessage``
~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is neither a valid ISBN-10 nor a valid ISBN-13.``

The message that will be shown if the `type`_ option is ``null`` and the given
value does not pass any of the ISBN checks.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_groups-option.rst.inc

``isbn10Message``
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-10.``

The message that will be shown if the `type`_ option is ``isbn10`` and the given
value does not pass the ISBN-10 check.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

``isbn13Message``
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-13.``

The message that will be shown if the `type`_ option is ``isbn13`` and the given
value does not pass the ISBN-13 check.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The message that will be shown if the value is not valid. If not ``null``,
this message has priority over all the other messages.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc

``type``
~~~~~~~~

**type**: ``string`` **default**: ``null``

The type of ISBN to validate against. Valid values are ``isbn10``, ``isbn13``
and ``null`` to accept any kind of ISBN.

.. _`International Standard Book Number (ISBN)`: https://en.wikipedia.org/wiki/Isbn
