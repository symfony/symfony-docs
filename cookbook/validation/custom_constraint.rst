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
    class Url extends Constraint
    {
        public $message = 'This value is not a valid URL';
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
Take the ``NotBlankValidator`` as an example:

.. code-block:: php

    class NotBlankValidator extends ConstraintValidator
    {
        public function isValid($value, Constraint $constraint)
        {
            if (null === $value || '' === $value) {
                $this->setMessage($constraint->message);

                return false;
            }

            return true;
        }
    }

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

Your constraint class may now use this alias to reference the appropriate
validator::

    public function validatedBy()
    {
        return 'alias_name';
    }