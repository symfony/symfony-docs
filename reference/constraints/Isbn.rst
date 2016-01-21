Isbn
====

.. versionadded:: 2.3
    The Isbn constraint was introduced in Symfony 2.3.

.. caution::

    The ``isbn10`` and ``isbn13`` options are deprecated since Symfony 2.5
    and will be removed in Symfony 3.0. Use the ``type`` option instead.
    Furthermore, when using the ``type`` option, lowercase characters are no
    longer supported starting in Symfony 2.5, as they are not allowed in ISBNs.

This constraint validates that an `International Standard Book Number (ISBN)`_
is either a valid ISBN-10 or a valid ISBN-13.

+----------------+----------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                |
+----------------+----------------------------------------------------------------------+
| Options        | - `type`_                                                            |
|                | - `message`_                                                         |
|                | - `isbn10Message`_                                                   |
|                | - `isbn13Message`_                                                   |
|                | - `bothIsbnMessage`_                                                 |
|                | - `payload`_                                                         |
+----------------+----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Isbn`            |
+----------------+----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\IsbnValidator`   |
+----------------+----------------------------------------------------------------------+

Basic Usage
-----------

To use the ``Isbn`` validator, simply apply it to a property or method
on an object that will contain an ISBN.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Book.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            /**
             * @Assert\Isbn(
             *     type = isbn10,
             *     message: This value is not  valid.
             * )
             */
            protected $isbn;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Book:
            properties:
                isbn:
                    - Isbn:
                        type: isbn10
                        message: This value is not  valid.


    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Book">
                <property name="isbn">
                    <constraint name="Isbn">
                        <option name="type">isbn10</option>
                        <option name="message">This value is not  valid.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Book.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Book
        {
            protected $isbn;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('isbn', new Assert\Isbn(array(
                    'type'    => isbn10,
                    'message' => 'This value is not valid.'
                )));
            }
        }

Available Options
-----------------

type
~~~~

**type**: ``string`` **default**: ``null``

The type of ISBN to validate against. Valid values are ``isbn10``, ``isbn13``
and ``null`` to accept any kind of ISBN.

message
~~~~~~~

**type**: ``string`` **default**: ``null``

The message that will be shown if the value is not valid. If not ``null``,
this message has priority over all the other messages.

isbn10Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-10.``

The message that will be shown if the `type`_ option is ``isbn10`` and the given
value does not pass the ISBN-10 check.

isbn13Message
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid ISBN-13.``

The message that will be shown if the `type`_ option is ``isbn13`` and the given
value does not pass the ISBN-13 check.

bothIsbnMessage
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is neither a valid ISBN-10 nor a valid ISBN-13.``

The message that will be shown if the `type`_ option is ``null`` and the given
value does not pass any of the ISBN checks.

.. include:: /reference/constraints/_payload-option.rst.inc

.. _`International Standard Book Number (ISBN)`: https://en.wikipedia.org/wiki/Isbn
