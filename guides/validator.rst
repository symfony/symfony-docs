.. index::
   single: Forms; Validators
   single: Validators

Validator
=========

The Basics
----------

The new Validator component is based on the `JSR303 Bean Validation
specification`_. What? A Java specification in PHP? You heard right, but
it's not as bad as it sounds. Let's look at how we use it in PHP.

The Validator is designed to validate objects against different constraints.
These constraints can be put on the class itself, on properties and on 
methods prefixed with "get" or "is". Let's look at a sample configuration::

    class Author
    {
        /**
         * @Validation({
         *   @NotBlank,
         *   @MinLength(4)
         * })
         */
        public $firstName;
      
        /**
         * @Validation({
         *   @Email(message="Ok, seriously now. Your email address please")
         * })
         */
        public function getEmail()
        {
            return 'foobar';
        }
    }
    
This snippet shows a very simple ``Author`` class with a property and a getter. 
Each constraint has a name, most of them also have a couple of options. Here we
configured the constraints with annotations, but Symfony2 also offers many
other configuration drivers.

Because the annotation driver depends on the Doctrine library, it is not enabled
by default. You can enable it in your ``config.yml``:

.. code-block:: yaml

    # hello/config/config.yml
    web.validation:
        annotations: true

Now let's try to validate an object::

    $author = new Author();
    $author->firstName = 'B.';

    print $this['validator']->validate($author);

You should see the following output:

.. code-block:: yaml

    Author.firstName:
        This value is too short. It should have 4 characters or more
    Author.email:
        Ok, seriously now. Your email address please

The ``validate()`` method returns a ``ConstraintViolationList`` object that can
simply be printed or processed in your code. That was easy!

.. index::
   single: Validators; Constraints

The Constraints
---------------

Symfony bundles many different constraints. The following list will show you
which ones are available and how you can use and configure them. Some
constraints have a default option. If you only set this option, you can leave
away the option name::

    /** @Validation({ @Min(limit=3) }) */

is identical to::

    /** @Validation({ @Min(3) }) */

AssertFalse
~~~~~~~~~~~

Validates that a value is ``false``. Very useful for testing return values of
methods::

    /** @Validation({ @AssertFalse }) */
    public function isInjured();

Options:

* message: The error message if validation fails

AssertTrue
~~~~~~~~~~

Works like ``AssertFalse``.

NotBlank
~~~~~~~~

Validates that a value is not empty::

    /** @Validation({ @NotBlank }) */
    private $firstName;

Options:

* message: The error message if validation fails

Blank
~~~~~

Works like ``NotBlank``.

NotNull
~~~~~~~

Validates that a value is not ``NULL``::

    /** @Validation({ @NotNull }) */
    private $firstName;

Null
~~~~

Works like ``NotNull``.

AssertType
~~~~~~~~~~

Validates that a value has a specific data type::

    /** @Validation({ @AssertType("integer") }) */
    private $age;

Options:

* type (default): The type

Choice
~~~~~~

Validates that a value is one or more of a list of choices::

    /** @Validation({ @Choice({"male", "female"}) }) */
    private $gender;

Options:

* choices (default): The available choices
* callback: Can be used instead of ``choices``. A static callback method
  returning the choices. If you set this to a string, the method is expected
  to be in the validated class.
* multiple: Whether multiple choices are allowed. Default: ``false``
* min: The minimum amount of selected choices
* max: The maximum amount of selected choices
* message: The error message if validation fails
* minMessage: The error message if ``min`` validation fails
* maxMessage: The error message if ``max`` validation fails

Valid
~~~~~

Validates that an object is valid. Can be put on properties or getters to
validate related objects::

    /** @Validation({ @Valid }) */
    private $address;

Options:

* class: The expected class of the object (optional)
* message: The error message if the class doesn't match

Collection
~~~~~~~~~~

Validates array entries against different constraints::

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
    
Options:

* fields (default): An associative array of array keys and one or more
  constraints
* allowMissingFields: Whether some of the keys may not be present in the
  array. Default: ``false``
* allowExtraFields: Whether the array may contain keys not present in the
  ``fields`` option. Default: ``false``
* missingFieldsMessage: The error message if the ``allowMissingFields``
  validation fails
* allowExtraFields: The error message if the ``allowExtraFields`` validation
  fails

Date
~~~~

Validates that a value is a valid date string with format ``YYYY-MM-DD``::

    /** @Validation({ @Date }) */
    private $birthday;

Options:

* message: The error message if the validation fails

DateTime
~~~~~~~~

Validates that a value is a valid datetime string with format ``YYYY-MM-DD
HH:MM:SS``::

    /** @Validation({ @DateTime }) */
    private $createdAt;

Options:

* message: The error message if the validation fails

Time
~~~~

Validates that a value is a valid time string with format ``HH:MM:SS``::

    /** @Validation({ @Time }) */
    private $start;

Options:

* message: The error message if the validation fails

Email
~~~~~

Validates that a value is a valid email address::

    /** @Validation({ @Email }) */
    private $email;

Options:

* message: The error message if the validation fails
* checkMX: Whether MX records should be checked for the domain. Default: ``false``

File
~~~~

Validates that a value is an existing file::

    /** @Validation({ @File(maxSize="64k") }) */
    private $filename;

Options:

* maxSize: The maximum allowed file size. Can be provided in bytes, kilobytes
  (with the suffix "k") or megabytes (with the suffix "M")
* mimeTypes: One or more allowed mime types
* notFoundMessage: The error message if the file was not found
* notReadableMessage: The error message if the file could not be read
* maxSizeMessage: The error message if ``maxSize`` validation fails
* mimeTypesMessage: The error message if ``mimeTypes`` validation fails

Max
~~~

Validates that a value is at most the given limit::

    /** @Validation({ @Max(99) }) */
    private $age;

Options:

* limit (default): The limit
* message: The error message if validation fails

Min
~~~

Works like ``Max``.

MaxLength
~~~~~~~~~

Validates that the string length of a value is at most the given limit::

    /** @Validation({ @MaxLength(32) }) */
    private $hash;

Options:

* limit (default): The size limit
* message: The error message if validation fails

MinLength
~~~~~~~~~

Works like ``MaxLength``.

Regex
~~~~~

Validates that a value matches the given regular expression::

    /** @Validation({ @Regex("/\w+/") }) */
    private $title;

Options:

* pattern (default): The regular expression pattern
* match: Whether the pattern must be matched or must not be matched.
  Default: ``true``
* message: The error message if validation fails

Url
~~~

Validates that a value is a valid URL::

    /** @Validation({ @Url }) */
    private $website;

Options:

* protocols: A list of allowed protocols. Default: "http", "https", "ftp"
  and "ftps".
* message: The error message if validation fails

.. index::
   single: Validators; Configuration

Other Configuration Drivers
---------------------------

As always in Symfony, there are multiple ways of configuring the constraints
for your classes. Symfony supports the following four drivers.

XML Configuration
~~~~~~~~~~~~~~~~~

The XML driver is a little verbose, but has the benefit that the XML file can be
validated to prevent errors. To use the driver, simply put a file called 
``validation.xml`` in the ``Resources/config/`` directory of your bundle:

.. code-block:: xml

    <?xml version="1.0" ?>
    <constraint-mapping xmlns="http://www.symfony-project.org/schema/dic/constraint-mapping"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.symfony-project.org/schema/dic/constraint-mapping 
            http://www.symfony-project.org/schema/dic/services/constraint-mapping-1.0.xsd">

        <class name="Application\HelloBundle\Model\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">4</constraint>
            </property>
            <getter property="email">
                <constraint name="Email">
                    <option name="message">Ok, seriously now. Your email address please</option>
                </constraint>
            </getter>
        </class>
    </constraint-mapping>

YAML Configuration
~~~~~~~~~~~~~~~~~~

The YAML driver offers the same functionality as the XML driver. To use it,
put the file ``validation.yml`` in the ``Resources/config/`` directory of your
bundle:

.. code-block:: yaml

    Application\HelloBundle\Model\Author:
        properties:
            firstName:
                - NotBlank: ~
                - MinLength: 4
          
        getters:
            email:
                - Email: { message: "Ok, seriously now. Your email address please" }

PHP Configuration
~~~~~~~~~~~~~~~~~

If you prefer to write configurations in plain old PHP, you can add the static
method ``loadValidatorMetadata()`` to the classes that you want to validate::

    use Symfony\Component\Validator\Constraints;
    use Symfony\Component\Validator\Mapping\ClassMetadata;

    class Author
    {
        public static function loadValidatorMetadata(ClassMetadata $metadata)
        {
            $metadata->addPropertyConstraint('firstName', new Constraints\NotBlank());
            $metadata->addPropertyConstraint('firstName', new Constraints\MinLength(3));
            $metadata->addGetterConstraint('email', new Constraints\Email(array(
                'message' => 'Ok, seriously now. Your email address please',
            )));
        }
    }

You can use either of the configuration drivers, or all together. Symfony will
merge all the information it can find.

.. _JSR303 Bean Validation specification: http://jcp.org/en/jsr/detail?id=303
