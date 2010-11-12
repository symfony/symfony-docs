Constraints
===========

The Validator is designed to validate objects against *constraints*.
In real life, a constraint could be: "The cake must not be burned". In
Symfony2, constraints are similar: They are assertions that a condition is 
true.

Supported Constraints
---------------------

The following constraints are natively available in Symfony2:

* `AssertFalse <constraints/AssertFalse>`_
* `AssertTrue <constraints/AssertTrue>`_
* `AssertType <constraints/AssertType>`_
* `Choice <constraints/Choice>`_
* `Collection <constraints/Collection>`_
* `Date <constraints/Date>`_
* `DateTime <constraints/DateTime>`_
* `Email <constraints/Email>`_
* `File <constraints/File>`_
* `Max <constraints/Max>`_
* `MaxLength <constraints/MaxLength>`_
* `Min <constraints/Min>`_
* `MinLength <constraints/MinLength>`_
* `NotBlank <constraints/NotBlank>`_
* `NotNull <constraints/NotNull>`_
* `Regex <constraints/Regex>`_
* `Time <constraints/Time>`_
* `Url <constraints/Url>`_
* `Valid <constraints/Valid>`_
  
Constraint Targets
------------------

Constraints can be put on properties of a class, on public getters and on the
class itself. The benefit of class constraints is that they can validate
the whole state of an object at once, with all of its properties and methods.

Properties
~~~~~~~~~~

Validating class properties is the most basic validation technique. Symfony2
allows you to validate private, protected or public properties. The next listing
shows how to configure the properties ``$firstName`` and ``$lastName`` of a class 
``Author`` to have at least 3 characters.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - MinLength: 3
                lastName:
                    - NotBlank: ~
                    - MinLength: 3
                    
    .. code-block:: xml
    
        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
            <property name="lastName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
        </class>
                    
    .. code-block:: php
    
        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank()
             * @validation:MinLength(3)
             */
            private $firstName;
            
            /**
             * @validation:NotBlank()
             * @validation:MinLength(3)
             */
            private $firstName;
        }
          
Getters
~~~~~~~

The next validation technique is to constrain the return value of a method.
Symfony2 allows you to constrain any public method whose name starts with
"get" or "is". In this guide, this is commonly referred to as "getter".

The benefit of this technique is that it allows you to validate your object
dynamically. Depending on the state of your object, the method may return
different values which are then validated.

The next listing shows you how to use the `AssertTrue <constraints/AssertTrue>`_
constraint to validate whether a dynamically generated token is correct.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            getters:
                tokenValid:
                    - AssertTrue: { message: "The token is invalid" }
                    
    .. code-block:: xml
    
        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <getter property="tokenValid">
                <constraint name="AssertTrue">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>
        
    .. code-block:: php
    
        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:AssertTrue(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                // return true or false
            }
        }
          
.. note::

    The keen-eyed among you will have noticed that the prefix of the getter 
    ("get" or "is") is omitted in the mapping. This allows you to move the
    constraint to a property with the same name later (or vice versa) without
    changing your validation logic.

Custom Constraints
------------------

You can create a custom constraint by extending the base constraint class,
:class::`Symfony\Component\Validator\Constraint`::. Options for your constraint are
represented by public properties on the constraint class. For example, the
``Url`` constraint includes ``message`` and ``protocols`` properties::

    namespace Symfony\Component\Validator\Constraints;

    class Url extends \Symfony\Component\Validator\Constraint
    {
        public $message = 'This value is not a valid URL';
        public $protocols = array('http', 'https', 'ftp', 'ftps');
    }

As you can see, a constraint class is fairly minimal. The actual validation is
performed by a another "constraint validator" class. Which constraint
validator is specified by the constraint's ``validatedBy()`` method, which
includes some simple default logic::

    // in the base Symfony\Component\Validator\Constraint class
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

Constraint Validators with Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your constraint validator has dependencies, such as a database connection,
it will need to be configured as a service in the dependency injection
container. This service must include the ``validator.constraint_validator`` tag
and an ``alias`` attribute:

.. configuration-block::

    .. code-block:: yaml

        services:
            validator.unique.your_validator_name:
                class: Fully\Qualified\Validator\Class\Name
                tags:
                    - { name: validator.constraint_validator, alias: alias_name }

    .. code-block:: xml

        <service id="validator.unique.your_validator_name" class="Fully\Qualified\Validator\Class\Name">
            <argument type="service" id="doctrine.orm.default_entity_manager" />
            <tag name="validator.constraint_validator" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('validator.unique.your_validator_name', 'Fully\Qualified\Validator\Class\Name')
            ->addTag('validator.constraint_validator', array('alias' => 'alias_name'))
        ;

Your constraint class may now use this alias to reference the appropriate
validator:

.. code-block:: php

    public function validatedBy()
    {
        return 'alias_name';
    }