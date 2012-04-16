.. index::
   single: Validation; Custom constraints

How to create a Custom Validation Constraint
--------------------------------------------

You can create a custom constraint by extending the base constraint class,
:class:`Symfony\\Component\\Validator\\Constraint`. Options for your
constraint are represented as public properties on the constraint class. For
example, the :doc:`Url</reference/constraints/Url>` constraint includes
the ``message`` and ``protocols`` properties:

.. code-block:: php

    namespace Symfony\Component\Validator\Constraints;
    
    use Symfony\Component\Validator\Constraint;

    /**
     * @Annotation
     */
    class Protocol extends Constraint
    {
        public $message = 'This value is not a valid protocol';
        public $protocols = array('http', 'https', 'ftp', 'ftps');
    }

.. note::

    The ``@Annotation`` annotation is necessary for this new constraint in
    order to make it available for use in classes via annotations.

As you can see, a constraint class is fairly minimal. The actual validation is
performed by a another "constraint validator" class. The constraint validator
class is specified by the constraint's ``validatedBy()`` method, which
includes some simple default logic:

.. code-block:: php

    // in the base Symfony\Component\Validator\Constraint class
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

In other words, if you create a custom ``Constraint`` (e.g. ``MyConstraint``),
Symfony2 will automatically look for another class, ``MyConstraintValidator``
when actually performing the validation.

The validator class is also simple, and only has one required method: ``isValid``.
Furthering our example, take a look at the ``ProtocolValidator`` as an example:

.. code-block:: php

    namespace Symfony\Component\Validator\Constraints;
    
    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;

    class ProtocolValidator extends ConstraintValidator
    {
        public function isValid($value, Constraint $constraint)
        {
            if (in_array($value, $constraint->protocols)) {
                $this->setMessage($constraint->message, array('%protocols%' => $constraint->protocols));

                return true;
            }

            return false;
        }
    }

.. note::

    Don't forget to call ``setMessage`` to construct an error message when the
    value is invalid.

Constraint Validators with Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your constraint validator has dependencies, such as a database connection,
it will need to be configured as a service in the dependency injection
container. This service must include the ``validator.constraint_validator``
tag and an ``alias`` attribute:

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

Your constraint class should now use this alias to reference the appropriate
validator::

    public function validatedBy()
    {
        return 'alias_name';
    }

As mentioned above, Symfony2 will automatically look for a class named after
the constraint, with ``Validator`` appended.  If your constraint validator
is defined as a service, it's important that you override the
``validatedBy()`` method to return the alias used when defining your service,
otherwise Symfony2 won't use the constraint validator service, and will
instantiate the class instead, without any dependencies injected.

Class Constraint Validator
~~~~~~~~~~~~~~~~~~~~~~~~~~

Beside validating a class property, constraint can have a class scope by
providing a target:

.. code-block:: php

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

Thus, validator ``isValid()`` method get an object as first argument:

.. code-block:: php

    class ProtocolClassValidator extends ConstraintValidator
    {
        public function isValid(Protocol $protocol, Constraint $constraint)
        {
            if ($protocol->getFoo() != $protocol->getBar()) {

                //bind error message on foo property
                $this->context->addViolationAtSubPath('foo', $constraint->getMessage(), array(), null);

                return true;
            }

            return false;
        }
    }

