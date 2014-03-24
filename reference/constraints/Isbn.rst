Isbn
====

.. versionadded:: 2.3
    The Isbn constraint was introduced in Symfony 2.3.

This constraint validates that an `International Standard Book Number (ISBN)`_
is either a valid ISBN-10, a valid ISBN-13 or both.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                |
+----------------+----------------------------------------------------------------------+
| Options        | - `isbn10`_                                                          |
|                | - `isbn13`_                                                          |
|                | - `isbn10Message`_                                                   |
|                | - `isbn13Message`_                                                   |
|                | - `bothIsbnMessage`_                                                 |
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

        # src/Acme/BookcaseBundle/Resources/config/validation.yml
        Acme\BookcaseBundle\Entity\Book:
            properties:
                isbn:
                    - Isbn:
                        isbn10: true
                        isbn13: true
                        bothIsbnMessage: This value is neither a valid ISBN-10 nor a valid ISBN-13.

    .. code-block:: php-annotations

        // src/Acme/BookcaseBundle/Entity/Book.php
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

    .. code-block:: xml

        <!-- src/Acme/BookcaseBundle/Resources/config/validation.xml -->
        <class name="Acme\BookcaseBundle\Entity\Book">
            <property name="isbn">
                <constraint name="Isbn">
                    <option name="isbn10">true</option>
                    <option name="isbn13">true</option>
                    <option name="bothIsbnMessage">This value is neither a valid ISBN-10 nor a valid ISBN-13.</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/BookcaseBundle/Entity/Book.php
        namespace Acme\BookcaseBundle\Entity;

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

isbn10
~~~~~~

**type**: ``boolean``

If this required option is set to ``true`` the constraint will check if the
code is a valid ISBN-10 code.

isbn13
~~~~~~

**type**: ``boolean``

If this required option is set to ``true`` the constraint will check if the
code is a valid ISBN-13 code.

isbn10Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-10.``

The message that will be shown if the `isbn10`_ option is true and the given
value does not pass the ISBN-10 check.

isbn13Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-13.``

The message that will be shown if the `isbn13`_ option is true and the given
value does not pass the ISBN-13 check.

bothIsbnMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is neither a valid ISBN-10 nor a valid ISBN-13.``

The message that will be shown if both the `isbn10`_ and `isbn13`_ options
are true and the given value does not pass the ISBN-13 nor the ISBN-13 check.

.. _`International Standard Book Number (ISBN)`: http://en.wikipedia.org/wiki/Isbn
