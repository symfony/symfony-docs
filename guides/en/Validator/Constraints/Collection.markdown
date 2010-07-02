Collection
==========

Validates array entries against different constraints.

    [yml]
    - Collection:
        fields:
          key1:
            - NotNull: ~
          key2:
            - MinLength: 10
  
Options
-------

  * `fields` (required): An associative array of array keys and one or more
    constraints
  * `allowMissingFields`: Whether some of the keys may not be present in the
    array. Default: `false`
  * `allowExtraFields`: Whether the array may contain keys not present in the
    `fields` option. Default: `false`
  * `missingFieldsMessage`: The error message if the `allowMissingFields`
    validation fails
  * `allowExtraFields`: The error message if the `allowExtraFields` validation
    fails

Example 1 (YAML):
-----------------

**Listing 1**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        options:
          - Collection:
              fields:
                firstName:
                  - NotNull: ~
                lastName:
                  - NotNull: ~
                  - MinLength: 4
              allowMissingFields: true
            
The following object would fail the validation.

**Listing 2**

    [php]
    $author = new Author();
    $author->options['firstName'] = null;
    $author->options['lastName'] = 'foo';
    
    print $validator->validate($author);
    
You should see the following error messages:

    Application\HelloBundle\Author.options[firstName]:
        This value should not be null
    Application\HelloBundle\Author.options[lastName]:
        This value is too short. It should have 4 characters or more
  
Example 2 (XML):
----------------

This example shows the same mapping as in Example 1 using XML.

**Listing 3**

    [xml]
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
    
Example 3 (Docblock Annotations):
---------------------------------

This example shows the mapping from Example 1 with Docblock Annotations.

**Listing 4**

    [php]
    // Application/HelloBundle/Author.php
    namespace Application\HelloBundle;
    
    class Author
    {
        /**
         * @Validation({ @Collection(
         *   fields = {
         *     "firstName" = @NotNull,
         *     "lastName" = { @NotBlank, @MinLength(4) }
         *   },
         *   allowMissingFields = true
         * )})
         */
        private $options = array();
    }