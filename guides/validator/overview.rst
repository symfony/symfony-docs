The Validator
=============

Validation is a very common task in web applications. Data entered in forms
needs to be validated. Data also needs to be validated before it is written
into a database or passed to a web service.

Symfony2 ships with a Validator component that makes this task very easy. This
component is based on the `JSR303 Bean Validation specification`_. What? A
Java specification in PHP? You heard right, but it's not as bad as it sounds.
Let's look at how we use it in PHP.

The validator validates objects against :doc:`constraints <constraints>`.
Let's start with the simple constraint that the ``$name`` property of a class
``Author`` must not be empty::

    // Application/HelloBundle/Author.php
    class Author
    {
        private $name;
    }

The next listing shows the configuration that connects properties of the class
with constraints; this process is called the "mapping":

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                name:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="name">
                <constraint name="NotBlank" />
            </property>
        </class>

    .. code-block:: php-annotations

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank()
             */
            private $name;
        }

    .. code-block:: php

        // Application/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;        
        use Symfony\Components\Validator\Constraints\NotBlank;
        
        class Author
        {
            private $name;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank());
            }
        }

Finally, we can use the :class:`Symfony\\Component\\Validator\\Validator`
class for :doc:`validation <validation>`. To use the default Symfony2
validator, adapt your application configuration as follows:

.. code-block:: yaml

    # hello/config/config.yml
    app.config:
        validation:
            enabled: true

Now call the ``validate()`` method on the service, which delivers a list of
errors if validation fails.

.. code-block:: php

    $validator = $container->get('validator');
    $author = new Author();

    print $validator->validate($author);

Because the ``$name`` property is empty, you will see the following error
message:

.. code-block:: text

    Application\HelloBundle\Author.name:
        This value should not be blank

Insert a value into the property and the error message will disappear.

.. _JSR303 Bean Validation specification: http://jcp.org/en/jsr/detail?id=303
