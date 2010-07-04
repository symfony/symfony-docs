Valid
=====

Validates an associated object.

    [yml]
    properties:
      address:
        - Valid: ~
    
Options
-------

  * `class`: The expected class of the object
  * `message`: The error message if the class doesn't match
  
Example 1: Validate object trees (YAML)
---------------------------------------
        
This constraint helps to validate whole object trees. In the following example,
we create two classes `Author` and `Address` that both have constraints on
their properties. Furthermore, `Author` stores an `Address` instance in the
`$address` property.
    
**Listing 1**

    [php]
    // Application/HelloBundle/Address.php
    class Address
    {
        protected $street;
        protected $zipCode;
    }

**Listing 2**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        protected $firstName;
        protected $lastName;
        protected $address;
    }
    
**Listing 3**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Address:
      properties:
        street:
          - NotBlank: ~
        zipCode:
          - NotBlank: ~
          - MaxLength: 5
          
    Application\HelloBundle\Author:
      properties:
        firstName:
          - NotBlank: ~
          - MinLength: 4
        lastName:
          - NotBlank: ~
          
With this mapping it is possible to successfully validate an author with an
invalid address. To prevent that, we add the `Valid` constraint to the 
`$address` property.

**Listing 4**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        address:
          - Valid: ~
          
We can even go one step further and validate the class of the related object
to be `Address` or one of its subclasses.

**Listing 5**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        address:
          - Valid: { class: Application\ḨelloBundle\Address }
          
Example 2: Validate object trees (XML)
--------------------------------------

The following code snippet shows the mapping of Listing 5 in XML.

    [xml]
    <!-- Application/HelloBundle/Resources/config/validation.xml -->
    <class name="Application\HelloBundle\Author">
      <property name="address">
        <constraint name="Valid">Application\HelloBundle\Address</constraint>
      </property>
    </class>
    
Example 3: Validate object trees (Docblock Annotations)
-------------------------------------------------------

With annotations, the mapping of Listing 5 looks like this:

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        /**
         * @Validation({ @Valid(class = "Application\HelloBundle\Address") })
         */
        protected $address;
    }
