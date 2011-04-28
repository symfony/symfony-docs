Collection
==========

Validates array entries against different constraints.

.. code-block:: yaml

    - Collection:
        fields:
            key1:
                - NotNull: ~
            key2:
                - MinLength: 10

Options
-------

* ``fields`` (required): An associative array of array keys and one or more
  constraints
* ``allowMissingFields``: Whether some of the keys may not be present in the
  array. Default: ``false``
* ``allowExtraFields``: Whether the array may contain keys not present in the
  ``fields`` option. Default: ``false``
* ``missingFieldsMessage``: The error message if the ``allowMissingFields``
  validation fails
* ``allowExtraFields``: The error message if the ``allowExtraFields`` validation
  fails

Example:
--------

Let's validate an array with two indexes ``firstName`` and ``lastName``. The 
value of ``firstName`` must not be blank, while the value of ``lastName`` must 
not be blank with a minimum length of four characters. Furthermore, both keys
may not exist in the array.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                options:
                    - Collection:
                        fields:
                            firstName:
                                - NotBlank: ~
                            lastName:
                                - NotBlank: ~
                                - MinLength: 4
                        allowMissingFields: true

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <property name="options">
                <constraint name="Collection">
                    <option name="fields">
                        <value key="firstName">
                            <constraint name="NotNull" />
                        </value>
                        <value key="lastName">
                            <constraint name="NotNull" />
                            <constraint name="MinLength">4</constraint>
                        </value>
                    </option>
                    <option name="allowMissingFields">true</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        class Author
        {
            /**
             * @assert:Collection(
             *   fields = {
             *     "firstName" = @assert:NotNull(),
             *     "lastName" = { @assert:NotBlank(), @assert:MinLength(4) }
             *   },
             *   allowMissingFields = true
             * )
             */
            private $options = array();
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\Collection;
        use Symfony\Component\Validator\Constraints\NotNull;
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Constraints\MinLength;
        
        class Author
        {
            private $options = array();
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('options', new Collection(array(
                    'fields' => array(
                        'firstName' => new NotNull(),
                        'lastName' => array(new NotBlank(), new MinLength(4)),
                    ),
                    'allowMissingFields' => true,
                )));
            }
        }

The following object would fail the validation.

.. code-block:: php

    $author = new Author();
    $author->options['firstName'] = null;
    $author->options['lastName'] = 'foo';

    print $validator->validate($author);

You should see the following error messages:

.. code-block:: text

    Acme\HelloBundle\Author.options[firstName]:
        This value should not be null
    Acme\HelloBundle\Author.options[lastName]:
        This value is too short. It should have 4 characters or more
