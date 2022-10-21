Collection
==========

This constraint is used when the underlying data is a collection (i.e. an
array or an object that implements ``Traversable`` and ``ArrayAccess``),
but you'd like to validate different keys of that collection in different
ways. For example, you might validate the ``email`` key using the ``Email``
constraint and the ``inventory`` key of the collection with the ``Range``
constraint.

This constraint can also make sure that certain collection keys are present
and that extra keys are not present.

.. seealso::

    If you want to validate that all the elements of the collection are unique
    use the :doc:`Unique constraint </reference/constraints/Unique>`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Collection`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\CollectionValidator`
==========  ===================================================================

Basic Usage
-----------

The ``Collection`` constraint allows you to validate the different keys
of a collection individually. Take the following example::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        protected $profileData = [
            'personal_email' => '...',
            'short_bio' => '...',
        ];

        public function setProfileData($key, $value)
        {
            $this->profileData[$key] = $value;
        }
    }

To validate that the ``personal_email`` element of the ``profileData`` array
property is a valid email address and that the ``short_bio`` element is
not blank but is no longer than 100 characters in length, you would do the
following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        // IMPORTANT: nested attributes requires PHP 8.1 or higher
        class Author
        {
            #[Assert\Collection(
                fields: [
                    'personal_email' => new Assert\Email,
                    'short_bio' => [
                        new Assert\NotBlank,
                        new Assert\Length(
                            max: 100,
                            maxMessage: 'Your short bio is too long!'
                        )
                    ]
                ],
                allowMissingFields: true,
            )]
            protected $profileData = [
                'personal_email' => '...',
                'short_bio' => '...',
            ];
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                profileData:
                    - Collection:
                        fields:
                            personal_email:
                                - Email: ~
                            short_bio:
                                - NotBlank: ~
                                - Length:
                                    max:   100
                                    maxMessage: Your short bio is too long!
                        allowMissingFields: true

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="profileData">
                    <constraint name="Collection">
                        <option name="fields">
                            <value key="personal_email">
                                <constraint name="Email"/>
                            </value>
                            <value key="short_bio">
                                <constraint name="NotBlank"/>
                                <constraint name="Length">
                                    <option name="max">100</option>
                                    <option name="maxMessage">Your short bio is too long!</option>
                                </constraint>
                            </value>
                        </option>
                        <option name="allowMissingFields">true</option>
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
                $metadata->addPropertyConstraint('profileData', new Assert\Collection([
                    'fields' => [
                        'personal_email' => new Assert\Email(),
                        'short_bio' => [
                            new Assert\NotBlank(),
                            new Assert\Length([
                                'max' => 100,
                                'maxMessage' => 'Your short bio is too long!',
                            ]),
                        ],
                    ],
                    'allowMissingFields' => true,
                ]));
            }
        }

Presence and Absence of Fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, this constraint validates more than whether or not the
individual fields in the collection pass their assigned constraints. In
fact, if any keys of a collection are missing or if there are any unrecognized
keys in the collection, validation errors will be thrown.

If you would like to allow for keys to be absent from the collection or
if you would like "extra" keys to be allowed in the collection, you can
modify the `allowMissingFields`_ and `allowExtraFields`_ options respectively.
In the above example, the ``allowMissingFields`` option was set to true,
meaning that if either of the ``personal_email`` or ``short_bio`` elements
were missing from the ``$personalData`` property, no validation error would
occur.

Required and Optional Field Constraints
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Constraints for fields within a collection can be wrapped in the ``Required``
or ``Optional`` constraint to control whether they should always be applied
(``Required``) or only applied when the field is present (``Optional``).

For instance, if you want to require that the ``personal_email`` field of
the ``profileData`` array is not blank and is a valid email but the
``alternate_email`` field is optional but must be a valid email if supplied,
you can do the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Collection(
                fields: [
                    'personal_email' => new Assert\Required([
                        new Assert\NotBlank,
                        new Assert\Email,
                    ]),
                    'alternate_email' => new Assert\Optional(
                        new Assert\Email
                    ),
                ],
            )]
            protected $profileData = ['personal_email' => 'email@example.com'];
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                profile_data:
                    - Collection:
                        fields:
                            personal_email:
                                - Required:
                                    - NotBlank: ~
                                    - Email: ~
                            alternate_email:
                                - Optional:
                                    - Email: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="profile_data">
                    <constraint name="Collection">
                        <option name="fields">
                            <value key="personal_email">
                                <constraint name="Required">
                                    <constraint name="NotBlank"/>
                                    <constraint name="Email"/>
                                </constraint>
                            </value>
                            <value key="alternate_email">
                                <constraint name="Optional">
                                    <constraint name="Email"/>
                                </constraint>
                            </value>
                        </option>
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
            protected $profileData = ['personal_email'];

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('profileData', new Assert\Collection([
                    'fields' => [
                        'personal_email'  => new Assert\Required([
                            new Assert\NotBlank(),
                            new Assert\Email(),
                        ]),
                        'alternate_email' => new Assert\Optional(new Assert\Email()),
                    ],
                ]));
            }
        }

Even without ``allowMissingFields`` set to true, you can now omit the ``alternate_email``
property completely from the ``profileData`` array, since it is ``Optional``.
However, if the ``personal_email`` field does not exist in the array,
the ``NotBlank`` constraint will still be applied (since it is wrapped in
``Required``) and you will receive a constraint violation.

When you define groups in nested constraints they are automatically added to
the ``Collection`` constraint itself so it can be traversed for all nested
groups. Take the following example::

    use Symfony\Component\Validator\Constraints as Assert;

    $constraint = new Assert\Collection([
        'fields' => [
            'name' => new Assert\NotBlank(['groups' => 'basic']),
            'email' => new Assert\NotBlank(['groups' => 'contact']),
        ],
    ]);

This will result in the following configuration::

    $constraint = new Assert\Collection([
        'fields' => [
            'name' => new Assert\Required([
                'constraints' => new Assert\NotBlank(['groups' => 'basic']),
                'groups' => ['basic', 'strict'],
            ]),
            'email' => new Assert\Required([
                "constraints" => new Assert\NotBlank(['groups' => 'contact']),
                'groups' => ['basic', 'strict'],
            ]),
        ],
        'groups' => ['basic', 'strict'],
    ]);

The default ``allowMissingFields`` option requires the fields in all groups.
So when validating in ``contact`` group, ``$name`` can be empty but the key is
still required. If this is not the intended behavior, use the ``Optional``
constraint explicitly instead of ``Required``.

Options
-------

``allowExtraFields``
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: false

If this option is set to ``false`` and the underlying collection contains
one or more elements that are not included in the `fields`_ option, a validation
error will be returned. If set to ``true``, extra fields are OK.

``allowMissingFields``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: false

If this option is set to ``false`` and one or more fields from the `fields`_
option are not present in the underlying collection, a validation error
will be returned. If set to ``true``, it's OK if some fields in the `fields`_
option are not present in the underlying collection.

``extraFieldsMessage``
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This field was not expected.``

The message shown if `allowExtraFields`_ is false and an extra field is
detected.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ field }}``  The key of the extra field detected
===============  ==============================================================

``fields``
~~~~~~~~~~

**type**: ``array`` [:ref:`default option <validation-default-option>`]

This option is required and is an associative array defining all of the
keys in the collection and, for each key, exactly which validator(s) should
be executed against that element of the collection.

.. include:: /reference/constraints/_groups-option.rst.inc

``missingFieldsMessage``
~~~~~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This field is missing.``

The message shown if `allowMissingFields`_ is false and one or more fields
are missing from the underlying collection.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ field }}``  The key of the missing field defined in ``fields``
===============  ==============================================================

.. include:: /reference/constraints/_payload-option.rst.inc
