Collection
==========

This constraint is used when the underlying data is a collection (i.e. an
array or an object that implements ``Traversable`` and ``ArrayAccess``),
but you'd like to validate different keys of that collection in different
ways. For example, you might validate the ``email`` key using the ``Email``
constraint and the ``inventory`` key of the collection with the ``Range`` constraint.

This constraint can also make sure that certain collection keys are present
and that extra keys are not present.

+----------------+--------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                    |
+----------------+--------------------------------------------------------------------------+
| Options        | - `fields`_                                                              |
|                | - `allowExtraFields`_                                                    |
|                | - `extraFieldsMessage`_                                                  |
|                | - `allowMissingFields`_                                                  |
|                | - `missingFieldsMessage`_                                                |
+----------------+--------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Collection`          |
+----------------+--------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CollectionValidator` |
+----------------+--------------------------------------------------------------------------+

Basic Usage
-----------

The ``Collection`` constraint allows you to validate the different keys of
a collection individually. Take the following example::

    // src/Acme/BlogBundle/Entity/Author.php
    namespace Acme\BlogBundle\Entity;

    class Author
    {
        protected $profileData = array(
            'personal_email',
            'short_bio',
        );

        public function setProfileData($key, $value)
        {
            $this->profileData[$key] = $value;
        }
    }

To validate that the ``personal_email`` element of the ``profileData`` array
property is a valid email address and that the ``short_bio`` element is not
blank but is no longer than 100 characters in length, you would do the following:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                profileData:
                    - Collection:
                        fields:
                            personal_email: Email
                            short_bio:
                                - NotBlank
                                - Length:
                                    max:   100
                                    maxMessage: Your short bio is too long!
                        allowMissingFields: true

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Collection(
             *     fields = {
             *         "personal_email" = @Assert\Email,
             *         "short_bio" = {
             *             @Assert\NotBlank(),
             *             @Assert\Length(
             *                 max = 100,
             *                 maxMessage = "Your bio is too long!"
             *             )
             *         }
             *     },
             *     allowMissingFields = true
             * )
             */
             protected $profileData = array(
                 'personal_email',
                 'short_bio',
             );
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="profileData">
                <constraint name="Collection">
                    <option name="fields">
                        <value key="personal_email">
                            <constraint name="Email" />
                        </value>
                        <value key="short_bio">
                            <constraint name="NotBlank" />
                            <constraint name="Length">
                                <option name="max">100</option>
                                <option name="maxMessage">Your bio is too long!</option>
                            </constraint>
                        </value>
                    </option>
                    <option name="allowMissingFields">true</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            private $options = array();

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('profileData', new Assert\Collection(array(
                    'fields' => array(
                        'personal_email' => new Assert\Email(),
                        'lastName' => array(
                            new Assert\NotBlank(),
                            new Assert\Length(array("max" => 100)),
                        ),
                    ),
                    'allowMissingFields' => true,
                )));
            }
        }

Presence and Absence of Fields
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, this constraint validates more than simply whether or not the
individual fields in the collection pass their assigned constraints. In fact,
if any keys of a collection are missing or if there are any unrecognized
keys in the collection, validation errors will be thrown.

If you would like to allow for keys to be absent from the collection or if
you would like "extra" keys to be allowed in the collection, you can modify
the `allowMissingFields`_ and `allowExtraFields`_ options respectively. In
the above example, the ``allowMissingFields`` option was set to true, meaning
that if either of the ``personal_email`` or ``short_bio`` elements were missing
from the ``$personalData`` property, no validation error would occur.

.. versionadded:: 2.3
    The ``Required`` and ``Optional`` constraints were moved to the namespace
    ``Symfony\Component\Validator\Constraints\`` in Symfony 2.3.

Required and Optional Field Constraints
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Constraints for fields within a collection can be wrapped in the ``Required`` or
``Optional`` constraint to control whether they should always be applied (``Required``)
or only applied when the field is present (``Optional``).

For instance, if you want to require that the ``personal_email`` field of the
``profileData`` array is not blank and is a valid email but the ``alternate_email``
field is optional but must be a valid email if supplied, you can do the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Collection(
             *     fields={
             *         "personal_email"  = @Assert\Required({@Assert\NotBlank, @Assert\Email}),
             *         "alternate_email" = @Assert\Optional(@Assert\Email),
             *     }
             * )
             */
             protected $profileData = array(
                 'personal_email',
             );
        }

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $profileData = array('personal_email');

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('profileData', new Assert\Collection(array(
                    'fields' => array(
                        'personal_email'  => new Assert\Required(array(new Assert\NotBlank(), new Assert\Email())),
                        'alternate_email' => new Assert\Optional(new Assert\Email()),
                    ),
                )));
            }
        }

Even without ``allowMissingFields`` set to true, you can now omit the ``alternate_email``
property completely from the ``profileData`` array, since it is ``Optional``.
However, if the ``personal_email`` field does not exist in the array,
the ``NotBlank`` constraint will still be applied (since it is wrapped in
``Required``) and you will receive a constraint violation.

Options
-------

fields
~~~~~~

**type**: ``array`` [:ref:`default option<validation-default-option>`]

This option is required, and is an associative array defining all of the
keys in the collection and, for each key, exactly which validator(s) should
be executed against that element of the collection.

allowExtraFields
~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: false

If this option is set to ``false`` and the underlying collection contains
one or more elements that are not included in the `fields`_ option, a validation
error will be returned. If set to ``true``, extra fields are ok.

extraFieldsMessage
~~~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``The fields {{ fields }} were not expected``

The message shown if `allowExtraFields`_ is false and an extra field is detected.

allowMissingFields
~~~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: false

If this option is set to ``false`` and one or more fields from the `fields`_
option are not present in the underlying collection, a validation error will
be returned. If set to ``true``, it's ok if some fields in the `fields_`
option are not present in the underlying collection.

missingFieldsMessage
~~~~~~~~~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``The fields {{ fields }} are missing``

The message shown if `allowMissingFields`_ is false and one or more fields
are missing from the underlying collection.
