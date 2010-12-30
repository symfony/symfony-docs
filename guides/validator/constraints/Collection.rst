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

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
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

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
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

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Collection(
             *   fields = {
             *     "firstName" = @validation:NotNull(),
             *     "lastName" = { @validation:NotBlank(), @validation:MinLength(4) }
             *   },
             *   allowMissingFields = true
             * )
             */
            private $options = array();
        }

    .. code-block:: php

        // Application/HelloBundle/Author.php
        use Symfony\Components\Validator\Constraints\Collection;
        use Symfony\Components\Validator\Constraints\NotNull;
        use Symfony\Components\Validator\Constraints\NotBlank;
        use Symfony\Components\Validator\Constraints\MinLength;
        
        class Author
        {
            private $options = array();
            
            public static function loadMetadata(ClassMetadata $metadata)
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

    Application\HelloBundle\Author.options[firstName]:
        This value should not be null
    Application\HelloBundle\Author.options[lastName]:
        This value is too short. It should have 4 characters or more
