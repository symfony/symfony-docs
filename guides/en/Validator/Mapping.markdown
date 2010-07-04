Constraint Mapping
==================

The mapping of constraints to classes, properties and getters takes place
in the mapping. Symfony2 supports YAML, XML, PHP and Docblock Annotations as
mapping drivers.

Let's look at the YAML driver for now. We start with a simple example class
`Author`.

**Listing 1**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
      public $firstName;
      public $lastName;
      
      public function getFullName()
      {
        return $this->firstName.' '.$this->lastName;
      }
    }
    
Now we want to put some simple constraints on this class.

  * The first and last name should not be blank
  
  * The first and last name together should have at least 10 characters
  
To map these constraints with YAML, create a new `validation.yml` mapping file 
in your bundle.

**Listing 2**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        firstName:
          - NotBlank: ~
        lastName:
          - NotBlank: ~
      getters:
        fullName:
          - MinLength: 10
          
As you can see, the mapping is very straight-forward. 

>**NOTE**
>The keen-eyed among you will have noticed that the prefix of the getter 
>("get" or "is") is omitted in the mapping. This allows you to move the
>constraint to a property with the same name later (or vice versa) without
>changing your validation logic.