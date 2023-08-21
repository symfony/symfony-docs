Email
=====

Validates that a value is a valid email address. The underlying value is
cast to a string before being validated.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Email`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\EmailValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Email(
                message: 'The email {{ value }} is not a valid email.',
            )]
            protected string $email;
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
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
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

.. _reference-constraint-email-mode:

``mode``
~~~~~~~~

**type**: ``string`` **default**: (see below)

This option defines the pattern used to validate the email address. Valid values are:

* ``html5`` uses the regular expression of the `HTML5 email input element`_,
  except it enforces a tld to be present.
* ``html5-allow-no-tld`` uses exactly the same regular expression as the `HTML5 email input element`_,
  making the backend validation consistent with the one provided by browsers.
* ``strict`` validates the address according to `RFC 5322`_ using the
  `egulias/email-validator`_ library (which is already installed when using
  :doc:`Symfony Mailer </mailer>`; otherwise, you must install it separately).

.. versionadded:: 6.2

   The ``html5-allow-no-tld`` mode was introduced in 6.2.

.. tip::

    The possible values of this option are also defined as PHP constants of
    :class:`Symfony\\Component\\Validator\\Constraints\\Email`
    (e.g. ``Email::VALIDATION_MODE_STRICT``).

The default value used by this option is set in the
:ref:`framework.validation.email_validation_mode <reference-validation-email_validation_mode>`
configuration option.

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

.. _egulias/email-validator: https://packagist.org/packages/egulias/email-validator
.. _HTML5 email input element: https://www.w3.org/TR/html5/sec-forms.html#valid-e-mail-address
.. _RFC 5322: https://datatracker.ietf.org/doc/html/rfc5322
