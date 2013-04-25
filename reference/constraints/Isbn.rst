Isbn
====

.. versionadded:: New in version 2.3:
    The Isbn validation were added in Symfony 2.3.

This constraint permits that a ISBN (International Standard Book Numbers)
number is either a valid ISBN-10, a valid ISBN-13 code or both on a value.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                |
+----------------+----------------------------------------------------------------------+
| Options        | - `isbn10Message`_                                                   |
|                | - `isbn13Message`_                                                   |
|                | - `bothIsbnMessage`_                                                 |
|                | - `isbn10`_                                                          |
|                | - `isbn13`_                                                          |
+----------------+----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Isbn`            |
+----------------+----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IsbnValidator`   |
+----------------+----------------------------------------------------------------------+

Basic Usage
-----------

To use the ``Isbn`` validator, simply apply it to a property or method
on an  object that will contain a ISBN number.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BookcaseBunlde/Resources/config/validation.yml
        Acme\BookcaseBunlde\Entity\Book:
            properties:
                isbn:
                    - Isbn:
                        isbn10: true
                        isbn13: true
                        bothIsbnMessage: This value is neither a valid ISBN-10 nor a valid ISBN-13.

    .. code-block:: xml

        <!-- src/Acme/BookcaseBunlde/Resources/config/validation.xml -->
        <class name="Acme\BookcaseBunlde\Entity\Book">
            <property name="isbn">
                <constraint name="Isbn">
                    <option name="isbn10">true</option>
                    <option name="isbn13">true</option>
                    <option name="bothIsbnMessage">This value is neither a valid ISBN-10 nor a valid ISBN-13.</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/BookcaseBunlde/Entity/Book.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            /**
             * @Assert\Isbn(
             *     isbn10 = true,
             *     isbn13 = true,
             *     bothIsbnMessage = "This value is neither a valid ISBN-10 nor a valid ISBN-13."
             * )
             */
            protected $isbn;
        }

    .. code-block:: php

        // src/Acme/BookcaseBunlde/Entity/Book.php
        namespace Acme\BookcaseBunlde\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            protected $isbn;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('isbn', new Assert\Isbn(array(
                    'isbn10'          => true,
                    'isbn13'          => true,
                    'bothIsbnMessage' => 'This value is neither a valid ISBN-10 nor a valid ISBN-13.'
                )));
            }
        }

Available Options
-----------------

isbn10Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-10.``

The message that will be shown if the option isbn10 is true
and the given value does not pass the ISBN-10 check.

isbn13Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-13.``

The message that will be shown if the option isbn13 is true
and the given value does not pass the ISBN-13 check.

bothIsbnMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is neither a valid ISBN-10 nor a valid ISBN-13.``

The message that will be shown if the options (isbn10, isbn13) is true
and the given value does not pass the ISBN-13 nor ISBN-13 check.

isbn10
~~~~~~

**type**: ``boolean`` [:ref:`default option<validation-default-option>`]

If this required option is set to ``true`` the constraint will check
if the code is a valid ISBN-10 code.

isbn13
~~~~~~

**type**: ``boolean`` [:ref:`default option<validation-default-option>`]

If this required option is set to ``true`` the constraint will check
if the code is a valid ISBN-13 code.
