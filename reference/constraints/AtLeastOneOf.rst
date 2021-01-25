AtLeastOneOf
============

This constraint checks that the value satisfies at least one of the given
constraints. The validation stops as soon as one constraint is satisfied.

.. versionadded:: 5.1

    The ``AtLeastOneOf`` constraint was introduced in Symfony 5.1.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `constraints`_
            - `includeInternalMessages`_
            - `message`_
            - `messageCollection`_
            - `groups`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\AtLeastOneOf`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\AtLeastOneOfValidator`
==========  ===================================================================

Basic Usage
-----------

The following constraints ensure that:

* the ``password`` of a ``Student`` either contains ``#`` or is at least ``10``
  characters long;
* the ``grades`` of a ``Student`` is an array which contains at least ``3``
  elements or that each element is greater than or equal to ``5``.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Student.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Student
        {
            /**
             * @Assert\AtLeastOneOf({
             *     @Assert\Regex("/#/"),
             *     @Assert\Length(min=10)
             * })
             */
            protected $password;

            /**
             * @Assert\AtLeastOneOf({
             *     @Assert\Count(min=3),
             *     @Assert\All(
             *         @Assert\GreaterThanOrEqual(5)
             *     )
             * })
             */
            protected $grades;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Student:
            properties:
                password:
                    - AtLeastOneOf:
                        - Regex: '/#/'
                        - Length:
                            min: 10
                grades:
                    - AtLeastOneOf:
                        - Count:
                            min: 3
                        - All:
                            - GreaterThanOrEqual: 5

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Student">
                <property name="password">
                    <constraint name="AtLeastOneOf">
                        <option name="constraints">
                            <constraint name="Regex">
                                <option name="pattern">/#/</option>
                            </constraint>
                            <constraint name="Length">
                                <option name="min">10</option>
                            </constraint>
                        </option>
                    </constraint>
                </property>
                <property name="grades">
                    <constraint name="AtLeastOneOf">
                        <option name="constraints">
                            <constraint name="Count">
                                <option name="min">3</option>
                            </constraint>
                            <constraint name="All">
                                <option name="constraints">
                                    <constraint name="GreaterThanOrEqual">
                                        5
                                    </constraint>
                                </option>
                            </constraint>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Student.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Student
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('password', new Assert\AtLeastOneOf([
                    'constraints' => [
                        new Assert\Regex(['pattern' => '/#/']),
                        new Assert\Length(['min' => 10]),
                    ],
                ]));

                $metadata->addPropertyConstraint('grades', new Assert\AtLeastOneOf([
                    'constraints' => [
                        new Assert\Count(['min' => 3]),
                        new Assert\All([
                            'constraints' => [
                                new Assert\GreaterThanOrEqual(5),
                            ],
                        ]),
                    ],
                ]));
            }
        }

Options
-------

constraints
~~~~~~~~~~~

**type**: ``array`` [:ref:`default option <validation-default-option>`]

This required option is the array of validation constraints from which at least one of
has to be satisfied in order for the validation to succeed.

includeInternalMessages
~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If set to ``true``, the message that is shown if the validation fails,
will include the list of messages for the internal constraints. See option
`message`_ for an example.

message
~~~~~~~

**type**: ``string`` **default**: ``This value should satisfy at least one of the following constraints:``

This is the intro of the message that will be shown if the validation fails. By default,
it will be followed by the list of messages for the internal constraints
(configurable by `includeInternalMessages`_ option) . For example,
if the above ``grades`` property fails to validate, the message will be
``This value should satisfy at least one of the following constraints:
[1] This collection should contain 3 elements or more.
[2] Each element of this collection should satisfy its own set of constraints.``

messageCollection
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``Each element of this collection should satisfy its own set of constraints.``

This is the message that will be shown if the validation fails
and the internal constraint is either :doc:`/reference/constraints/All`
or :doc:`/reference/constraints/Collection`. See option `message`_ for an example.

.. include:: /reference/constraints/_groups-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc
