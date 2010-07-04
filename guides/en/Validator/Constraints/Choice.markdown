Choice
======

Validates that a value is one or more of a list of choices.

    [yml]
    properties:
      gender:
        - Choice: [male, female]
   
Options
-------

  * `choices` (**default**, required): The available choices
  * `callback`: Can be used instead of `choices`. A static callback method
    returning the choices. If you pass a string, it is expected to be
    the name of a static method in the validated class.
  * `multiple`: Whether multiple choices are allowed. Default: `false`
  * `min`: The minimum amount of selected choices
  * `max`: The maximum amount of selected choices
  * `message`: The error message if validation fails
  * `minMessage`: The error message if `min` validation fails
  * `maxMessage`: The error message if `max` validation fails
  
Example 1: Choices as array (YAML)
----------------------------------

If the choices are few and easy to determine, they can be passed to the
constraint definition as array.

**Listing 1**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        gender:
          - Choice: [male, female]
          
Example 2: Choices as array (XML)
---------------------------------

The following snippet shows the mapping of Example 1 using XML.

**Listing 2**

    [xml]
    <!-- Application/HelloBundle/Resources/config/validation.xml -->
    <class name="Application\HelloBundle\Author">
      <property name="gender">
        <constraint name="Choice">
          <value>male</value>
          <value>female</value>
        </constraint>
      </property>
    </class>
    
Example 3: Choices as array (Docblock Annotations)
--------------------------------------------------

Of course, Example 1 can also be solved with annotations.

**Listing 3**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        /**
         * @Validation({ @Choice({"male", "female"}) })
         */
        protected $gender;
    }
          
Example 4: Choices from a callback (YAML)
-----------------------------------------

When you also need the choices in other contexts (such as a drop-down box in
a form), it is more flexible to bind them to your domain model using a static
callback method.

**Listing 4**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        public static function getGenders()
        {
            return array('male', 'female');
        }
    }
    
You can pass the name of this method to the `callback` option of the `Choice`
constraint.

**Listing 5**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        gender:
          - Choice: { callback: getGenders }
          
If the static callback is stored in a different class, for example `Util`,
you can pass the class name and the method as array.

**Listing 6**

    [yml]
    # Application/HelloBundle/Resources/config/validation.yml
    Application\HelloBundle\Author:
      properties:
        gender:
          - Choice: { callback: [Util, getGenders] }
          
Example 5: Choices from a callback (XML)
----------------------------------------

The following listing shows how Listing 6 is written with XML.

**Listing 7**

    [xml]
    <!-- Application/HelloBundle/Resources/config/validation.xml -->
    <class name="Application\HelloBundle\Author">
      <property name="gender">
        <constraint name="Choice">
          <option name="callback">
            <value>Util</value>
            <value>getGenders</value>
          </option>
        </constraint>
      </property>
    </class>
    
Example 6: Choices from a callback (Docblock Annotations)
---------------------------------------------------------

Here you see how the mapping of Listing 6 is written with annotations.

**Listing 8**

    [php]
    // Application/HelloBundle/Author.php
    class Author
    {
        /**
         * @Validation({ @Choice(callback = {"Util", "getGenders"}) })
         */
        protected $gender;
    }
