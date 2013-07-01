Callback
========

The purpose of the Callback assertion is to let you create completely custom
validation rules and to assign any validation errors to specific fields on
your object. If you're using validation with forms, this means that you can
make these custom errors display next to a specific field, instead of simply
at the top of your form.

This process works by specifying one or more *callback* methods, each of
which will be called during the validation process. Each of those methods
can do anything, including creating and assigning validation errors.

.. note::

    A callback method itself doesn't *fail* or return any value. Instead,
    as you'll see in the example, a callback method has the ability to directly
    add validator "violations".

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`class<validation-class-target>`                                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `methods`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Callback`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\CallbackValidator` |
+----------------+------------------------------------------------------------------------+

Setup
-----

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            constraints:
                - Callback:
                    methods:   [isAuthorValid]

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        /**
         * @Assert\Callback(methods={"isAuthorValid"})
         */
        class Author
        {
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <constraint name="Callback">
                <option name="methods">
                    <value>isAuthorValid</value>
                </option>
            </constraint>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Callback(array(
                    'methods' => array('isAuthorValid'),
                )));
            }
        }

The Callback Method
-------------------

The callback method is passed a special ``ExecutionContextInterface`` object. You
can set "violations" directly on this object and determine to which field
those errors should be attributed::

    // ...
    use Symfony\Component\Validator\ExecutionContextInterface;
    
    class Author
    {
        // ...
        private $firstName;
    
        public function isAuthorValid(ExecutionContextInterface $context)
        {
            // somehow you have an array of "fake names"
            $fakeNames = array();
        
            // check if the name is actually a fake name
            if (in_array($this->getFirstName(), $fakeNames)) {
                $context->addViolationAt('firstname', 'This name sounds totally fake!', array(), null);
            }
        }
    }

Options
-------

methods
~~~~~~~

**type**: ``array`` **default**: ``array()`` [:ref:`default option<validation-default-option>`]

This is an array of the methods that should be executed during the validation
process. Each method can be one of the following formats:

1) **String method name**

    If the name of a method is a simple string (e.g. ``isAuthorValid``), that
    method will be called on the same object that's being validated and the
    ``ExecutionContextInterface`` will be the only argument (see the above example).

2) **Static array callback**

    Each method can also be specified as a standard array callback:

    .. configuration-block::

        .. code-block:: yaml

            # src/Acme/BlogBundle/Resources/config/validation.yml
            Acme\BlogBundle\Entity\Author:
                constraints:
                    - Callback:
                        methods:
                            -    [Acme\BlogBundle\MyStaticValidatorClass, isAuthorValid]

        .. code-block:: php-annotations

            // src/Acme/BlogBundle/Entity/Author.php
            use Symfony\Component\Validator\Constraints as Assert;

            /**
             * @Assert\Callback(methods={
             *     { "Acme\BlogBundle\MyStaticValidatorClass", "isAuthorValid"}
             * })
             */
            class Author
            {
            }

        .. code-block:: xml

            <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
            <class name="Acme\BlogBundle\Entity\Author">
                <constraint name="Callback">
                    <option name="methods">
                        <value>
                            <value>Acme\BlogBundle\MyStaticValidatorClass</value>
                            <value>isAuthorValid</value>
                        </value>
                    </option>
                </constraint>
            </class>

        .. code-block:: php

            // src/Acme/BlogBundle/Entity/Author.php

            use Symfony\Component\Validator\Mapping\ClassMetadata;
            use Symfony\Component\Validator\Constraints\Callback;

            class Author
            {
                public $name;

                public static function loadValidatorMetadata(ClassMetadata $metadata)
                {
                    $metadata->addConstraint(new Callback(array(
                        'methods' => array(
                            array('Acme\BlogBundle\MyStaticValidatorClass', 'isAuthorValid'),
                        ),
                    )));
                }
            }

    In this case, the static method ``isAuthorValid`` will be called on the
    ``Acme\BlogBundle\MyStaticValidatorClass`` class. It's passed both the original
    object being validated (e.g. ``Author``) as well as the ``ExecutionContextInterface``::

        namespace Acme\BlogBundle;
    
        use Symfony\Component\Validator\ExecutionContextInterface;
        use Acme\BlogBundle\Entity\Author;
    
        class MyStaticValidatorClass
        {
            public static function isAuthorValid(Author $author, ExecutionContextInterface $context)
            {
                // ...
            }
        }

    .. tip::

        If you specify your ``Callback`` constraint via PHP, then you also have
        the option to make your callback either a PHP closure or a non-static
        callback. It is *not* currently possible, however, to specify a :term:`service`
        as a constraint. To validate using a service, you should
        :doc:`create a custom validation constraint</cookbook/validation/custom_constraint>`
        and add that new constraint to your class.
