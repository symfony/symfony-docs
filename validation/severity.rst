How to Handle Different Error Levels
====================================

Sometimes, you may want to display constraint validation error messages differently
based on some rules. For example, you have a registration form for new users
where they enter some personal information and choose their authentication
credentials. They would have to choose a username and a secure password,
but providing bank account information would be optional. Nonetheless, you
want to make sure that these optional fields, if entered, are still valid,
but display their errors differently.

The process to achieve this behavior consists of two steps:

#. Apply different error levels to the validation constraints;
#. Customize your error messages depending on the configured error level.

1. Assigning the Error Level
----------------------------

Use the ``payload`` option to configure the error level for each constraint:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class User
        {
            #[Assert\NotBlank(payload: ['severity' => 'error'])]
            protected string $username;

            #[Assert\NotBlank(payload: ['severity' => 'error'])]
            protected string $password;

            #[Assert\Iban(payload: ['severity' => 'warning'])]
            protected string $bankAccountNumber;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\User:
            properties:
                username:
                    - NotBlank:
                        payload:
                            severity: error
                password:
                    - NotBlank:
                        payload:
                            severity: error
                bankAccountNumber:
                    - Iban:
                        payload:
                            severity: warning

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\User">
                <property name="username">
                    <constraint name="NotBlank">
                        <option name="payload">
                            <value key="severity">error</value>
                        </option>
                    </constraint>
                </property>
                <property name="password">
                    <constraint name="NotBlank">
                        <option name="payload">
                            <value key="severity">error</value>
                        </option>
                    </constraint>
                </property>
                <property name="bankAccountNumber">
                    <constraint name="Iban">
                        <option name="payload">
                            <value key="severity">warning</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/User.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class User
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('username', new Assert\NotBlank([
                    'payload' => ['severity' => 'error'],
                ]));
                $metadata->addPropertyConstraint('password', new Assert\NotBlank([
                    'payload' => ['severity' => 'error'],
                ]));
                $metadata->addPropertyConstraint('bankAccountNumber', new Assert\Iban([
                    'payload' => ['severity' => 'warning'],
                ]));
            }
        }

2. Customize the Error Message Template
---------------------------------------

When validation of the ``User`` object fails, you can retrieve the constraint
that caused a particular failure using the
:method:`Symfony\\Component\\Validator\\ConstraintViolation::getConstraint`
method. Each constraint exposes the attached payload as a public property::

    // a constraint validation failure, instance of
    // Symfony\Component\Validator\ConstraintViolation
    $constraintViolation = ...;
    $constraint = $constraintViolation->getConstraint();
    $severity = $constraint->payload['severity'] ?? null;

For example, you can leverage this to customize the ``form_errors`` block
so that the severity is added as an additional HTML class:

.. code-block:: html+twig

    {%- block form_errors -%}
        {%- if errors|length > 0 -%}
        <ul>
            {%- for error in errors -%}
                <li class="{{ error.cause.constraint.payload.severity ?? '' }}">{{ error.message }}</li>
            {%- endfor -%}
        </ul>
        {%- endif -%}
    {%- endblock form_errors -%}

.. seealso::

    For more information on customizing form rendering, see :doc:`/form/form_customization`.
