The Validator
=============

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony2 comes with a new Validator component that makes this task very easy.
This component is based on the [JSR303 Bean Validation specification][1]. 
What? A Java specification in PHP? You heard right, but it's not as bad as it 
sounds. Let's look at how we use it in PHP.

Example
-------

The validator validates objects against [constraints](Constraints). Let's start
with the simple constraint that the `$name` property of a class `Author` must
not be empty.

**Listing 1**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
      private $name;
    }
    
**Listing 2**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        name:
          - NotBlank: ~
          
Listing 2 shows a YAML file that connects properties of the class with
constraints. This process is called the [mapping](Mapping).

Finally, we can use the `Validator` class for [validation](Validation). Symfony2
provides a validator instance as a service in the Dependency Injection
Container. To use that service, adapt your application configuration.

**Listing 3**

    [yml]
    # config.yml
    web.config:
      validation:
        enabled: true
      
Now call the `validate()` method on the service, which delivers a list of
errors if validation fails.

**Listing 4**

    [php]
    $validator = $container->getService('validator');
    $author = new Author();
    
    print $validator->validate($author);
    
Because the `$name` property is empty, you will see the following error
message.

    Application\HelloBundle\Author.name:
        This value should not be blank
        
Insert a value into the property and the error message will disappear.

[1]: http://jcp.org/en/jsr/detail?id=303