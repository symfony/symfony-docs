Email
=====

Validates that a value is a valid email address. The underlying value is
cast to a string before being validated.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `mode`_
            - `normalizer`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Email`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\EmailValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Email(
             *     message = "The email '{{ value }}' is not a valid email."
             * )
             */
            protected $email;
        }

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Email(
                message: 'The email {{ value }} is not a valid email.',
            )]
            protected $email;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                email:
                    - Email:
                        message: The email "{{ value }}" is not a valid email.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="email">
                    <constraint name="Email">
                        <option name="message">The email "{{ value }}" is not a valid email.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('email', new Assert\Email([
                    'message' => 'The email "{{ value }}" is not a valid email.',
                ]));
            }
        }

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid email address.``

This message is shown if the underlying data is not a valid email address.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. versionadded:: 5.2

    The ``{{ label }}`` parameter was introduced in Symfony 5.2.

``mode``
~~~~~~~~

**type**: ``string`` **default**: ``loose``

This option is optional and defines the pattern the email address is validated against.
Valid values are:

* ``loose``
* ``strict``
* ``html5``

``loose``
.........

A simple regular expression. Allows all values with an "@" symbol in, and a "."
in the second host part of the email address.

``strict``
..........

Uses the `egulias/email-validator`_ library to perform an RFC compliant
validation. You will need to install that library to use this mode.

``html5``
.........

This matches the pattern used for the `HTML5 email input element`_.

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _egulias/email-validator: https://packagist.org/packages/egulias/email-validator
.. _HTML5 email input element: https://www.w3.org/TR/html5/sec-forms.html#valid-e-mail-address
