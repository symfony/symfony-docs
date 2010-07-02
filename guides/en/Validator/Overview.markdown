The Validator
=============

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony2 comes with a new Validator component that makes this task very easy.
This component is based on the [JSR303 Bean Validation specification][1]. 
What? A Java specification in PHP? You heard right, but it's not as bad as it 
sounds. Let's look at how we use it in PHP.

Constraints
-----------

The Validator is designed to validate objects against different *constraints*.
In real life, a constraint would be: "The cake must not be burned". In
Symfony2, constraints are very similar: They are assertions that a specific
condition is true.

Constraints can be put on properties of a class, on public getters and on the
class itself. Property and getter constraints obviously can only be used to
validate a single value. Class constraints, on the other hand, can validate
the whole state of an object at once, with all its properties and methods.

>**NOTE**
>As "getter" the validator accepts any method with the prefix "get" or "is."

The following constraints are natively available in Symfony2:

  * [AssertFalse](Constraints/AssertFalse)
  * [AssertTrue](Constraints/AssertTrue)
  * [AssertType](Constraints/AssertType)
  * [Choice](Constraints/Choice)
  * [Collection](Constraints/Collection)
  * [Date](Constraints/Date)
  * [DateTime](Constraints/DateTime)
  * [Email](Constraints/Email)
  * [File](Constraints/File)
  * [Max](Constraints/Max)
  * [MaxLength](Constraints/MaxLength)
  * [Min](Constraints/Min)
  * [MinLength](Constraints/MinLength)
  * [NotBlank](Constraints/NotBlank)
  * [NotNull](Constraints/NotNull)
  * [Regex](Constraints/Regex)
  * [Time](Constraints/Time)
  * [Url](Constraints/Url)
  * [Valid](Constraints/Valid)

Mapping
-------

The mapping of constraints to classes, properties and getters takes place
in the mapping. Symfony2 supports YAML, XML, PHP and Docblock Annotations as
mapping drivers.

Let's look at the YAML driver for now. We start with a simple example class
`Author`.

**Listing 1**

    [php]
    // Application/HelloBundle/Author.php
    namespace Application\HelloBundle;
    
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

This guide uses YAML mappings throughout all examples. There will be dedicated
chapters about the XML, PHP and Annotation drivers.

The Validator
-------------

Objects with constraints are validated by the `Validator` class. If you use
Symfony2, this class is already registered as a service in the Dependency
Injection Container. To enable the service, add the following lines to your
configuration:

**Listing 3**

    [yml]
    # config.yml
    web.config:
      validation: true
      
Then you can get the validator from the container and start validating your
objects.

**Listing 4**

    [php]
    $validator = $container->getService('validator');
    $author = new Author();
    
    print $validator->validate($author);
    
The `validate()` method returns a `ConstraintViolationList` object. This object
behaves exactly like an array. You can iterate over it, and you can even print
it in a nicely formatted manner. Every element of the list corresponds to
one validation error. If the list is empty, it's time to dance, because then
validation succeeded.

The above call will output something similar to this:

    Application\HelloBundle\Author.firstName:
        This value should not be blank
    Application\HelloBundle\Author.lastName:
        This value should not be blank
    Application\HelloBundle\Author.fullName:
        This value is too short. It should have 10 characters or more
        
If you fill the object with correct values, you will see that the validation
errors disappear.

### Validating Properties

TODO

### Validating Values

TODO

[1]: http://jcp.org/en/jsr/detail?id=303