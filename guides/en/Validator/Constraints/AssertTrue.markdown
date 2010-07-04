AssertTrue
==========

Validates that a value is `true`.

    [yml]
    properties:
      termsAccepted:
        - AssertTrue: ~
        
Options
-------

  * `message`: The error message if validation fails
  

Example 1 (YAML)
----------------
          
This constraint is very useful to execute custom validation logic. You can
put the logic in a method which returns either `true` or `false`.

**Listing 1**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        protected $token;
      
        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }
    
Then you can constrain this method with `AssertTrue`.

**Listing 2**

    [yaml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      getters:
        tokenValid:
          - AssertTrue: { message: "The token is invalid" }
        
If the validation of this method fails, you will see a message similar to this:

    Application\HelloBundle\Author.tokenValid:
        This value should not be null
        
Example 2 (XML)
---------------

Example 1 can also be solved using XML.

**Listing 3**

    [xml]
    <!-- Application/HelloBundle/Resources/config/validation.xml -->
    <class name="Application\HelloBundle\Author">
      <getter name="tokenValid">
        <constraint name="True">
          <option name="message">The token is invalid</option>
        </constraint>
      </getter>
    </class>
    
Example 3 (Docblock Annotations)
--------------------------------

The following listing shows how Listing 2 can be mapped using Docblock
Annotations.

**Listing 4**

    [php]
    // Application/HelloBundle/Author.php
    namespace Application\HelloBundle;
    
    class Author
    {
        protected $token;
        
        /**
         * @Validation({ @AssertTrue(message = "The token is invalid") })
         */
        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }

